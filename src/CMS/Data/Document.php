<?php

namespace CMS\Data;

class Document extends \Anano\Database\ORM\Model
{
    public $timestamps = false;
    public $struct;
    
    public function __construct($table_name)
    {
        $this->table_name = $table_name;
        $this->struct = $this->describe(); // @todo Migrator::fromSQL
    }
    
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }   
    
    public function __get($name)
    {
        return $this->fields[$name];
    }
    
    public function toArray($simple=false)
    {
        if ($simple)
            return parent::toArray();
        
        
    }
    
    
    public static function doctypes()
    {
        static $doctypes;
        $path = STORAGE_DIR . '/cms/doc_types.json';
        if ($doctypes === null)
        {
            if (file_exists($path))
            {
                $doctypes = file_get_contents($path);
                $doctypes = json_decode($doctypes, true);
            }
        }
        return $doctypes;
    }
    
    public static function doctype($name)
    {
        $doctypes = self::doctypes();
        return $doctypes[$name];
    }
    
    public static function datatypes()
    {
        static $datatypes;
        $path = STORAGE_DIR . '/cms/data_types.json';
        if ($datatypes === null)
        {
            if (file_exists($path))
            {
                $datatypes = file_get_contents($path);
                $datatypes = json_decode($datatypes, true);
            }
        }
        return $datatypes;
    }
    
    public static function datatype($name)
    {
        $datatypes = self::datatypes();
        return $datatypes[$name];
    }
}