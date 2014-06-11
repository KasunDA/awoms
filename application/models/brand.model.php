<?php

class Brand extends Model
{
    protected static function getBrandColumns()
    {
        $cols = array('brandID', 'brandName', 'brandActive', 'brandLabel', 'activeTheme');
        return $cols;
    }

    public function getBrandInfo($brandID)
    {
        // ACL CHECK?
        
        $cols  = self::getBrandColumns();
        $where = array('brandID' => $brandID);
        
        #$where = 'brandID=' . $brandID;
        $res   = self::select($cols, $where); // Dereferencing not available until php v5.4-5.5
        return $res[0];
    }

    public function getBrandIDs($where = NULL)
    {

        // ACL CHECK?

        $cols  = 'brandID';
        $order = 'brandName ASC';
        return self::select($cols, $where, $order);
    }

    /**
     * Get Brand List
     * 
     * Returns array of all active brands and their info
     * 
     * @return array
     */
    public function getBrands($where = NULL)
    {
        Errors::debugLogger(__METHOD__ . '@' . __LINE__, 10);        
        if ($where == NULL) { $where = 'brandActive=1'; }
        $brandIDs = self::getBrandIDs($where);
        $brands   = array();
        foreach ($brandIDs as $b) {
            $brand    = self::getBrandInfo($b['brandID']);
            $brands[] = $brand;
        }
        return $brands;
    }

}