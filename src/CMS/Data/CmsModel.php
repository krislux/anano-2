<?php

namespace CMS\Data;

class CmsModel extends \Anano\Database\ORM\Model
{
    public $timestamps = false;
    
    public function __construct($table_name)
    {
        $this->table_name = $table_name;
        $struct = $this->describe();
        dd($struct);
    }
    
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }
    
    public function __get($name)
    {
        return $this->fields[$name];
    }
}
