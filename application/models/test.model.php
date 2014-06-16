<?php

class Test extends Model
{
    protected static function getTestColumns()
    {
        return false;
    }
    
    public function update($data, $table = NULL)
    {
        Errors::debugLogger(__METHOD__.': ');
        Errors::debugLogger($data);
        
        parent::update($data, $table);
    }
}