<?php

class RewriteMapping extends Model
{
    protected static function getRewriteMappingColumns()
    {
        $cols = array('rewriteMappingID', 'aliasURL', 'actualURL', 'sortOrder', 'domainID');
        return $cols;
    }
    
    public function update($data, $table = NULL)
    {
        // DB Changes
        parent::update($data);
        
        // Alias Map Rewrite
        
    }
}