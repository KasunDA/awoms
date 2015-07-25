<?php
if (empty($_GET['f']))
{
    Errors::debugLogger("File F missing, die.");
    die("No file defined");
    return;
}
else
{
    $file = $_GET['f'];
}

// Global definitions
if (!defined('DS'))
{
    define('DS', DIRECTORY_SEPARATOR);
}
if (!defined('ROOT'))
{
    define('ROOT', dirname(dirname(dirname(__FILE__))));
}
// Load Config
require_once (ROOT . DS . 'config' . DS . 'config.php');
# Look up the requested domain and its matching brand
Bootstrap::lookupDomainBrand();
# Session start/resume
new Session();
Errors::debugLogger("Checking if user has files read access...");
$_SESSION['returnURL'] = "file/".$file;
Errors::debugLogger('Requested File: "'.$file.'"');

// Normal ACL check for straight image request
#if (!ACL::IsUserAuthorized("files", "read", TRUE)) # TRUE redirects to login if denied
#{
#    Errors::debugLogger("Access Denied (ACL)");
#    die("Access Denied");
#}
# @TODO: DISABLED: Anonymous views to Pages with restricted images not showing images. Temporariliy disabling ACL check for anon to allow images to work on pages
//^-- fixed below. This won't work until tied into Database. Using folder structure to determin acl below.

Errors::debugLogger("Checking if file exists...");
if (!is_file($file))
{
    Errors::debugLogger("Requested file not found: $file");
    die("Requested file not found: $file");
    return;
}

# Possible request formats:
# 
#  #1) Store Picture
#.....................(1)........(2)......(3)..(4).(5)(6)
#      public/files/source/Brands/1/Stores/2/Public/x.jpg
#      
#  #2) Brand Picture
#.....................(1)........(2)..(3).(4)(5)
#      public/files/source/Brands/1/Public/x.jpg
#      
#  #3) Global Picture
#.....................(1)....(2).(3)(4)
#      public/files/source/Public/x.jpg

$foundMatch = FALSE;
$size       = NULL;
$brandID    = NULL;
$storeID    = NULL;
$public     = FALSE;
$where      = NULL;
$ext        = NULL;
if (!$foundMatch)
{
    // Format #1) Store Picture
    #.....................(1)........(2)......(3)..(4).(5)(6)
    #      public/files/source/Brands/1/Stores/2/Public/x.jpg
    $pattern = "/(source|thumbs)\/Brands\/(\d+)\/Stores\/(\d+)\/(Public|Private)\/(.+)(\..+)$/";
    Errors::debugLogger("Extracting path info with pattern #1: $pattern...",10);
    $r=preg_match_all($pattern, $file, $matches);
    if ($r)
    {
        $foundMatch = TRUE;
        $size  = $matches[1]; # source or thumbs
        $size  = $size[0];
        $brandID = $matches[2]; # brand #
        $brandID = $brandID[0];
        $storeID = $matches[3]; # store #
        $storeID = $storeID[0];
        $p = $matches[4];
        $p = $p[0];
        if ($p == "Public") { $public = TRUE; } else { $public = FALSE; }
        $where = $matches[5]; # filepath/filename
        $where = $where[0];
        $ext = $matches[6]; # ext
        $ext = $ext[0];
        Errors::debugLogger(sprintf('Request parsed: Size: (%s) Brand: (%s) Store: (%s) Public: (%s) Where: (%s) Ext: (%s)', $size, $brandID, $storeID, $public, $where, $ext));
    }
}

if (!$foundMatch)
{
    // Format #2) Brand Picture
    #.....................(1)........(2)..(3).(4)(5)
    #      public/files/source/Brands/1/Public/x.jpg
    $pattern = "/(source|thumbs)\/Brands\/(\d+)\/(Public|Private)\/(.+)(\..+)$/";
    Errors::debugLogger("Extracting path info with pattern #2: $pattern...",10);
    $r=preg_match_all($pattern, $file, $matches);
    if ($r)
    {
        $foundMatch = TRUE;
        $size  = $matches[1]; # source or thumbs
        $size  = $size[0];
        $brandID = $matches[2]; # brand #
        $brandID = $brandID[0];
        $p = $matches[3];
        $p = $p[0];
        if ($p == "Public") { $public = TRUE; } else { $public = FALSE; }
        $where = $matches[4]; # filepath/filename
        $where = $where[0];
        $ext = $matches[5]; # ext
        $ext = $ext[0];
        Errors::debugLogger(sprintf('Request parsed: Size: (%s) Brand: (%s) Public: (%s) Where: (%s) Ext: (%s)', $size, $brandID, $public, $where, $ext));
    }
}

if (!$foundMatch)
{
    // Format #3) Global Picture
    #.....................(1)....(2).(3)(4)
    #      public/files/source/Public/x.jpg
    $pattern = "/(source|thumbs)\/(Public|Private)\/(.+)(\..+)$/";
    Errors::debugLogger("Extracting path info with pattern #3: $pattern...",10);
    $r=preg_match_all($pattern, $file, $matches);
    if ($r)
    {
        $foundMatch = TRUE;
        $size  = $matches[1]; # source or thumbs
        $size  = $size[0];
        $p = $matches[2];
        $p = $p[0];
        if ($p == "Public") { $public = TRUE; } else { $public = FALSE; }
        $where = $matches[3]; # filepath/filename
        $where = $where[0];
        $ext = $matches[4]; # ext
        $ext = $ext[0];
        Errors::debugLogger(sprintf('Request parsed: Size: (%s) Public: (%s) Where: (%s) Ext: (%s)', $size, $public, $where, $ext));
    }
}

if (!$foundMatch)
{
    Errors::debugLogger("Requested file did not match any expected formats, die()...");
    die("Requested file not found.");
    return;
}

if ($public)
{
    Errors::debugLogger(sprintf('Is Public, ACL = Allowed...'));
    $allowedByACL = TRUE;
} else {
    
    // Check ACL for access to Private file
    Errors::debugLogger(sprintf('Is Private, Checking ACL...'));
    $allowedByACL = FALSE;
    
    // Admins have access to everything
    if (!empty($_SESSION['user'])
                && $_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
    {
        Errors::debugLogger("Is Administrator, setting AllowedByACL to TRUE...");
        $allowedByACL = TRUE;
    } elseif (!empty($_SESSION['user'])
                && $_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
        {
        // Request is for private Brand Store file:
        // ensure user's usergroup is part of this brand store
        if ($brandID != NULL)
        {
            if ($brandID == $_SESSION['user']['usergroup']['brandID'])
            {
                Errors::debugLogger("Brand # matches! Setting AllowedByACL to TRUE...");
                $allowedByACL = TRUE;
            } else {
                Errors::debugLogger("Brand # does NOT match! Setting AllowedByACL to FALSE...");
                $allowedByACL = FALSE;
            }
        }
        
        // Request is for private Store file:
        if ($storeID != NULL)
        {
            // Is this user the store owner of this store to see this private store file?
            $Store = new Store();
            $reqStore = $Store->getSingle(array('storeID'=>$storeID));
            if (empty($reqStore)) {
                Errors::debugLogger("Store not found!");
                $allowedByACL = FALSE;
            } else {
                if ($reqStore['ownerID'] == $_SESSION['user']['userID'])
                {
                    Errors::debugLogger("User is Store Owner of requested! Setting AllowedByACL to TRUE...");
                    $allowedByACL = TRUE;
                } else {
                    Errors::debugLogger("File Store #: ".$storeID." Session Store#: ".$_SESSION['user']['userID']);
                    Errors::debugLogger("Store # does NOT match! Setting AllowedByACL to FALSE...");
                    $allowedByACL = FALSE;
                }
            }
        }
    } else {
        if (empty($_SESSION['user']))
        {
            Errors::debugLogger("User NOT Logged In!");
        }
    }
}

$allowedLabel = "No";
if ($allowedByACL === TRUE)
{
    $allowedLabel = "Yes";
    Errors::debugLogger("Found file $file... Allowed: " . $allowedLabel);
    Errors::debugLogger("Getting filesize...",10);
    $filesize = (string)(filesize($file));
    Errors::debugLogger("Filesize: ".$filesize,10);
    $filename = basename($file);
    Errors::debugLogger("Basename: ".$filename,10);
    $mode = "inline"; # Needed for inline image display
    if (!empty($_GET['mode']))
    {
        $mode = "attachment"; # Will force download of file
    }
    Errors::debugLogger("Display mode: ".$mode,10);
    header('Pragma: private');
    header('Cache-control: private, must-revalidate');
    if ($mode == "inline")
    {
        $image_mime = image_type_to_mime_type(exif_imagetype($file));
        Errors::debugLogger("Image mime: ".$image_mime,10);
        header("Content-Type: ".$image_mime); 
    } else {
        header("Content-Type: application/octet-stream");
    }
    header("Content-Length: " . $filesize);
    header('Content-Disposition: '.$mode.'; filename="' . $filename . '"'); 
    readfile($file);
    exit;
}
else
{
    Errors::debugLogger(__FILE__." :: ACL Access Denied to file.");
    die("Access Denied to file.");
    return;
}