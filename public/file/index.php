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

// Normal ACL check for straight image request
#if (!ACL::IsUserAuthorized("files", "read", TRUE)) # TRUE redirects to login if denied
#{
#    Errors::debugLogger("Access Denied (ACL)");
#    die("Access Denied");
#}
# @TODO: DISABLED: Anonymous views to Pages with restricted images not showing images. Temporariliy disabling ACL check for anon to allow images to work on pages

// File info
//$pattern = "/(source|thumbs)\/(.+)(\/.+\..+)$/";
$pattern = "/(source|thumbs)\/(.+)(\..+)$/";
Errors::debugLogger('Requested File: "'.$file.'"');
Errors::debugLogger("Extracting path info...",10);
$r=preg_match_all($pattern, $file, $matches);
if (empty($r))
{
    Errors::debugLogger("Requested file did not match the expected format, die()...");
    die("Requested file not found");
    return;
}

Errors::debugLogger("Checking if file exists...");
if (!is_file($file))
{
    Errors::debugLogger("Requested file not found: $file");
    die("Requested file not found: $file");
    return;
}

#public/files/thumbs/testpic.jpg
#public/files/source/testpic.jpg
#public/files/source/test.pic.jpg
#public/files/source/testsub/testpic.jpg
#public/files/source/Stores/2/teststore2pic.jpg <--- Restricted by store #
#public/files/source/Stores/2/test.store2pic.jpg <--- Restricted by store #
#public/files/source/Stores/2/testsub/store2pic.jpg <--- Restricted by store #
#.............(1).....(2)......................(3)

$size  = $matches[1]; # source or thumbs
$size  = $size[0];
$where = $matches[2]; # filepath/filename
$where = $where[0];
$ext = $matches[3]; # ext
$ext = $ext[0];

Errors::debugLogger(sprintf('Size: %s Where: %s Ext: %s', $size, $where, $ext));

# Ensure user has access to requested folder
Errors::debugLogger(sprintf('Checking if user has access to requested size (%s) and folder (%s)', $size, $where));
$allowedByACL = FALSE;

// Admins have access to everything
if (!empty($_SESSION['user'])
            && $_SESSION['user']['usergroup']['usergroupName'] == "Administrators")
{
    Errors::debugLogger("(Administrators) Setting AllowedByACL to TRUE...");
    $allowedByACL = TRUE;
}
// If user is store owner, ensure path begins with Stores/# and ensure user has permission to that store
elseif (!empty($_SESSION['user'])
            && $_SESSION['user']['usergroup']['usergroupName'] == "Store Owners")
{
    $r = preg_match_all('/(^Stores\/)(\d)\//', $where, $matches);
    $storeID = 0;
    if (empty($r))
    {
        Errors::debugLogger("File path ($where) does NOT begin with Stores/#...",10);
    }
    else
    {
        // Requested File's store ID
        $storeID = $matches[2];
        $storeID = $storeID[0];
        Errors::debugLogger("File path ($where) begins with Stores/#...",10);
        Errors::debugLogger("Store ID: ".$storeID,10);
    }
    
    // Load store to find what Brand it belongs to
    $store = new Store();
    $storeFile = $store->getSingle(array('storeID' => $storeID));
    $storeFileBrandID = $storeFile['brandID'];
    Errors::debugLogger("storeFileBrandID: ",$storeFileBrandID,10);
    
    //if ($storeID != $_SESSION['user']['usergroup']['storeID'])
    if ($storeFileBrandID != $_SESSION['brandID'])
    {
        Errors::debugLogger("Requested path store ID (brandID) does NOT match store owner ID (brandID)!");
        
        // If visitor is viewing a page created by store owner then allow image viewing
        if ($_SESSION['controller'] == 'pages' && $_SESSION['action'] == 'read')
        {
            Errors::debugLogger("Overriding ALLOW ACL for Pages/Read");
            $allowedByACL = TRUE;
        }
        
    } else {
        Errors::debugLogger("Requested path store ID (brandID) matches store owner ID (brandID)");
        Errors::debugLogger("Setting AllowedByACL to TRUE...");
        $allowedByACL = TRUE;
    }
}
else
{
    # Public/Anonymous
    Errors::debugLogger("Temporarily allowing guest access...",10);
    $allowedByACL = TRUE;
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
    header("Content-Type: application/octet-stream");
    if ($mode == "inline")
    {
        $image_mime = image_type_to_mime_type(exif_imagetype($file));
        Errors::debugLogger("Image mime: ".$image_mime,10);
        header("Content-Type: ".$image_mime); 
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