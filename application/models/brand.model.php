<?php

class Brand extends Model
{
    protected static function getBrandColumns()
    {
        $cols = array('brandID', 'brandName', 'brandActive', 'brandLabel', 'activeTheme');
        return $cols;
    }

}