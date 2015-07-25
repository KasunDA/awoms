<?php
namespace killerCart;

/**
 * Product_Category class
 *
 * Product Category management methods
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 */
class Product_Category
{
    /*
     * Database connection
     * Database query sql
     */
    protected $DB, $sql, $sqlData;

    /*
     * Product_Category class data
     */
    protected $data = array();

    /*
     * Main magic methods
     */
    public function __construct()
    {
        \Errors::debugLogger(__METHOD__, 10);
        $this->DB = new \Database();
    }

    public function __destruct()
    {
        // \Errors::debug_logger("__destruct():");
        unset($this->DB, $this->sql);
    }

    public function __set($key, $value)
    {
        // \Errors::debug_logger("__set():");
        $this->data[$key] = $value;
    }

    public function __get($key)
    {
        // \Errors::debug_logger("__get():");
        if ($this->__isset($key)) {
            return $this->data[$key];
        }
        return false;
    }

    public function __isset($key)
    {
        // \Errors::debug_logger("__isset():");
        if (array_key_exists($key, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * saveCategory
     * 
     * Create or update product category
     * 
     * @version v0.0.1
     * 
     * @param int $cartID
     * @param int $categoryID
     * @param int $status
     * @param int $parentID
     * @param int $level
     * @param string $name
     * @param string $code
     * @param string $descPub
     * @param string $descPriv
     * @param int $taxable
     * @param int $private
     * @param int $showPrice
     * 
     * @return boolean|int False on failure, true when updating existing, int when creating new
     */
    public function saveCategory($cartID, $categoryID, $status, $parentID, $level, $name, $code, $descPub, $descPriv, $taxable,
                                 $private, $showPrice)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			INSERT INTO productCategories
                (categoryID, categoryCode, categoryActive, categoryName, categoryDescriptionPublic, categoryDescriptionPrivate,
                categoryTaxable, categoryPrivate, categoryShowPrice)
			VALUES
                (:categoryID, :category_code, :active, :name, :description, :descPrivate, :taxable, :private, :showPrice)
			ON DUPLICATE KEY UPDATE categoryID = :categoryID,
									categoryCode = :category_code,
									categoryActive = :active,
									categoryName = :name,
									categoryDescriptionPublic = :description,
                                    categoryDescriptionPrivate = :descPrivate,
                                    categoryTaxable = :taxable,
                                    categoryPrivate = :private,
                                    categoryShowPrice = :showPrice";
        $sql_data  = array(':categoryID'    => $categoryID,
            ':category_code' => $code,
            ':active'        => $status,
            ':name'          => $name,
            ':description'   => $descPub,
            ':descPrivate'   => $descPriv,
            ':taxable'       => $taxable,
            ':private'       => $private,
            ':showPrice'     => $showPrice);
        $returnID  = $this->DB->query($this->sql, $sql_data);
        if (!isset($returnID)) {
            trigger_error("Error #PC002: Invalid results.", E_USER_ERROR);
            return false;
        }

        // Create Category<->Cart Reference
        if ($categoryID == 'DEFAULT') {
            $categoryID = $returnID;
        }
        if ($parentID == NULL) {
            $parentID = 'NULL';
        }
        if ($level == NULL) {
            $level = 'NULL';
        }

        \Errors::debugLogger(__METHOD__ . ' returnID:' . $returnID . ' categoryID:' . $categoryID);

        $this->sql     = "
			INSERT INTO refCartsProductCategories
                (cartID, categoryID, parentCategoryID, levelNumber)
			VALUES
                (:cartID, :categoryID, :parentCategoryID, :levelNumber)
			ON DUPLICATE KEY UPDATE cartID = :cartID,
									categoryID = :categoryID,
                                    parentCategoryID = :parentCategoryID,
                                    levelNumber = :levelNumber";
        $this->sqlData = array(':cartID'          => $cartID,
            ':categoryID'       => $categoryID,
            ':parentCategoryID' => $parentID,
            ':levelNumber'      => $level);
        $this->DB->query($this->sql, $this->sqlData); // No return value, database class will catch exception
        // Image Upload
        $frmFilesID    = "category_logo";
        if (isset($_FILES[$frmFilesID])) {
            foreach ($_FILES[$frmFilesID] as $key => $value) {
                // Verify each file to upload has no error
                if ($key == "error") {
                    $iu_count = 0;
                    foreach ($value as $thisError) {
                        // http://php.net/manual/en/features.file-upload.errors.php
                        if ($thisError == 4) {
                            continue;
                        }
                        if ($thisError != 0) {
                            trigger_error("Error #S015: Invalid image.", E_USER_ERROR);
                            return false;
                        }
                        $iu_count++;
                    }
                }
                // Verify each file to upload has valid size		
                if ($key == "size") {
                    $iu_count = 0;
                    foreach ($value as $thisSize) {
                        if ($thisSize == 0) {
                            if ($thisError == 4) {
                                continue;
                            }
                            trigger_error("Error #S016: Invalid image.", E_USER_ERROR);
                            return false;
                        }
                        $iu_count++;
                    }
                }
            } #end:foreach
        } #end:if $_FILES
        if (!empty($iu_count)) {
            if (!Product_Category::handleImageUpload($frmFilesID, 0, $cartID, $categoryID)) {
                echo "<br /><strong>NOTE:</strong> Failed to upload file 0...";
            }
        }
        return $categoryID;
    }

    /*
     * handleImageUpload
     */
    private function handleImageUpload($frmFilesID = false, $thisFile = false, $cartID, $categoryID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $fileName = $_FILES["$frmFilesID"]['name'][$thisFile];
        $tmpName  = $_FILES["$frmFilesID"]['tmp_name'][$thisFile];
        if (!is_uploaded_file($tmpName)) {
            trigger_error("Error #S011: Security block.", E_USER_ERROR);
            return false;
        }
        $fileSize = $_FILES["$frmFilesID"]['size'][$thisFile];
        $fileType = $_FILES["$frmFilesID"]['type'][$thisFile];
        $fileExt  = pathinfo($fileName, PATHINFO_EXTENSION);
        // Make nested folder path
        $destPath = cartImagesDir . $cartID . '/' . $categoryID;
        Util::createNestedDirectory($destPath);

        // Test if file already exists (auto-delete)
        if (file_exists($destPath . '/' . $categoryID . '.' . $fileExt)) {
            $unlink = unlink($destPath . '/' . $categoryID . '.' . $fileExt);
        }
        // Move uploaded file from tmp
        if (move_uploaded_file($tmpName, $destPath . '/' . $categoryID . '.' . $fileExt)) {
            return true;
        } else {
            trigger_error("Error #S014: Invalid results.", E_USER_NOTICE);
            return false;
        }
    }

    /**
     * getCategoryCount
     * 
     * @param string $type
     * 
     * @return boolean
     */
    public function getCategoryCount($type = false, $cartID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (strtolower($type) == "all") {
            $whereSql = "ref.categoryID IS NOT NULL";
        } elseif (strtolower($type) == "active") {
            $whereSql = "pc.categoryActive='1'";
        } elseif (strtolower($type) == "inactive") {
            $whereSql = "pc.categoryActive='0'";
        }
        if (!empty($cartID)) {
            $whereSql .= " AND ref.cartID = " . $cartID;
        }
        $this->sql  = "
			SELECT COUNT(ref.categoryID) as Total
			FROM refCartsProductCategories AS ref
                INNER JOIN productCategories AS pc
                    ON ref.categoryID = pc.categoryID
			WHERE " . $whereSql;
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr[0]['Total'];
        } else {
            return false;
        }
    }

    /**
     * getCategoryIDs
     * 
     * @version v0.0.1
     * 
     * @param string $type
     * @param int $cartID
     * @param string $orderBy Optional column to orderBy (e.g. categoryName or categoryName DESC)
     * 
     * @return boolean
     */
    public function getCategoryIDs($type, $cartID = NULL, $orderBy = NULL, $customWhere = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Type
        if (strtolower($type) == "all") {
            $whereSql = "ref.categoryID IS NOT NULL";
        } elseif (strtolower($type) == "active") {
            $whereSql = "pc.categoryActive='1'";
        } elseif (strtolower($type) == "inactive") {
            $whereSql = "pc.categoryActive='0'";
        }
        // Cart ID
        if (!empty($cartID)) {
            $whereSql .= " AND ref.cartID = " . $cartID;
        }
        // Custom Where
        if (!empty($customWhere)) {
            $whereSql .= " AND " . $customWhere;
        }
        // Order By
        if (!empty($orderBy)) {
            $whereSql .= " ORDER BY " . $orderBy;
        }
        $this->sql  = "
            SELECT DISTINCT ref.categoryID, ref.parentCategoryID, ref.levelNumber,
                pc.categoryCode, pc.categoryActive, pc.categoryName, pc.categoryDescriptionPublic, pc.categoryDescriptionPrivate, pc.categoryTaxable, pc.categoryPrivate
            FROM refCartsProductCategories AS ref
                INNER JOIN productCategories AS pc
                    ON ref.categoryID = pc.categoryID
            WHERE " . $whereSql;
        // Orders by parent category Name then child cat name
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr;
        } else {
            return false;
        }
    }

    /**
     * getCategoryChildren
     * 
     * Finds child categories with the matching parent category ID
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $categoryID CategoryID to find children of
     * @param int $active Optional 1=Active, 0=Inactive
     * @param string $orderBy Optional orderBy column (e.g. categoryName or categoryName DESC)
     * 
     * @return array Child Categories
     */
    public function getCategoryChildren($categoryID, $active = NULL, $orderBy = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            SELECT ref.categoryID, ref.levelNumber
            FROM refCartsProductCategories AS ref
                INNER JOIN productCategories AS pc
                    ON ref.categoryID = pc.categoryID
            WHERE ref.parentCategoryID = :categoryID";
        if (!empty($active)) {
            $this->sql .= " AND pc.categoryActive = " . $active;
        }
        if (!empty($orderBy)) {
            $this->sql .= " ORDER BY " . $orderBy;
        }
        $this->sqlData = array(':categoryID' => $categoryID);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getCategoryInfo
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $categoryID Category ID
     * 
     * @return array Category info
     */
    public function getCategoryInfo($categoryID)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        if (empty($categoryID))
        {
            \Errors::debugLogger(__METHOD__." categoryID is Empty!", 1);
            return;
        }
        $this->sql     = "
			SELECT pc.categoryID, pc.categoryCode, pc.categoryActive, pc.categoryName, pc.categoryDescriptionPublic, pc.categoryDescriptionPrivate,
                pc.categoryTaxable, pc.categoryPrivate, pc.categoryShowPrice,
					ref.cartID, ref.parentCategoryID, ref.levelNumber
			FROM productCategories AS pc
				INNER JOIN refCartsProductCategories AS ref
					ON pc.categoryID = ref.categoryID
			WHERE pc.categoryID = :categoryID
            LIMIT 1";
        $this->sqlData = array(':categoryID' => $categoryID);
        $r             = $this->DB->query($this->sql, $this->sqlData);
        return $r[0];
    }

    /**
     * getCategoryImage
     * 
     * Gets category image path
     * 
     * @version v0.0.1
     * 
     * @param int $cartID
     * @param int $categoryID
     * 
     * @return boolean|string Img path
     * 
     * @deprecated since version v0.0.1
     */
    public function getCategoryImage($cartID, $categoryID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $imgDir = cartImagesDir . $cartID . '/' . $categoryID . '/';
        if (is_dir($imgDir)) {
            $dh       = opendir($imgDir);
            while (false !== ($filename = readdir($dh))) {
                // Skip directories
                if (!is_file($imgDir . $filename)) {
                    continue;
                }
                $ext = substr($filename, strrpos($filename, '.') + 1);
                if (in_array($ext, array("jpg", "jpeg", "png", "gif"))) {
                    // Category image <categoryID>.<ext>
                    if ($filename == $categoryID . "." . $ext) {
                        return $filename;
                    }
                }
            }
        }
        return false;
    }

    /**
     * getCategoryPrivacy
     * 
     * Gets privacy status of category
     * 
     * @since v0.0.1
     * 
     * @param int $categoryID Category ID
     * 
     * @return boolean|int 1=private, 0=public
     */
    public function getCategoryPrivacy($categoryID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT categoryPrivate
            FROM productCategories
            WHERE categoryID = :categoryID
            LIMIT 1";
        $this->sqlData = array(':categoryID' => $categoryID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $res[0]['categoryPrivate'];
    }

    /**
     * getCategoryTaxable
     * 
     * @version v0.0.1
     * 
     * Gets taxable status of category
     * 
     * @param int $categoryID Category ID
     * 
     * @return boolean|int 1=taxable, 0=nontaxable
     */
    public function getCategoryTaxable($categoryID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT categoryTaxable
            FROM productCategories
            WHERE categoryID = :categoryID
            LIMIT 1";
        $this->sqlData = array(':categoryID' => $categoryID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        return $res[0]['categoryTaxable'];
    }

    /**
     * updateCategorySettings
     * 
     * @return boolean
     * 
     * @deprecated since version v0.0.1
     */
    public function updateCategorySettings()
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        /*
         * PURPOSE: Updates product category with data from user form.
         * [ ] 1. Sanitize data from user form
         * [/] 2. Take details from user form and update settings table using (make deleting and inserting new options possible))
         * [ ] 3. Ensure required fields only allow updating VALUE (not name/type)
         */
        if (
                !isset($_POST['step']) || !isset($_POST['category_status'])
        ) {
            trigger_error("Error #95: Please check your form and try again.", E_USER_ERROR);
            return false;
        }

        # Set $this->cartID
        $this->getCategoryCartID();

        // 1
        /* (settings can be array so go through each setting#Name/type/value */

        // Sanitize!
        // [ ] Separate table ...
        $thisCategoryStatus = trim($_POST['category_status']);
        if ($thisCategoryStatus == 'Active') {
            $thisCategoryStatus = 1;
        } else {
            $thisCategoryStatus = 0;
        }
        $this->sql = "UPDATE `" . DBNAME . "`.`productCategories`
						SET active = '" . $thisCategoryStatus . "'
						WHERE `id` = '" . $this->id . "'
							AND	`cartID` = '" . $this->cartID . "'";

        $returnID = $this->DB->query($this->sql);
        if (!isset($returnID)) {
            trigger_error("Error #117: ", E_USER_ERROR);
            return false;
        }

        // Begin constructing sql statement
        $mySql = "INSERT INTO `" . DBNAME . "`.`productCategories_settings`
					(`id`, `cartID`, `categoryID`, `name`, `type`, `value`)
					VALUES";

        // Add to sql statement foreach user setting from form
        foreach ($_POST as $key => $value) {
            // Sanitize!
            $key   = trim($key);
            $value = trim($value);

            // User Defined Setting
            if (preg_match('/^setting\d+/', $key)) {
                if (preg_match('/Name/', $key)) {
                    # 1st
                    $thisSettingName = $value;
                    if (preg_match('/\d+/', $key, $matches)) {
                        $thisSettingID = $matches[0];
                    }
                } elseif (preg_match('/_type/', $key)) {
                    # 2nd
                    $thisSettingType = $value;
                } elseif (preg_match('/_value/', $key)) {
                    # 3rd / Final
                    $thisSettingValue = $value;
                    $mySql .= "(" . $thisSettingID . ", " . $this->cartID . ", " . $this->id . ", '" . $thisSettingName . "', '" . $thisSettingType . "', '" . $thisSettingValue . "'),";
                }
            }
        }

        // Remove last comma
        $mySql     = trim($mySql);
        $mySql     = substr_replace($mySql, "", -1);
        $mySql .= " ON DUPLICATE KEY UPDATE `cartID` = VALUES (`cartID`),
											`categoryID` = VALUES (`categoryID`),
											`name` = VALUES (`name`),
											`type` = VALUES (`type`),
											`value` = VALUES (`value`)";
        $this->sql = $mySql;
        $returnID  = $this->DB->query($this->sql);
        if (isset($returnID)) {
            return $returnID;
        } else {
            trigger_error("Error #148: ", E_USER_ERROR);
            return false;
        }
    }

    public function getCategorySettings($name = false)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        /*
         * RETURNS: array of all settings of this id
         * OPTIONAL: string name of setting to return that value if exists
         */

        // Load cart ID and status
        self::getCategoryCartID();
        self::getCategoryStatus();

        // IF Named setting requested (and data previously loaded): RETURNS: string(*) of setting value of passed setting name
        if (!empty($name)) {
            echo '<h2>Name: ' . $name . '</h2>';
            foreach ($this->data as $setting) {
                if (isset($setting['name']) && $setting['name'] == $name) {
                    return $setting['value'];
                }
            }
            // Not found in data
            trigger_error("Error #182: Unable to retrieve '" . $name . "', make sure all settings have been loaded first", E_USER_ERROR);
            return false;
        } #endif:$name
        // Save into variables as all data is overwritten by select results
        $thisID      = $this->id;
        $thisCartID = $this->cartID;
        $thisStatus  = $this->active;

        // Get all settings
        /* Future Use
          $this->sql = "
          SELECT `settingID`, `name`, `type`, `value`
          FROM `" . DBNAME . "`.`productCategories_settings`
          WHERE `categoryID` = '" . $this->id . "'";
          $return_arr = $this->DB->query($this->sql);
          if (!empty($return_arr)) {
          $this->data = $return_arr;
          $this->id = $thisID;
          $this->cartID = $thisCartID;
          $this->active = $thisStatus;
          return true;
          } else {
          trigger_error("Error #131: ", E_USER_ERROR);
          return false;
          }
         */
    }

    public function getCategoryCartID()
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        /*
         * RETURNS: int of cart id of this category id
         */
        $this->sql  = "		
			SELECT `cartID`
			FROM `" . DBNAME . "`.`refCartsProductCategories`
			WHERE `categoryID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            $this->cartID = $return_arr[0]['cartID'];
            return true;
        } else {
            trigger_error("Error #96: ", E_USER_ERROR);
            return false;
        }
    }

    public function getCategoryStatus()
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        /*
         * RETURNS: int of category status of this category id
         */
        $this->sql  = "
			SELECT `active`
			FROM `" . DBNAME . "`.`productCategories`
			WHERE `categoryID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (isset($return_arr)) {
            $this->active = $return_arr[0]['active'];
            return true;
        } else {
            trigger_error("Error #225: ", E_USER_ERROR);
            return false;
        }
    }

    public function get_categoryName()
    {
        \Errors::debugLogger(__METHOD__, 5);
        /*
         * RETURNS: string of category name of this category id
         */
        $this->sql  = "
			SELECT `name`
			FROM `" . DBNAME . "`.`productCategories`
			WHERE `categoryID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (isset($return_arr)) {
            return $this->name = $return_arr[0]['name'];
        } else {
            trigger_error("Error #292: ", E_USER_ERROR);
            return false;
        }
    }

    /*
     * add_new_category
     */
    public function aadd_new_category()
    {
        // Validate & Sanitize _POST
        $args = array(
            'category_cartID'     => FILTER_SANITIZE_NUMBER_INT,
            'categoryName'         => FILTER_SANITIZE_SPECIAL_CHARS,
            'category_code'        => FILTER_SANITIZE_SPECIAL_CHARS,
            'category_description' => FILTER_UNSAFE_RAW,
            'category_parentID'    => FILTER_SANITIZE_NUMBER_INT,
            'level_number'         => FILTER_SANITIZE_NUMBER_INT
        );
        $san  = filter_input_array(INPUT_POST, $args);
        if (array_search(false, $san, true) !== false) {
            $invalid = array_map(function($a) {
                        return $a;
                    }, $san);
            \Errors::debugLogger($invalid);
            trigger_error("Invalid entries.", E_USER_ERROR);
            return false;
        }

        // Create Product Category
        if (empty($san['category_code'])) {
            $san['category_code'] = strtoupper(substr($san['categoryName'], 0, 4));
        }
        $this->sql = "
			INSERT INTO `productCategories`
			(`category_code`, `active`, `name`, `description`)
			VALUES
			(:category_code, :active, :name, :description)";
        $sql_data  = array(':category_code' => $san['category_code'],
            ':active'        => 1,
            ':name'          => $san['categoryName'],
            ':description'   => $san['category_description']);
        $returnID  = $this->DB->query($this->sql, $sql_data);
        if ($returnID) {
            $this->id = $returnID;
        } else {
            trigger_error("Error #PC002: Invalid results.", E_USER_ERROR);
            return false;
        }
        // Create Category<->Cart Reference
        if (empty($san['category_parentID'])) {
            $san['category_parentID'] = 'NULL';
        }
        if (empty($san['level_number'])) {
            $san['level_number'] = 'NULL';
        }
        $this->sql = "
			INSERT INTO `refCartsProductCategories`
			(`cartID`, `categoryID`, `parent_categoryID`, `level_number`)
			VALUES
			(:cartID, :categoryID, :parent_categoryID, :level_number)";
        $sql_data  = array(':cartID'           => $san['category_cartID'],
            ':categoryID'        => $this->id,
            ':parent_categoryID' => $san['category_parentID'],
            ':level_number'      => $san['level_number']);
        $this->DB->query($this->sql, $sql_data); // No return value, database class will catch exception
    }

}
?>