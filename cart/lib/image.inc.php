<?php
namespace killerCart;

/**
 * Image class
 *
 * Image storage, retrieval, modification
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @copyright 2014 Goin' Postal Franchise Corporation
 * @license   Private
 * @version   Release: v01.06.00
 * @since     Release: v01.02.06
 */
class Image
{
    /**
     * Database connection
     * Database query sql
     *
     * @var PDO $DB
     * @var string $sql
     * @var array $sqlData
     */
    protected $DB, $sql, $sqlData;

    /**
     * Class data
     *
     * @var array $data
     */
    protected $data = array();

    /**
     * Main magic methods
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $this->DB = new \Database();
    }

    public function __destruct()
    {
        unset($this->sql, $this->sqlData);
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __get($key)
    {
        if ($this->__isset($key)) {
            return $this->data[$key];
        }
        return false;
    }

    public function __isset($key)
    {
        if (array_key_exists($key, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * saveImageInfo
     * 
     * Saves image info to database
     * 
     * @version v01.06.00
     * 
     * @since v01.03.00
     * 
     * @param int $cartID
     * @param int $id
     * @param int $parentID
     * @param int $active
     * @param string $name
     * @param string $ext
     * @param int $width
     * @param int $height
     * @param string $orient
     * @param int $userID
     * @param int $customerID
     * @param int $categoryID
     * @param int $productID
     * @param int $sortOrder
     * @param int $showInCarousel
     * @param int $showInCarouselThumbs
     * 
     * @return int
     */
    public function saveImageInfo($cartID, $imageID, $parentID, $active, $name, $ext, $width, $height, $orient = NULL,
                                  $userID = NULL, $customerID = NULL, $categoryID = NULL, $productID = NULL, $sortOrder = NULL,
                                  $showInCarousel = NULL, $showInCarouselThumbs = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO images
                (imageID, parentImageID, imageActive, imageName, imageExt, imageWidth, imageHeight, imageOrientation, imageDateCreated, cartID, userID, customerID, categoryID, productID, imageSortOrder, showInCarousel, showInCarouselThumbs)
            VALUES
                (:imageID, :parentImageID, :imageActive, :imageName, :imageExt, :imageWidth, :imageHeight, :imageOrientation, :imageDateCreated, :cartID, :userID, :customerID, :categoryID, :productID, :imageSortOrder, :showInCarousel, :showInCarouselThumbs)
            ON DUPLICATE KEY UPDATE
                imageID = :imageID, parentImageID = :parentImageID, imageActive = :imageActive, imageName = :imageName, imageExt = :imageExt,
                imageWidth = :imageWidth, imageHeight = :imageHeight, imageOrientation = :imageOrientation, imageDateCreated = :imageDateCreated,
                cartID = :cartID, userID = :userID, customerID = :customerID, categoryID = :categoryID, productID = :productID, imageSortOrder = :imageSortOrder, showInCarousel = :showInCarousel, showInCarouselThumbs = :showInCarouselThumbs";
        $this->sqlData = array(':imageID'          => $imageID,
            ':parentImageID' => $parentID,
            ':imageActive'      => $active,
            ':imageName'        => $name,
            ':imageExt'   => $ext,
            ':imageWidth'       => $width,
            ':imageHeight'      => $height,
            ':imageOrientation' => $orient,
            ':imageDateCreated' => Util::getDateTimeUTC(),
            ':cartID' => $cartID,
            ':userID' => $userID,
            ':customerID' => $customerID,
            ':categoryID' => $categoryID,
            ':productID' => $productID,
            ':imageSortOrder' => $sortOrder,
            ':showInCarousel' => $showInCarousel,
            ':showInCarouselThumbs' => $showInCarouselThumbs);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if ($imageID == 'DEFAULT') {
            return $res;
        }
        return $imageID;
    }
    
    /**
     * setImageActive
     * 
     * Sets image (parent and children) to active/inactive
     * 
     * @since v01.03.00
     * 
     * @version v01.03.00
     * 
     * @param int $cartID
     * @param int $imageID
     * @param int $active 1=Active, 0=Inactive
     * 
     * @return boolean
     */
    public function setImageActive($cartID, $imageID, $active) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            UPDATE images
            SET imageActive = :imageActive
            WHERE cartID = :cartID
                AND (imageID = :imageID
                    OR
                    parentImageID = :imageID)";
        $this->sqlData = array(':cartID' => $cartID,
            ':imageID' => $imageID,
            ':imageActive' => $active);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getProductImageMaxSortOrder
     * 
     * @version v01.05.02
     * 
     * @since v01.05.02
     * 
     * @param type $productID
     * 
     * @return int
     */
    public function getProductImageMaxSortOrder($productID) {
        $this->sql = "
            SELECT MAX(imageSortOrder) AS imageSortOrderMax
            FROM images
            WHERE productID = :productID
            AND showInCarousel = 1";
        $this->sqlData = array(':productID' => $productID);
        $max = $this->DB->query($this->sql, $this->sqlData);
        return $max[0]['imageSortOrderMax'];
    }
    
    /**
     * updateProductImageSortOrder
     * 
     * Changes sort order of image
     * 
     * @version v01.05.02
     * 
     * @since v01.05.02
     * 
     * @param type $productID
     * @param type $imageID
     * @param type $oldOrder
     * @param type $newOrder
     * 
     * @return boolean
     */
    public function updateProductImageSortOrder($productID, $imageID, $oldOrder, $newOrder) {
        // Get option ID with desired sort order to replace (or nearest if deletion gaps)
        if ($oldOrder < $newOrder) {
            $symbol = ">"; // Greater than
            $order = "ASC";
        } else {
            $symbol = "<"; // Less than
            $order = "DESC";
        }
        $this->sql = "
            SELECT imageID, imageSortOrder
            FROM  images
            WHERE productID = :productID
            AND imageSortOrder ".$symbol."= :newOrder
            ORDER BY imageSortOrder ".$order.", imageID DESC
            LIMIT 1";
        $this->sqlData = array(':productID' => $productID,
            ':newOrder' => $newOrder);
        $existing = $this->DB->query($this->sql, $this->sqlData);
        
        // Update that ID to use oldOrder (swap)
        if (!empty($existing)) {
            $existingID = $existing[0]['imageID'];
            $existingOrder = $existing[0]['imageSortOrder'];
            $this->sql = "
                UPDATE images
                SET imageSortOrder = :oldOrder
                WHERE imageID = :existingID
                LIMIT 1";
            $this->sqlData = array(':oldOrder' => $oldOrder,
                ':existingID' => $existingID);
            $update = $this->DB->query($this->sql, $this->sqlData);
        } else {
            $existingOrder = $newOrder;
        }
        // Update desired option desired sort order
        $this->sql = "
            UPDATE images
            SET imageSortOrder = :newOrder
            WHERE imageID = :imageID
            LIMIT 1";
        $this->sqlData = array(':newOrder' => $existingOrder,
            ':imageID' => $imageID);
        $new = $this->DB->query($this->sql, $this->sqlData);
        
        return true;
    }
    
    /**
     * setImageVisibility
     * 
     * Sets image visibility
     * 
     * @version v01.06.00
     * 
     * @since v01.03.00
     * 
     * @param int $cartID
     * @param int $imageID
     * @param int $showInCarousel
     * @param int $showInCarouselThumbs
     * 
     * @return boolean
     */
    public function setImageVisibility($cartID, $imageID, $showInCarousel, $showInCarouselThumbs) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        
        echo "lib-ShowInCar: $showInCarousel ShowInCarThumbs: $showInCarouselThumbs";
        echo "\n\n";
        
        // ShowInCarousel toggle
        if ($showInCarousel == 1 || $showInCarousel == 0
                && !isset($showInCarouselThumbs))
        {
            echo "lib-ShowInCarousel $showInCarousel toggle";
            $this->sql = "
                UPDATE images
                SET showInCarousel = :showInCarousel
                WHERE cartID = :cartID
                    AND imageID = :imageID";
            $this->sqlData = array(':cartID' => $cartID,
                ':imageID' => $imageID,
                ':showInCarousel' => $showInCarousel);
            $this->DB->query($this->sql, $this->sqlData);
            
            // Hide thumbs if hiding carousel
            if ($showInCarousel == 0)
            {
                $showInCarouselThumbs = 0;
            }
        }
        
        // ShowInCarouselThumbs toggle
        if ($showInCarouselThumbs == 1 || $showInCarouselThumbs == 0)
        {
            echo "lib-ShowInCarouselThumbs $showInCarouselThumbs toggle";
            $this->sql = "
                UPDATE images
                SET showInCarouselThumbs = :showInCarouselThumbs
                WHERE cartID = :cartID
                    AND imageID = :imageID";
            $this->sqlData = array(':cartID' => $cartID,
                ':imageID' => $imageID,
                ':showInCarouselThumbs' => $showInCarouselThumbs);
            $this->DB->query($this->sql, $this->sqlData);
        }
    }

    /**
     * getImageParentInfo
     * 
     * Gets parent image info from database
     * 
     * @version v01.06.00
     * 
     * @since v01.03.00
     * 
     * @param int $cartID
     * @param int $active 2=All, 1=Active, 0=Inactive
     * @param int $productID
     * @param int $categoryID
     * @param int $customerID
     * @param int $userID
     * @param int $showInCarousel Optional, defaults to showInCarousel only, anything else = all
     * 
     * @return array Image info
     */
    public function getImageParentInfo($cartID, $active = NULL, $productID = NULL, $categoryID = NULL, $customerID = NULL,
            $userID = NULL, $showInCarousel = NULL)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $this->sql     = "
            SELECT imageID, parentImageID, imageActive, imageName, imageExt, imageWidth, imageHeight, imageOrientation, imageDateCreated,
                   cartID, userID, customerID, categoryID, productID, imageSortOrder, showInCarousel, showInCarouselThumbs
            FROM images
            WHERE parentImageID IS NULL
                AND cartID = :cartID";
        $this->sqlData = array(':cartID' => $cartID);
        if ($active !== NULL) {
            $this->sql .= ' AND imageActive = :imageActive';
            $this->sqlData[':imageActive'] = $active;
        }
        if (!empty($productID)) {
            $this->sql .= ' AND productID = :productID';
            $this->sqlData[':productID'] = $productID;
        } else {
            $this->sql .= ' AND productID IS NULL';
        }
        if (!empty($categoryID)) {
            $this->sql .= ' AND categoryID = :categoryID';
            $this->sqlData[':categoryID'] = $categoryID;
        } else {
            $this->sql .= ' AND categoryID IS NULL';
        }
        if (!empty($customerID)) {
            $this->sql .= ' AND customerID = :customerID';
            $this->sqlData[':customerID'] = $customerID;
        } else {
            $this->sql .= ' AND customerID IS NULL';
        }
        if (!empty($userID)) {
            $this->sql .= ' AND userID = :userID';
            $this->sqlData[':userID'] = $userID;
        } else {
            $this->sql .= ' AND userID IS NULL';
        }
        //if (empty($showInCarousel)) { // ????
            // Default to showing only showInCarousel images
            //$this->sql .= ' AND showInCarousel = :showInCarousel';
            //$this->sqlData[':showInCarousel'] = 1;
        //}
        $this->sql .= " ORDER BY imageSortOrder, imageID ASC";
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getImageChildren
     * 
     * Gets info of all children images
     * 
     * @version v01.06.00
     * 
     * @since v01.03.00
     * 
     * @param int $cartID
     * @param int $parentImageID
     * 
     * @return array|boolean
     */
    public function getImageChildren($cartID, $parentImageID) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT imageID, parentImageID, imageActive, imageName, imageExt, imageWidth, imageHeight, imageOrientation, imageDateCreated, cartID, userID, customerID, categoryID, productID, imageSortOrder, showInCarousel, showInCarouselThumbs
            FROM images
            WHERE cartID = :cartID
            AND parentImageID = :parentImageID";
        $this->sqlData = array(':cartID' => $cartID,
            ':parentImageID' => $parentImageID);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    public function getCartImages($cartID) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
          SELECT *
          FROM images
          WHERE cartID = :cartID
          AND parentImageID IS NULL";
        $this->sqlData = array(':cartID' => $cartID);        
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * getBestFitImage
     * 
     * Returns best image dimensions to use for desired dimensions
     * 
     * @since v01.03.00
     * 
     * @version v01.03.00
     * 
     * @param int $cartID
     * @param int $parentImageID
     * @param int $parentWidth
     * @param int $parentHeight
     * @param int $desiredWidth
     * @param int $desiredHeight
     * 
     * @return array 
     */
    public function getBestFitImage($cartID, $parentImageID, $parentWidth, $parentHeight, $desiredWidth, $desiredHeight) {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        // Find best child image to fit our desired max w/h
        $thisImgMaxW = 0;
        $thisImgMaxH = 0;
        if (($parentWidth <= $desiredWidth)
                && ($parentHeight <= $desiredHeight)) {
            $thisImgMaxW = $parentWidth;
            $thisImgMaxH = $parentHeight;
        }
        $imageSizes = self::getImageChildren($cartID, $parentImageID);
        foreach ($imageSizes as $size) {
            if (($size['imageWidth'] <= $desiredWidth)
                && ($size['imageHeight'] <= $desiredHeight)) {

                // Within limits
                if (($size['imageWidth'] > $thisImgMaxW)
                    && ($size['imageHeight'] > $thisImgMaxH)) {

                    // New Best Fit
                    $thisImgMaxW = $size['imageWidth'];
                    $thisImgMaxH = $size['imageHeight'];
                }
            }
        }
        if ($thisImgMaxW == 0
                || $thisImgMaxH == 0) {
            // Orig is smallest we have?
            $thisImgMaxW = $parentWidth;
            $thisImgMaxH = $parentHeight;
            $showImgW = $parentWidth; // Forces img to shrink to desired width
        } else {
            $showImgW = $thisImgMaxW; // Uses actual img width as its right
        }
        $res = array('bestWidth' => $thisImgMaxW,
            'bestHeight' => $thisImgMaxH,
            'showWidth' => $showImgW);
        return $res;
    }
   
    /**
     * getImageInfoByID
     * 
     * Gets image details by image ID
     * 
     * @version v01.06.00
     * 
     * @since v01.02.06
     * 
     * @param int $imageID Image ID
     * @param int $imageActive Optional 1=Active, 0=Inactive
     * 
     * @return array Info
     * 
     * @deprecated since version v01.03.00
     */
    public function getImageInfoByID($imageID, $imageActive = NULL)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $this->sql = "
            SELECT
                imageID, parentImageID, imageActive, imageName, imageExt, imageWidth, imageHeight, imageOrientation, imageDateCreated,
                cartID, userID, customerID, categoryID, productID, imageSortOrder, showInCarousel, showInCarouselThumbs
            FROM images
            WHERE imageID = :imageID";
        if ($imageActive !== NULL) {
            $this->sql .= " AND imageActive = " . $imageActive;
        }
        $this->sqlData = array(':imageID' => $imageID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (empty($res)) {
            return false;
        }
        return $res[0];
    }

    /**
     * displayImage
     * 
     * Serves cached copy of image or serves image for first time if no cache
     * 
     * @since v1.2.5
     * 
     * @version v1.2.6
     * 
     * @param string $imagePath Absolute path to image file to serve
     * 
     * @todo verify cache working with getfile
     */
    public function displayImage($imagePath)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        session_cache_limiter('private');
        #$cache_limiter = session_cache_limiter(); // Needed?
        session_cache_expire((60 * 60 * 24 * 45)); // Set cache minutes as desired
        #$cache_expire = session_cache_expire(); // Needed?
        date_default_timezone_set('GMT');
        ini_set('date.timezone', 'America/New_York');
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
        } else {
            $if_modified_since = '';
        }
        $mtime      = filemtime($imagePath);
        $gmdate_mod = gmdate('D, d M Y H:i:s', $mtime) . ' GMT';
        if ($if_modified_since == $gmdate_mod) {
            \Errors::debugLogger(__METHOD__ . ' Serving cached image', 10);
            header('Last-Modified: ' . date('D, d M Y H:i:s', time() + (60 * 60 * 24 * 45)) . ' GMT', true, 304);
            exit;
        }
        \Errors::debugLogger(__METHOD__ . ' Serving raw image (no cache)', 8);
        // Not cached, send file
        $dateExpires = gmdate('D, d M Y H:i:s') + (60 * 60 * 24 * 45);
        header("Last-Modified: $gmdate_mod", true, 200);
        header("Content-Length: " . filesize($imagePath));
        header("Content-Type: image/png");
        header('Expires: ' . $dateExpires . ' GMT', true, 200);
        readfile($imagePath);
        exit;
    }
    
    /**
     * replaceProductImage
     * 
     * Updates product options using old image to new image
     * 
     * @version v01.05.02
     * 
     * @since v01.05.00
     * 
     * @uses setImageActive()
     * 
     * @param int $cartID
     * @param int $origImgID
     * @param int $newImgID
     * 
     * @return boolean
     */
    public function replaceProductImage($cartID, $origImgID, $newImgID) {
        $this->sql = "
            UPDATE productOptionsChoices
            SET choiceImageID = :newImgID
            WHERE choiceImageID = :origImgID";
        $this->sqlData = array(':origImgID' => $origImgID,
            ':newImgID' => $newImgID);
        $res = $this->DB->query($this->sql, $this->sqlData);
        
        $this->sql = "
            UPDATE productOptionsChoicesCustom
            SET choiceImageIDCustom = :newImgID
            WHERE choiceImageIDCustom = :origImgID";
        $this->sqlData = array(':origImgID' => $origImgID,
            ':newImgID' => $newImgID);
        $res = $this->DB->query($this->sql, $this->sqlData);
        // Set old image to inactive
        if (isset($res)) {
            $resb = $this->setImageActive($cartID, $origImgID, 0);
            if (isset($resb)) {
                return true;
            }
        }
        return false;
    }
    
    public static function saveStorefrontCarouselImage($cartID, $slideno, $origImgID, $name, $ext, $width, $height, $orient)
    {
      // Save img to DB
      $thisImgID = $this->saveImageInfo($cartID, $origImgID, NULL, 1, $name, $ext, $width, $height, $orient);
      
      $this->sql = "
        SELECT *
        FROM storefrontCarousel
        WHERE cartID = :cartID";
      $this->sqlData = array(':cartID' => $cartID);
      $res = $this->DB->query($this->sql, $this->sqlData);
      if (empty($res))
      {
        // Insert first row for cart
        $this->sql = "
          INSERT INTO storefrontCarousel
          (slide".$slideno."ImageID, cartID)
          VALUES
          (:slideImageID, :cartID)";
        $this->sqlData = array(':slideImageID' => $thisImgID,
            ':cartID' => $cartID);
        $res = $this->DB->query($this->sql, $this->sqlData);
      }
      else
      {
          // Replace existing
          $this->sql = "
            UPDATE storefrontCarousel
            SET slide".$slideno."ImageID = :slideImageID
            WHERE cartID = :cartID";
          $this->sqlData = array(':slideImageID' => $thisImgID,
              ':cartID' => $cartID);
          $res = $this->DB->query($this->sql, $this->sqlData);
      }
      return $thisImgID;
    }

    /*****************************************/
    /*****************************************/
    /*****************************************/
    
    /**
     * getImage
     * 
     * Displays requested image or creates size if doesnt exist
     * 
     * @version v01.03.00
     * 
     * @since v1.2.6
     * 
     * @uses createImage()
     * @uses displayImage()
     * 
     * @param int $cartID
     * @param string $imageName
     * @param string $imageSize
     * @param int $width
     * @param int $height
     * @param int $categoryID Optional
     * @param int $productID Optional
     * 
     * @deprecated since version v01.03.00
     */
    public function getImage($cartID, $imageName, $imageSize, $width, $height, $categoryID = NULL, $productID = NULL,
                             $textOverlay = NULL, $fontFile = NULL, $fontSize = NULL, $fontColor = NULL, $fontPosition = NULL,
                             $xOffset = NULL, $yOffset = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 9);

        // Construct Path
        $origImg = $cartID . '/';
        if (!empty($categoryID)) {
            $origImg .= $categoryID . '/';
        }
        if (!empty($productID)) {
            $origImg .= $productID . '/';
        }

        // Separate name.ext
        $fileExt  = substr($imageName, strrpos($imageName, '.') + 1);
        $fileName = str_replace("." . $fileExt, "", $imageName);
        $newName  = strtolower($imageSize);
        // Get Img
        $getImg   = cartImagesDir . $origImg . $fileName . '.' . $newName . '.png';
        // Create Img if needed
        if (!is_file($getImg)) {
            \Errors::debugLogger(__METHOD__ . ' Creating image which does not exist... ' . $getImg, 5);
            // Create new image from original (resize if applicable)
            $origImg = cartImagesDir . $origImg . $imageName;
            $getImg  = self::createImage($origImg, $getImg, $width, $height, $textOverlay, $fontFile, $fontSize, $fontColor,
                                         $fontPosition, $xOffset, $yOffset);
        }
        self::displayImage($getImg);
    }

    /**
     * createImage
     * 
     * Creates new image from original
     * Optional text overlay can be added
     * 
     * @version v01.03.00
     * 
     * @since v1.2.6
     * 
     * @uses SimpleImage()
     * 
     * @param string $origImg
     * @param string $newImg
     * @param int $newWidth
     * @param int $newHeight
     * @param string $textOverlay
     * @param string $fontFile
     * @param int $fontSize
     * @param string $fontColor
     * @param string $fontPosition
     * @param int $xOffset
     * @param int $yOffset
     * 
     * @return boolean|string
     */
    public function createImage($origImg, $newImg, $newWidth, $newHeight, $textOverlay = NULL, $fontFile = NULL, $fontSize = NULL,
                                $fontColor = NULL, $fontPosition = NULL, $xOffset = NULL, $yOffset = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 9);

        // Verify original file exists
        if (!is_file($origImg)) {
            \Errors::debugLogger(__METHOD__ . ' Missing orig image ' . $origImg);
            trigger_error('Missing orig image: '.$origImg, E_USER_ERROR);
            return false;
        }
        // Create new image if needs to be resized
        $simpleImage = new SimpleImage($origImg);

        // Text overlay? (Pre-resize!)
        if ($textOverlay !== NULL) {
            \Errors::debugLogger(__METHOD__ . ' Adding Text Overlay', 5);
            $simpleImage->text($textOverlay, cartAdminDir . '../css/' . $fontFile, $fontSize, $fontColor, $fontPosition,
                               $xOffset, $yOffset);
        }

        // Resize
        $simpleImage->resize($newWidth, $newHeight);

        // Save new image
        $simpleImage->save($newImg);

        return $newImg;
    }

}