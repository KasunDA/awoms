<?php

class Domain extends Model
{
    protected static function getDomainColumns()
    {
        $cols = array('domainID', 'brandID', 'domainName', 'domainActive', 'parentDomainID');
        return $cols;
    }

}