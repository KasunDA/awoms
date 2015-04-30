<?php
namespace killerCart;

/**
 * Product class
 *
 * Product management methods
 *
 * PHP version 5
 *
 * @category  killerCart
 * @package   killerCart
 * @author    Brock Hensley <brock@brockhensley.com>
 * @version   v0.0.1
 */
class Product extends Cart
{
    /**
     * __construct
     */
    public function __construct()
    {
        $this->DB = new \Database();
    }

    /**
     * __destruct
     */
    public function __destruct()
    {
        unset($this->DB, $this->sql, $this->data['session']);
    }

    /**
     * saveProduct
     *
     * @version v0.0.1
     * 
     * Sanitizes user input from product edit form, saves product in database
     *
     * @return boolean
     */
    public function saveProduct($productID, $categoryID, $parentProductID, $cartID, $productActive, $productName, $price, $descPub,
                                $descPriv, $taxable, $private, $email, $sku, $productSpecifications, $productShipping, $productWarranty)
    {
        if (empty($productID)) {
            $productID = 'DEFAULT';
        }
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
			INSERT INTO products
				(productID, categoryID, parentProductID, productActive, productName, price, productDescriptionPublic,
                productDescriptionPrivate, productTaxable, productPrivate, productEmail, productSKU, productSpecifications, productShipping, productWarranty)
			VALUES
				(:productID, :categoryID, :parentProductID, :productActive, :productName, :price, :productDescriptionPublic,
                :productDescriptionPrivate, :productTaxable, :productPrivate, :productEmail, :productSKU, :productSpecifications, :productShipping, :productWarranty)
			ON DUPLICATE KEY UPDATE productID = :productID,
									categoryID = :categoryID,
									parentProductID = :parentProductID,
									productActive = :productActive,
									productName = :productName,
									price = :price,
									productDescriptionPublic = :productDescriptionPublic,
                                    productDescriptionPrivate = :productDescriptionPrivate,
                                    productTaxable = :productTaxable,
                                    productPrivate = :productPrivate,
                                    productEmail = :productEmail,
                                    productSKU = :productSKU,
                                    productSpecifications = :productSpecifications,
                                    productShipping = :productShipping,
                                    productWarranty = :productWarranty";
        $sql_data  = array(':productID'                 => $productID,
            ':categoryID'                => $categoryID,
            ':parentProductID'           => $parentProductID,
            ':productActive'             => $productActive,
            ':productName'               => $productName,
            ':price'                     => $price,
            ':productDescriptionPublic'  => $descPub,
            ':productDescriptionPrivate' => $descPriv,
            ':productTaxable'            => $taxable,
            ':productPrivate'            => $private,
            ':productEmail'              => $email,
            ':productSKU'                => $sku,
            ':productSpecifications'     => $productSpecifications,
            ':productShipping'           => $productShipping,
            ':productWarranty'           => $productWarranty);
        $returnID  = $this->DB->query($this->sql, $sql_data);
        if (isset($returnID) && $returnID > 0) {
            $productID        = $returnID;
            $this->id         = $returnID;
            $this->categoryID = $categoryID;
            $this->cartID    = $cartID;
        } elseif (isset($returnID) && $returnID == 0) {
            
        } else {
            trigger_error("Error #P002: Invalid results.", E_USER_ERROR);
            return false;
        }

        // Image Upload
        $frmFilesID = "productImages";
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
                            trigger_error("Error #P015: Invalid image.", E_USER_ERROR);
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
                            trigger_error("Error #P016: Invalid image.", E_USER_ERROR);
                            return false;
                        }
                        $iu_count++;
                    }
                }
            } #end:foreach
        } #end:if $_FILES
        // Image Upload
        if (!empty($iu_count)) {
            for ($i = 0; $i < $iu_count; $i++) {
                $frmFilesID = "productImages";
                if (!Product::handleImageUpload($frmFilesID, $i, $cartID, $categoryID, $productID)) {
                    echo "<br /><strong>NOTE:</strong> Failed to upload file $i...";
                }
            }
        }
        return true;
    }

    /**
     * handleImageUpload
     *
     * @param string $frmFilesID HTML form files ID to handle
     * @param int $thisFile File to process in array e.g. 0, 1
     * @return boolean
     */
    private function handleImageUpload($frmFilesID = false, $thisFile = false, $cartID, $categoryID, $productID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $fileName = $_FILES["$frmFilesID"]['name'][$thisFile];
        $tmpName  = $_FILES["$frmFilesID"]['tmp_name'][$thisFile];
        if (!is_uploaded_file($tmpName)) {
            trigger_error("Error #P000 (handleImageUpload): Security block.", E_USER_ERROR);
            return false;
        }
        $fileSize  = $_FILES["$frmFilesID"]['size'][$thisFile];
        $fileType  = $_FILES["$frmFilesID"]['type'][$thisFile];
        // Save into Database
        $this->sql = "
			INSERT INTO productImages
				(productID, name, size, type, imageActive)
			VALUES
				(:productID, :name, :size, :type, :active)";
        $sql_data  = array(':productID' => $productID,
            ':name'      => $fileName,
            ':size'      => $fileSize,
            ':type'      => $fileType,
            ':active'    => '1');
        $returnID  = $this->DB->query($this->sql, $sql_data);
        if (empty($returnID)) {
            trigger_error("Error #P000 (admin_handle_upload): Invalid results.", E_USER_NOTICE);
            return false;
        }
        // Make nested folder path
        $destPath = cartImagesDir . $cartID . '/' . $categoryID . '/' . $productID;
        Util::createNestedDirectory($destPath);

        // Test if file already exists (auto-rename?)
        if (file_exists($destPath . '/' . $fileName)) {
            trigger_error("Error #P000 (admin_handle_upload): File already exists, rename the file and try again.", E_USER_NOTICE);
            return false;
        }
        // Move uploaded file from tmp
        if (move_uploaded_file($tmpName, $destPath . '/' . $fileName)) {
            return true;
        } else {
            trigger_error("Error #P000 (admin_handle_upload): Invalid results.", E_USER_NOTICE);
            // [] Remove database entries
            return false;
        }
    }

    /**
     * getProductCount
     *
     * Gets count of products matching selected type
     *
     * @version v0.0.1
     * 
     * @param string $type Type of product: All, Active, Inactive
     * @param int $cartID Cart ID (null for all)
     * @param int $categoryID Category ID (null for all)
     * 
     * @return int Count of products
     */
    public function getProductCount($type, $cartID = NULL, $categoryID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // All
        if (strtolower($type) == "all") {
            $whereSql = "productID IS NOT NULL";
            // Active
        } elseif (strtolower($type) == "active") {
            $whereSql = "productActive='1'";
            // Not active
        } elseif (strtolower($type) == "inactive") {
            $whereSql = "productActive='0'";
        }
        // Cart
        if (!empty($cartID)) {
            $whereSql .= " AND ref.cartID = " . $cartID;
        }
        // Category
        if (!empty($categoryID)) {
            $whereSql .= " AND pc.categoryID = " . $categoryID;
        }
        $this->sql  = "
			SELECT COUNT(p.productID) as Total
			FROM products AS p
                INNER JOIN productCategories AS pc
                    ON p.categoryID = pc.categoryID
                INNER JOIN refCartsProductCategories as ref
                    ON pc.categoryID = ref.categoryID
			WHERE " . $whereSql;
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr[0]['Total'];
        } else {
            return false;
        }
    }

    /**
     * getProductIDs
     *
     * @version v0.0.1
     * 
     * @param string $active ALL/ACTIVE/INACTIVE
     * @param int $cartID Cart ID (null for all)
     * @param int $categoryID Optional category ID
     * @param string $orderBy Optional order by clause
     * @param int $catActive Optional category active filter
     * @param string $customWhere Optional custom where filter
     * 
     * @return boolean|array
     */
    public function getProductIDs($active, $cartID = NULL, $categoryID = NULL, $orderBy = NULL, $catActive = NULL, $customWhere = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        if (strtolower($active) == "all") {
            $whereSql = "p.productID IS NOT NULL";
        } elseif (strtolower($active) == "active") {
            $whereSql = "p.productActive='1'";
        } elseif (strtolower($active) == "inactive") {
            $whereSql = "p.productActive='0'";
        }
        // Specified cart ID products only
        if (!empty($cartID)) {
            $whereSql .= " AND ref.cartID = " . $cartID;
        }
        // Category ID?
        if (!empty($categoryID)) {
            $whereSql .= " AND ref.categoryID = " . $categoryID;
        }
        // Cat Active?
        if ($catActive !== NULL) {
            $whereSql .= " AND pc.categoryActive = " . $catActive;
        }
        // Custom Where
        if (!empty($customWhere)) {
            $whereSql .= " AND " . $customWhere;
        }
        // Order by?
        if (!empty($orderBy)) {
            $whereSql .= " ORDER BY " . $orderBy;
        }
        $this->sql  = "
            SELECT DISTINCT
                p.productID, p.categoryID, p.parentProductID, p.productActive, p.productName, p.price,
                p.productDescriptionPublic, p.productDescriptionPrivate,
                p.productTaxable, p.productPrivate, p.productEmail, p.productSKU,
                p.productSpecifications, p.productShipping, p.productWarranty,
                pc.categoryName,
                ref.cartID
            FROM products AS p
                INNER JOIN refCartsProductCategories AS ref
                    ON p.categoryID = ref.categoryID
                INNER JOIN productCategories AS pc
                    ON ref.categoryID = pc.categoryID
			WHERE " . $whereSql;
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $return_arr;
        } else {
            return false;
        }
    }

    /**
     * getProductInfo
     *
     * Returns product information
     * 
     * @version v0.0.1
     * 
     * @param int $productID Product ID
     * 
     * @return boolean
     */
    public function getProductInfo($productID)
    {
        \Errors::debugLogger(__METHOD__, 10);
        \Errors::debugLogger(func_get_args(), 10);
        $this->sql     = "
			SELECT p.productID, p.categoryID, p.parentProductID, p.productActive, p.productName, p.price,
                p.productDescriptionPublic, p.productDescriptionPrivate,
                p.productTaxable, p.productPrivate, p.productEmail, p.productSKU,
                p.productSpecifications, p.productShipping, p.productWarranty,
                ref.cartID
			FROM products AS p
                INNER JOIN refCartsProductCategories AS ref
                    ON p.categoryID = ref.categoryID
			WHERE p.productID = :productID
            LIMIT 1";
        $this->sqlData = array(':productID' => $productID);
        $r             = $this->DB->query($this->sql, $this->sqlData);
        return $r[0];
    }

    /**
     * getProductImages
     *
     * Gets products images (active only by default)
     * 
     * @version v0.0.1
     * 
     * @param int $productID Product ID
     * @param int $imageID Optional image ID to narrow results to
     * @param int $inactive Optional flag to narrow results to Inactive images compared to default of Active
     * 
     * @return array Image details
     * 
     * @deprecated since version v0.0.1
     */
    public function getProductImages($productID, $imageID = NULL, $inactive = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Product ID
        $this->sql     = "
			SELECT imageID, name, size, type
			FROM productImages
			WHERE productID = :productID";
        $this->sqlData = array(':productID' => $productID);
        // Active
        if ($inactive === NULL) {
            $inactive = '1';
        } else {
            $inactive = '0';
        }
        $this->sql .= " AND imageActive = :imageActive";
        $this->sqlData[':imageActive'] = $inactive;
        // Image ID
        if (!empty($imageID)) {
            $this->sql .= " AND imageID = :imageID";
            $this->sqlData[':imageID'] = $imageID;
            $r                         = $this->DB->query($this->sql, $this->sqlData);
            return $r[0];
        }
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * removeProductImage
     * 
     * Removes product image from database and server (actually sets to Inactive in DB)
     * 
     * @since v0.0.1
     * 
     * @param int $cartID
     * @param int $categoryID
     * @param int $productID
     * @param int $imageID
     * 
     * @return boolean
     */
    public function removeProductImage($cartID, $categoryID, $productID, $imageID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // Get image info
        $img = self::getProductImages($productID, $imageID);

        // Remove from filesystem first
        if (!unlink(cartPrivateDir . 'images/' . $cartID . '/' . $categoryID . '/' . $productID . '/' . $img['name'])) {
            trigger_error(__METHOD__ . __LINE__ . 'Unexpected Results.');
            return false;
        }

        // Remove from database once confirmed removed from system
        $this->sql     = "
            UPDATE productImages
            SET imageActive = 0
            WHERE imageID = :imageID
            LIMIT 1";
        $this->sqlData = array(':imageID' => $imageID);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * getProductTaxable
     * 
     * Gets taxable status of product
     * 
     * @uses Product_Category\getCategoryTaxable() If product inherits from category
     * 
     * @param int $productID Product ID
     * 
     * @return boolean|int 1=taxable, 0=nontaxable
     */
    public function getProductTaxable($productID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT productTaxable, categoryID
            FROM products
            WHERE productID = :productID";
        $this->sqlData = array(':productID' => $productID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        $taxable = $res[0]['productTaxable'];
        // Product inherits from category
        if ($taxable == '2') {
            $pc      = new Product_Category();
            $taxable = $pc->getCategoryTaxable($res[0]['categoryID']);
        }
        return $taxable;
    }

    /**
     * getProductPrivate
     * 
     * Gets privacy state of product or category if inherits
     * 
     * @since v0.0.1
     * 
     * @param type $productID
     * 
     * @return boolean|int 1=private, 0=public
     */
    public function getProductPrivate($productID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT productPrivate, categoryID
            FROM products
            WHERE productID = :productID";
        $this->sqlData = array(':productID' => $productID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (!isset($res)) {
            \Errors::debugLogger(__METHOD__, 5);
            trigger_error('Unexpected results', E_USER_ERROR);
            return false;
        }
        $private = $res[0]['productPrivate'];
        // Product inherits from category
        if ($private == '2') {
            $pc      = new Product_Category();
            $private = $pc->getCategoryPrivacy($res[0]['categoryID']);
        }
        return $private;
    }

    /**
     * getProductOptions
     * 
     * Gets product options information at cart level
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $cartID Cart ID
     * @param int $optionID Optional option ID to narrow results
     * @param int $inactive Optional flag to narrow results to inactive
     * 
     * @return boolean|array Product Options
     */
    public function getProductOptions($cartID, $optionID = NULL, $inactive = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            SELECT
                po.optionID, po.cartID, po.optionName, po.optionType, po.optionActive, po.optionBehavior, po.optionRequired
            FROM
                productOptions AS po
            WHERE
                po.cartID = :cartID
            AND
                po.optionActive = :active";
        // Inactive?
        if ($inactive === NULL) {
            $inactive = 1;
        } else {
            $inactive = 0;
        }
        $this->sqlData = array(':cartID' => $cartID,
            ':active'    => $inactive);
        // Option ID
        if (!empty($optionID)) {
            $this->sql .= ' AND po.optionID = :optionID';
            $this->sqlData[':optionID'] = $optionID;
        }
        // Order
        $this->sql .= ' ORDER BY po.optionName ASC';
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($optionID)
                && (!empty($res[0]))) {
            return $res[0];
        }
        return $res;
    }
    
    /**
     * getProductOptionsCustom
     * 
     * Gets product options information at product level
     * Also gets global option information the custom creation was inherited from
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $cartID Cart ID
     * @param int $productID Product ID
     * @param int $optionIDCustom Optional custom option ID to narrow results
     * @param int $inactive Optional flag to narrow results to inactive
     * 
     * @return boolean|array Product Options
     */
    public function getProductOptionsCustom($cartID, $productID, $optionIDCustom = NULL, $inactive = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            SELECT
                po.optionID, po.cartID, po.optionName, po.optionType, po.optionActive, po.optionBehavior, po.optionRequired,
                poc.optionIDCustom, poc.productID, poc.optionIDGlobal, poc.optionNameCustom, poc.optionActiveCustom, poc.optionBehaviorCustom,
                poc.optionRequiredCustom, poc.optionSortOrder, poc.inheritsGlobalOption
            FROM
                productOptions AS po
                    INNER JOIN productOptionsCustom AS poc
                        ON po.optionID = poc.optionIDGlobal
            WHERE
                po.cartID = :cartID
            AND
                po.optionActive = :active";
        // Inactive?
        if ($inactive === NULL) {
            $inactive = 1;
        } else {
            $inactive = 0;
        }
        $this->sqlData = array(':cartID' => $cartID,
            ':active'    => $inactive);
        // Product ID?
        if ($productID !== NULL) {
            $this->sql .= ' AND poc.productID = :productID AND poc.optionActiveCustom = :optionActiveCustom';
            $this->sqlData[':productID'] = $productID;
            $this->sqlData[':optionActiveCustom'] = 1;
        }
        // Custom Option ID
        if (!empty($optionIDCustom)) {
            $this->sql .= ' AND poc.optionIDCustom = :optionIDCustom';
            $this->sqlData[':optionIDCustom'] = $optionIDCustom;
        }
        // Order
        $this->sql .= " ORDER BY poc.optionSortOrder ASC";
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($optionIDCustom)
                && (!empty($res[0]))) {
            return $res[0];
        }
        return $res;
    }

    /**
     * getProductOptionsChoices
     * 
     * Gets product options choices info for option ID and option detail ID
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $optionID
     * @param int $choiceID Optional choice ID to trim results to
     * @param int $inactive Optional flag to narrow results to inactive
     * @param int $choiceImageID Optional
     * 
     * @return boolean|array Product Option Details
     */
    public function getProductOptionsChoices($optionID, $choiceID = NULL, $inactive = NULL, $choiceImageID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            SELECT choiceID, optionID, choiceValue, choicePrice, choiceActive, choiceImageID, choiceSortOrder
            FROM productOptionsChoices
            WHERE optionID = :optionID
            AND choiceActive = :choiceActive";
        // Defaults to Active only
        if ($inactive === NULL) {
            $inactive = 1;
        } else {
            $inactive = 0;
        }
        $this->sqlData = array(':optionID'     => $optionID, ':choiceActive' => $inactive);
        // Choice ID?
        if (!empty($choiceID)) {
            $this->sql .= " AND choiceID = :choiceID";
            $this->sqlData[':choiceID'] = $choiceID;
        }
        // Image ID?
        if (!empty($choiceImageID)) {
            $this->sql .= " AND choiceImageID = :choiceImageID";
            $this->sqlData[':choiceImageID'] = $choiceImageID;
        }
        // Sort
        $this->sql .= " ORDER BY choiceSortOrder ASC";
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($choiceID)) {
            return $res[0];
        }
        return $res;
    }
    
    /**
     * getProductOptionChoiceCustomMaxSortOrder
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param type $optionIDCustom
     * 
     * @return int
     */
    public function getProductOptionChoiceCustomMaxSortOrder($optionIDCustom) {
        $this->sql = "
            SELECT MAX(choiceSortOrderCustom) AS choiceSortOrderCustomMax
            FROM productOptionsChoicesCustom
            WHERE optionIDCustom = :optionIDCustom
            AND choiceActiveCustom = 1";
        $this->sqlData = array(':optionIDCustom' => $optionIDCustom);
        $max = $this->DB->query($this->sql, $this->sqlData);
        return $max[0]['choiceSortOrderCustomMax'];
    }
    
    /**
     * getProductOptionsChoicesCustom
     * 
     * Gets product options choices
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $optionIDCustom Custom option ID
     * @param int $choiceIDCustom Optional custom choice ID to trim results to
     * @param int $inactive Optional flag to narrow results to inactive
     * @param int $choiceImageID Optional
     * 
     * @return boolean|array Product Option Details
     */
    public function getProductOptionsChoicesCustom($optionIDCustom, $choiceIDCustom = NULL, $inactive = NULL, $choiceImageID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            SELECT choiceIDCustom, optionIDCustom, choiceIDGlobal, choiceValueCustom, choicePriceCustom, choiceActiveCustom,
                choiceImageIDCustom, choiceSortOrderCustom
            FROM productOptionsChoicesCustom
            WHERE optionIDCustom = :optionIDCustom
            AND choiceActiveCustom = :choiceActiveCustom";
        // Defaults to Active only
        if ($inactive === NULL) {
            $inactive = 1;
        } else {
            $inactive = 0;
        }
        $this->sqlData = array(':optionIDCustom'     => $optionIDCustom,
            ':choiceActiveCustom' => $inactive);
        // Choice ID?
        if (!empty($choiceIDCustom)) {
            $this->sql .= " AND choiceIDCustom = :choiceIDCustom";
            $this->sqlData[':choiceIDCustom'] = $choiceIDCustom;
        }
        // Image ID?
        if (!empty($choiceImageID)) {
            $this->sql .= " AND choiceImageIDCustom = :choiceImageID";
            $this->sqlData[':choiceImageID'] = $choiceImageID;
        }
        // Sort
        $this->sql .= " ORDER BY choiceSortOrderCustom ASC";
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($choiceIDCustom)) {
            return $res[0];
        }
        return $res;
    }

    /**
     * saveProductOptionCart
     * 
     * Creates or updates global product option template for cart
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int|string $optionID DEFAULT for new or ID for updating existing
     * @param int $cartID
     * @param string $name
     * @param string $type
     * @param string $optionBehavior
     * @param int $optionRequired
     * @param int $active
     * 
     * @return int|boolean
     */
    public function saveProductOptionCart($optionID, $cartID, $name, $type, $optionBehavior, $optionRequired, $active = NULL) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            INSERT INTO productOptions
                (optionID, cartID, optionName, optionType, optionActive, optionBehavior, optionRequired)
            VALUES
                (:optionID, :cartID, :optionName, :optionType, :active, :optionBehavior, :optionRequired)
            ON DUPLICATE KEY UPDATE
                optionID = :optionID, cartID = :cartID, optionName = :optionName, optionType = :optionType, optionActive = :active,
                optionBehavior = :optionBehavior, optionRequired = :optionRequired";
        // Active by default
        if ($active === NULL) {
            $active = 1;
        } else {
            $active = 0;
        }
        $this->sqlData = array(':optionID'  => $optionID,
            ':cartID'   => $cartID,
            ':optionName'      => $name,
            ':optionType'      => $type,
            ':active'    => $active,
            ':optionBehavior' => $optionBehavior,
            ':optionRequired' => $optionRequired);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * getProductOptionCustomMaxSortOrder
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param type $productID
     * 
     * @return int
     */
    public function getProductOptionCustomMaxSortOrder($productID) {
        $this->sql = "
            SELECT MAX(optionSortOrder) AS optionSortOrderMax
            FROM productOptionsCustom
            WHERE productID = :productID
            AND optionActiveCustom = 1";
        $this->sqlData = array(':productID' => $productID);
        $max = $this->DB->query($this->sql, $this->sqlData);
        return $max[0]['optionSortOrderMax'];
    }
    
    /**
     * saveProductOptionCustom
     * 
     * Creates or updates product option customized for a specific product
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param string|int $optionIDCustom DEFAULT for new or ID for existing
     * @param int $optionIDGlobal Global template option ID
     * @param int $productID
     * @param string $name
     * @param string $behavior
     * @param int $required
     * @param int $sortOrder
     * @param int $inherits
     * @param int $active
     * 
     * @return int|boolean
     */
    public function saveProductOptionCustom($optionIDCustom, $optionIDGlobal, $productID, $name, $behavior, $required, $sortOrder, $inherits = NULL, $active = NULL) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        // New Option defaults to last/max sort order
        if ($optionIDCustom == 'DEFAULT') {
            $max = self::getProductOptionCustomMaxSortOrder($productID);
            $sortOrder = (int)$max + 1;
        }
        // Query
        $this->sql = "
            INSERT INTO productOptionsCustom
                (optionIDCustom, productID, optionIDGlobal, optionNameCustom, optionActiveCustom, optionBehaviorCustom, optionRequiredCustom, optionSortOrder, inheritsGlobalOption)
            VALUES
                (:optionIDCustom, :productID, :optionIDGlobal, :optionNameCustom, :optionActiveCustom, :optionBehaviorCustom, :optionRequiredCustom, :optionSortOrder, :inheritsGlobalOption)
            ON DUPLICATE KEY UPDATE
                optionIDCustom = :optionIDCustom, productID = :productID, optionIDGlobal = :optionIDGlobal, optionNameCustom = :optionNameCustom,
                optionActiveCustom = :optionActiveCustom, optionBehaviorCustom = :optionBehaviorCustom, optionRequiredCustom = :optionRequiredCustom,
                optionSortOrder = :optionSortOrder, inheritsGlobalOption = :inheritsGlobalOption";
        // Inherits default: (no)
        if ($inherits === NULL) {
            $inherits = 0;
        } else {
            $inherits = 1;
        }        
        // Active by default
        if ($active === NULL) {
            $active = 1;
        } else {
            $active = 0;
        }
        $this->sqlData = array(':optionIDCustom' => $optionIDCustom,
            ':productID' => $productID,
            ':optionIDGlobal' => $optionIDGlobal,
            ':optionNameCustom' => $name,
            ':optionActiveCustom' => $active,
            ':optionBehaviorCustom' => $behavior,
            ':optionRequiredCustom' => $required,
            ':optionSortOrder' => $sortOrder,
            ':inheritsGlobalOption' => $inherits);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * saveProductOptionChoicesCart
     * 
     * Creates or updates global product option choices template for cart/option
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $choiceID
     * @param int $optionID
     * @param string $value
     * @param float $price
     * @param int $active Optional flag to narrow results to inactive
     * @param int $imageID Optional image ID to associate detail with
     * @param int $sortOrder Optional
     * 
     * @return boolean|int 0 if update existing otherwise int is new ID
     */
    public function saveProductOptionChoicesCart($choiceID, $optionID, $value, $price, $active = NULL, $imageID = NULL, $sortOrder = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            INSERT INTO productOptionsChoices
                (choiceID, optionID, choiceValue, choicePrice, choiceActive, choiceImageID, choiceSortOrder)
            VALUES
                (:choiceID, :optionID, :choiceValue, :choicePrice, :choiceActive, :choiceImageID, :choiceSortOrder)
            ON DUPLICATE KEY UPDATE
                choiceID = :choiceID, optionID = :optionID, choiceValue = :choiceValue, choicePrice = :choicePrice,
                choiceActive = :choiceActive, choiceImageID = :choiceImageID, choiceSortOrder = :choiceSortOrder";
        // Default to active
        if ($active === NULL) {
            $active = 1;
        } else {
            $active = 0;
        }
        $this->sqlData = array(':choiceID' => $choiceID,
            ':optionID'                => $optionID,
            ':choiceValue'                   => $value,
            ':choicePrice'                   => $price,
            ':choiceActive'                  => $active,
            ':choiceImageID'           => $imageID,
            ':choiceSortOrder'         => $sortOrder);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * saveProductOptionChoicesCustom
     * 
     * Creates or updates product option choices customized for a specific product
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param type $productOptionsChoicesCustomID DEFAULT for new
     * @param type $productOptionsCustomID
     * @param type $productOptionsChoicesID
     * @param type $choiceValueCustom
     * @param type $choicePriceCustom
     * @param type $choiceImageIDCustom
     * @param type $choiceSortOrder
     * @param type $choiceActiveCustom Optional
     * 
     * @return type
     */
    public function saveProductOptionChoicesCustom($choiceIDCustom, $optionIDCustom, $choiceIDGlobal,
            $choiceValueCustom, $choicePriceCustom, $choiceImageIDCustom, $choiceSortOrder, $choiceActiveCustom = NULL)
    {
        // New Choice defaults to last/max sort order
        if ($choiceIDCustom == 'DEFAULT') {
            $max = self::getProductOptionChoiceCustomMaxSortOrder($optionIDCustom);
            $choiceSortOrder = (int)$max + 1;
        }
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql = "
            INSERT INTO productOptionsChoicesCustom
                (choiceIDCustom, optionIDCustom, choiceIDGlobal, choiceValueCustom, choicePriceCustom,
                choiceActiveCustom, choiceImageIDCustom, choiceSortOrderCustom)
            VALUES
                (:choiceIDCustom, :optionIDCustom, :choiceIDGlobal, :choiceValueCustom, :choicePriceCustom,
                :choiceActiveCustom, :choiceImageIDCustom, :choiceSortOrderCustom)
            ON DUPLICATE KEY UPDATE
                choiceIDCustom = :choiceIDCustom, optionIDCustom = :optionIDCustom,
                choiceIDGlobal = :choiceIDGlobal, choiceValueCustom = :choiceValueCustom, choicePriceCustom = :choicePriceCustom,
                choiceActiveCustom = :choiceActiveCustom, choiceImageIDCustom = :choiceImageIDCustom, choiceSortOrderCustom = :choiceSortOrderCustom";
        // Default to active
        if ($choiceActiveCustom === NULL) {
            $active = 1;
        } else {
            $active = 0;
        }
        $this->sqlData = array(':choiceIDCustom' => $choiceIDCustom,
            ':optionIDCustom' => $optionIDCustom,
            ':choiceIDGlobal' => $choiceIDGlobal,
            ':choiceValueCustom' => $choiceValueCustom,
            ':choicePriceCustom' => $choicePriceCustom,
            ':choiceActiveCustom' => $active,
            ':choiceImageIDCustom' => $choiceImageIDCustom,
            ':choiceSortOrderCustom' => $choiceSortOrder);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * removeProductOption
     * 
     * Removes product option (actually sets to Inactive in DB)
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @uses removeProductOptionChoice
     * 
     * @param int $productID
     * @param int $optionID
     * 
     * @return boolean
     */
    public function removeProductOption($productID, $optionID)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 5);
        self::removeProductOptionChoice($optionID, NULL);
        // Remove option
        $this->sql     = "
            UPDATE productOptions
            SET optionActive = 0
            WHERE productID = :productID
            AND optionID = :optionID
            LIMIT 1";
        $this->sqlData = array(':optionID'  => $optionID,
            ':productID' => $productID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        return $res;
    }

    /**
     * removeProductOptionChoice
     * 
     * Removes product option choice (actually sets to Inactive in DB)
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $optionID Option ID to remove
     * @param int $choiceID Optional Choice ID to restrict delete to
     * 
     * @return boolean
     */
    public function removeProductOptionChoice($optionID, $choiceID = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 5);
        $this->sql     = "
            UPDATE productOptionsChoices
            SET choiceActive = 0
            WHERE optionID = :optionID";
        $this->sqlData = array(':optionID' => $optionID);
        if (!empty($choiceID)) {
            $this->sql .= " AND productOptionsChoicesID = :productOptionsChoicesID";
            $this->sqlData[':productOptionsChoicesID'] = $choiceID;
        }
        $res = $this->DB->query($this->sql, $this->sqlData);
        return $res;
    }
    
    /**
     * removeProductOptionCustom
     * 
     * Removes custom product option (actually sets to Inactive in DB)
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @uses removeProductOptionChoiceCustom
     * 
     * @param int $productID
     * @param int $optionIDCustom
     * 
     * @return boolean
     */
    public function removeProductOptionCustom($productID, $optionIDCustom)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 5);
        // Remove custom choices
        self::removeProductOptionChoiceCustom($optionIDCustom);
        // Remove custom option
        $this->sql     = "
            UPDATE productOptionsCustom
            SET optionActiveCustom = 0
            WHERE productID = :productID
            AND optionIDCustom = :optionIDCustom
            LIMIT 1";
        $this->sqlData = array(':optionIDCustom'  => $optionIDCustom,
            ':productID' => $productID);
        $res           = $this->DB->query($this->sql, $this->sqlData);
        if (isset($res)) {
            return true;
        }
        return $res;
    }
    
    /**
     * removeProductOptionChoiceCustom
     * 
     * Removes custom product option choice (actually sets to Inactive in DB)
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $optionIDCustom Custom Option ID to remove
     * @param int $choiceIDCustom Optional custom choice ID to restrict delete to
     * 
     * @return boolean
     */
    public function removeProductOptionChoiceCustom($optionIDCustom, $choiceIDCustom = NULL)
    {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 5);
        $this->sql     = "
            UPDATE productOptionsChoicesCustom
            SET choiceActiveCustom = 0
            WHERE optionIDCustom = :optionIDCustom";
        $this->sqlData = array(':optionIDCustom' => $optionIDCustom);
        if (!empty($choiceIDCustom)) {
            $this->sql .= " AND choiceIDCustom = :choiceIDCustom";
            $this->sqlData[':choiceIDCustom'] = $choiceIDCustom;
        }
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (isset($res)) {
            return true;
        }
        return $res;
    }
    
    /**
     * getOptionChildren
     * 
     * @since v0.0.1
     * 
     * @param int $optionID
     * 
     * @return boolean|array
     */
    public function getOptionChildren($optionID) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT parentOptionID, childOptionID, triggerChoiceID
            FROM refProductOptionsChoices
            WHERE parentOptionID = :optionID";
        $this->sqlData = array(':optionID' => $optionID);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * getOptionChildrenCustom
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $optionIDCustom
     * 
     * @return boolean|array
     */
    public function getOptionChildrenCustom($optionIDCustom) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT ref.parentOptionIDCustom, ref.childOptionIDCustom, ref.triggerChoiceIDCustom
            FROM refProductOptionsChoicesCustom AS ref
                INNER JOIN productOptionsCustom AS poc
                    ON ref.childOptionIDCustom = poc.optionIDCustom
            WHERE ref.parentOptionIDCustom = :optionIDCustom
            ORDER BY poc.optionSortOrder";
        $this->sqlData = array(':optionIDCustom' => $optionIDCustom);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * getOptionParent
     * 
     * Can be used to find parentOptionID or triggerChoiceID of childOptionID
     * 
     * @since v0.0.1
     * 
     * @param int $optionID childOptionID
     * 
     * @return boolean|array
     */
    public function getOptionParent($optionID) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT parentOptionID, childOptionID, triggerChoiceID
            FROM refProductOptionsChoices
            WHERE childOptionID = :optionID";
        $this->sqlData = array(':optionID' => $optionID);
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    }
    
    /**
     * getOptionParentCustom
     * 
     * Can be used to find parentOptionID or triggerChoiceID of childOptionID
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $optionIDCustom childOptionIDCustom
     * 
     * @return boolean|array
     */
    public function getOptionParentCustom($optionIDCustom) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT parentOptionIDCustom, childOptionIDCustom, triggerChoiceIDCustom
            FROM refProductOptionsChoicesCustom
            WHERE childOptionIDCustom = :optionIDCustom";
        $this->sqlData = array(':optionIDCustom' => $optionIDCustom);
        $res = $this->DB->query($this->sql, $this->sqlData);
        if (!empty($res)) {
            return $res;
        } else {
            return false;
        }
    }

    /**
     * isChoiceTrigger
     * 
     * @since v0.0.1
     * 
     * @param int $choiceID
     * 
     * @return boolean|array
     */
    public function isChoiceTrigger($choiceID) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT parentOptionID, childOptionID, triggerChoiceID
            FROM refProductOptionsChoices
            WHERE triggerChoiceID = :choiceID";
        $this->sqlData = array(':choiceID' => $choiceID);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * isChoiceTriggerCustom
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $choiceIDCustom
     * 
     * @return boolean|array
     */
    public function isChoiceTriggerCustom($choiceIDCustom) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            SELECT parentOptionIDCustom, childOptionIDCustom, triggerChoiceIDCustom
            FROM refProductOptionsChoicesCustom
            WHERE triggerChoiceIDCustom = :choiceIDCustom";
        $this->sqlData = array(':choiceIDCustom' => $choiceIDCustom);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * saveProductOptionChoiceTrigger
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $parentOptionID
     * @param int $childOptionID
     * @param int $triggerChoiceID Optional
     * 
     * @return boolean
     */
    public function saveProductOptionChoiceTrigger($parentOptionID, $childOptionID, $triggerChoiceID = NULL) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO refProductOptionsChoices
                (parentOptionID, childOptionID, triggerChoiceID)
            VALUES
                (:parentOptionID, :childOptionID, :triggerChoiceID)";
        $this->sqlData = array(':parentOptionID' => $parentOptionID,
            ':childOptionID' => $childOptionID,
            ':triggerChoiceID' => $triggerChoiceID);
        return $this->DB->query($this->sql, $this->sqlData);
    }

    /**
     * saveProductOptionChoiceTriggerCustom
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param int $parentOptionID
     * @param int $childOptionID
     * @param int $triggerChoiceID Optional
     * 
     * @return boolean
     */
    public function saveProductOptionChoiceTriggerCustom($parentOptionID, $childOptionID, $triggerChoiceID = NULL) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            INSERT INTO refProductOptionsChoicesCustom
                (parentoptionIDCustom, childoptionIDCustom, triggerChoiceIDCustom)
            VALUES
                (:parentOptionID, :childOptionID, :triggerChoiceIDCustom)";
        $this->sqlData = array(':parentOptionID' => $parentOptionID,
            ':childOptionID' => $childOptionID,
            ':triggerChoiceIDCustom' => $triggerChoiceID);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * clearProductOptionChoiceTriggersCustom
     * 
     * Clears parent/child option triggers
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param type $parentOptionID
     * @param type $childOptionID
     * @return type
     */
    public function clearProductOptionChoiceTriggersCustom($parentOptionID, $childOptionID) {
        \Errors::debugLogger(__METHOD__, 5);
        \Errors::debugLogger(func_get_args(), 8);
        $this->sql     = "
            DELETE FROM refProductOptionsChoicesCustom
            WHERE parentoptionIDCustom = :parentoptionIDCustom
            AND childoptionIDCustom = :childoptionIDCustom";
        $this->sqlData = array(':parentoptionIDCustom' => $parentOptionID,
            ':childoptionIDCustom' => $childOptionID);
        return $this->DB->query($this->sql, $this->sqlData);
    }
    
    /**
     * updateProductOptionSortOrderCustom
     * 
     * Changes sort order of product option
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param type $productID
     * @param type $optionIDCustom
     * @param type $oldOrder
     * @param type $newOrder
     * @return boolean
     */
    public function updateProductOptionSortOrderCustom($productID, $optionIDCustom, $oldOrder, $newOrder) {
        // Get option ID with desired sort order to replace (or nearest if deletion gaps)
        if ($oldOrder < $newOrder) {
            $symbol = ">"; // Greater than
            $order = "ASC";
        } else {
            $symbol = "<"; // Less than
            $order = "DESC";
        }
        $this->sql = "
            SELECT optionIDCustom, optionSortOrder
            FROM  productOptionsCustom
            WHERE productID = :productID
            AND optionSortOrder ".$symbol."= :newOrder
            ORDER BY optionSortOrder ".$order.", optionIDCustom DESC
            LIMIT 1";
        $this->sqlData = array(':productID' => $productID,
            ':newOrder' => $newOrder);
        $existing = $this->DB->query($this->sql, $this->sqlData);
        
        // Update that ID to use oldOrder (swap)
        if (!empty($existing)) {
            $existingID = $existing[0]['optionIDCustom'];
            $existingOrder = $existing[0]['optionSortOrder'];
            $this->sql = "
                UPDATE productOptionsCustom
                SET optionSortOrder = :oldOrder
                WHERE optionIDCustom = :existingID
                LIMIT 1";
            $this->sqlData = array(':oldOrder' => $oldOrder,
                ':existingID' => $existingID);
            $update = $this->DB->query($this->sql, $this->sqlData);
        } else {
            $existingOrder = $newOrder;
        }
        // Update desired option desired sort order
        $this->sql = "
            UPDATE productOptionsCustom
            SET optionSortOrder = :newOrder
            WHERE optionIDCustom = :optionID
            LIMIT 1";
        $this->sqlData = array(':newOrder' => $existingOrder,
            ':optionID' => $optionIDCustom);
        $new = $this->DB->query($this->sql, $this->sqlData);
        
        return true;
    }
    
    /**
     * updateProductOptionChoiceSortOrderCustom
     * 
     * Changes sort order of product option choice
     * 
     * @version v0.0.1
     * 
     * @since v0.0.1
     * 
     * @param type $productID
     * @param type $choiceIDCustom
     * @param type $oldOrder
     * @param type $newOrder
     * @return boolean
     */
    public function updateProductOptionChoiceSortOrderCustom($optionIDCustom, $choiceIDCustom, $oldOrder, $newOrder) {
        // Get choice ID with desired sort order to replace (or nearest if deletion gaps)
        if ($oldOrder < $newOrder) {
            $symbol = ">"; // Greater than
            $order = "ASC";
        } else {
            $symbol = "<"; // Less than
            $order = "DESC";
        }
        $this->sql = "
            SELECT choiceIDCustom, choiceSortOrderCustom
            FROM  productOptionsChoicesCustom
            WHERE optionIDCustom = :optionIDCustom
            AND choiceSortOrderCustom ".$symbol."= :newOrder
            ORDER BY choiceSortOrderCustom ".$order.", choiceIDCustom DESC
            LIMIT 1";
        $this->sqlData = array(':optionIDCustom' => $optionIDCustom,
            ':newOrder' => $newOrder);
        $existing = $this->DB->query($this->sql, $this->sqlData);
        
        // Update that ID to use oldOrder (swap)
        if (!empty($existing)) {
            $existingID = $existing[0]['choiceIDCustom'];
            $existingOrder = $existing[0]['choiceSortOrderCustom'];
            $this->sql = "
                UPDATE productOptionsChoicesCustom
                SET choiceSortOrderCustom = :oldOrder
                WHERE choiceIDCustom = :existingID
                LIMIT 1";
            $this->sqlData = array(':oldOrder' => $oldOrder,
                ':existingID' => $existingID);
            $update = $this->DB->query($this->sql, $this->sqlData);
        } else {
            $existingOrder = $newOrder;
        }
        // Update desired option desired sort order
        $this->sql = "
            UPDATE productOptionsChoicesCustom
            SET choiceSortOrderCustom = :newOrder
            WHERE choiceIDCustom = :optionID
            LIMIT 1";
        $this->sqlData = array(':newOrder' => $existingOrder,
            ':optionID' => $choiceIDCustom);
        $new = $this->DB->query($this->sql, $this->sqlData);
        
        return true;
    }
    
    
    /*******************************************************/
    /* Slightly less red-headed step-child functions below */
    /*******************************************************/

    
    /*
     * get_product_category
     */
    public function get_product_category()
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($this->id)) {
            trigger_error("Error #P000 (get_product_category): Missing parameter.", E_USER_ERROR);
            return false;
        }
        // Category ID
        $this->sql  = "		
			SELECT `categoryID`
			FROM `products`
			WHERE `productID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            $this->categoryID = $return_arr[0]['categoryID'];
        } else {
            trigger_error("Error #P000 (get_product_category): Invalid results.", E_USER_ERROR);
            return false;
        }
        // Category Name
        $this->sql  = "		
			SELECT `category_code`, `parent_categoryID`, `level_number`, `name`, `description`
			FROM `productCategories`
			WHERE `categoryID` = '" . $this->categoryID . "'";
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            $this->category_code         = $return_arr[0]['category_code'];
            $this->parent_categoryID     = $return_arr[0]['parent_categoryID'];
            $this->category_level_number = $return_arr[0]['level_number'];
            $this->categoryName          = $return_arr[0]['name'];
            $this->category_description  = $return_arr[0]['description'];
            return true;
        } else {
            trigger_error("Error #P000 (get_product_category): Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    /*
     * get_productStatus
     */
    public function get_productStatus()
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($this->id)) {
            trigger_error("Error #P000 (get_productStatus): Missing parameter.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "
			SELECT `active`
			FROM `products`
			WHERE `productID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (isset($return_arr)) {
            return $this->active = $return_arr[0]['active'];
        } else {
            trigger_error("Error #P000 (get_productStatus): Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    /*
     * get_productName
     */
    public function get_productName()
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($this->id)) {
            trigger_error("Error #P000 (get_productName): Missing parameter.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "
			SELECT `name`
			FROM `products`
			WHERE `productID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (isset($return_arr)) {
            return $this->name = $return_arr[0]['name'];
        } else {
            trigger_error("Error #P000 (get_productName): Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    /*
     * get_productPrice
     */
    public function get_productPrice()
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($this->id)) {
            trigger_error("Error #P000 (get_productPrice): Missing parameter.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "
			SELECT `price`
			FROM `products`
			WHERE `productID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (isset($return_arr)) {
            $price       = $this->price = $return_arr[0]['price'];
            $price       = Util::getFormattedNumber($price);
            return $price;
        } else {
            trigger_error("Error #P000 (get_productPrice): Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    /*
     * getProductDescriptions
     */
    public function getProductDescriptions()
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($this->id)) {
            trigger_error("Error #P000 (getProductDescriptions): Missing parameter.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "
			SELECT `productDescriptionPublic`, `productDescriptionPrivate`
			FROM `products`
			WHERE `productID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (isset($return_arr)) {
            $this->productDescriptionPublic  = $return_arr[0]['productDescriptionPublic'];
            $this->productDescriptionPrivate = $return_arr[0]['productDescriptionPrivate'];
            return true;
        } else {
            trigger_error("Error #P000 (getProductDescriptions): Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    /***************************
      Exiled functions...
      ...review for need
      remove....
     ******************************/

    /*
     * admin_get_productCartID
     */
    public function admin_get_productCartID()
    {
        \Errors::debugLogger(__METHOD__, 5);
        if (empty($this->categoryID)) {
            trigger_error("Error #P000 (admin_get_productCartID): Missing parameter.", E_USER_ERROR);
            return false;
        }
        $this->sql  = "		
			SELECT `cartID`
			FROM `" . DBNAME . "`.`refCartsProductCategories`
			WHERE `categoryID` = '" . $this->categoryID . "'";
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            return $this->cartID = $return_arr[0]['cartID'];
        } else {
            trigger_error("Error #P000 (admin_get_productCartID): Invalid results.", E_USER_ERROR);
            return false;
        }
    }

    public function get_productSettings($name = false)
    {
        \Errors::debugLogger(__METHOD__, 5);
        /*
         * RETURNS: array of all settings of this id
         * OPTIONAL: string name of setting to return that value if exists
         */

        // IF Named setting requested (and data previously loaded): RETURNS: string(*) of setting value of passed setting name
        if (!empty($name)) {
            foreach ($this->data as $setting) {
                if (isset($setting['name']) && $setting['name'] == $name) {
                    return $setting['value'];
                }
            }
            // Not found in data
            trigger_error("Error #279: Unable to retrieve '" . $name . "', make sure all settings have been loaded first", E_USER_ERROR);
            return false;
        } #endif:$name
        // Load cart ID and status
        self::getProductCategoryID();
        self::getProductCartID();
        self::getProductStatus();

        // Save into variables as all data is overwritten by select results
        $thisID         = $this->id;
        $thisCategoryID = $this->categoryID;
        $thisCartID    = $this->cartID;
        $thisStatus     = $this->active;

        // Get all settings
        $this->sql  = "
			SELECT `settingID`, `name`, `type`, `value`
			FROM `" . DBNAME . "`.`productSettings`
			WHERE `productID` = '" . $this->id . "'";
        $return_arr = $this->DB->query($this->sql);
        if (!empty($return_arr)) {
            $this->data       = $return_arr;
            $this->id         = $thisID;
            $this->categoryID = $thisCategoryID;
            $this->cartID    = $thisCartID;
            $this->active     = $thisStatus;
            return true;
        } else {
            //print_r($return_arr);
            //trigger_error("Error #323: ", E_USER_ERROR);
            return false;
        }
    }

    public function update_ProductSettings()
    {
        \Errors::debugLogger(__METHOD__, 5);
        /*
         * PURPOSE: Updates product with data from user form.
         * [ ] 1. Sanitize data from user form
         * [/] 2. Take details from user form and update settings table using (make deleting and inserting new options possible))
         * [ ] 3. Ensure required fields only allow updating VALUE (not name/type)
         */
        if (
                !isset($_POST['step']) || !isset($_POST['productStatus']) || !isset($_POST['productCatID'])
        ) {
            trigger_error("Error #95: Please check your form and try again.", E_USER_ERROR);
            return false;
        }

        $this->catID = trim($_POST['productCatID']);
        if (empty($this->cartID)) {
            $this->getProductCartID();
        }

        /** 1 *****/
        /* (settings can be array so go through each setting#Name/type/value */

        // Sanitize!
        // [ ] Separate table ...
        $thisProductStatus = trim($_POST['productStatus']);
        if ($thisProductStatus == 'Active') {
            $thisProductStatus = 1;
        } else {
            $thisProductStatus = 0;
        }
        $this->sql = "UPDATE `products`
						SET productActive = '" . $thisProductStatus . "'
						WHERE `productID` = '" . $this->id . "'
							AND `categoryID` = '" . $this->catID . "'
							AND	`cartID` = '" . $this->cartID . "'";
        $returnID  = $this->DB->query($this->sql);
        if (!isset($returnID)) {
            trigger_error("Error #121: ", E_USER_ERROR);
            return false;
        }

        // Begin constructing sql statement
        $mySql = "INSERT INTO `" . DBNAME . "`.`productSettings`
					(`id`, `productID`, `categoryID`, `cartID`, `name`, `type`, `value`)
					VALUES";

        // Add to sql statement foreach user setting from form
        foreach ($_POST as $key => $value) {
            // Sanitize!
            $key   = trim($key);
            $value = trim($value);

            /** User Defined Setting *****/
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
                    $mySql .= "(" . $thisSettingID . ", " . $this->id . ", " . $this->catID . ", " . $this->cartID . ", '" . $thisSettingName . "', '" . $thisSettingType . "', '" . $thisSettingValue . "'),";
                }
            }
        }

        // Remove last comma
        $mySql     = trim($mySql);
        $mySql     = substr_replace($mySql, "", -1);
        $mySql .= " ON DUPLICATE KEY UPDATE `productID` = VALUES (`productID`),
											`categoryID` = VALUES (`categoryID`),
											`cartID` = VALUES (`cartID`),
											`name` = VALUES (`name`),
											`type` = VALUES (`type`),
											`value` = VALUES (`value`)";
        $this->sql = $mySql;
        $returnID  = $this->DB->query($this->sql);
        if (isset($returnID)) {
            return $returnID;
        } else {
            trigger_error("Error #256: ", E_USER_ERROR);
            return false;
        }
    }

    /*
      protected $DB, $sql, $sqlData;
      private $data = array();

      public function __construct() {
      $this->DB = new \Database();
      }

      public function __destruct() {
      echo '<h2>Cart destruct</h2>';
      unset($this->DB, $this->sql);
      }

      public function __set($key, $value) {
      $this->data[$key] = $value;
      }

      public function __get($key) {
      if ($key == 'data') { return $this->data; } // Returns entire data array
      if ($this->__isset($key)) { return $this->data[$key]; } // Returns found named data value
      if ($this->getProductSettings($key)) { return $this->getProductSettings($key); } // Returns found named setting value
      return false; // Not found
      }

      public function __isset($key) {
      if (array_key_exists($key, $this->data)) {
      return true;
      }
      else
      {
      return false;
      }
      }
     */
}
?>