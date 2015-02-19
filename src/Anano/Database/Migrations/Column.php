<?php

namespace Anano\Database\Migrations;

use ErrorException;

class Column
{
    public $name;
    public $type;
    public $size;
    public $data;
    public $unsigned = false;
    public $nullable = false;
    public $zerofill = false;
    public $default = false;
    public $index = false;
    public $auto_increment = false;
    
    public function __construct($name, $type, $size=null, $data=null)
    {
        $this->name = $name;
        $this->type = $type;
        $this->size = $size;
        $this->data = $data;
    }
    
    public function &unsigned()
    {
        $this->unsigned = true;
        return $this;
    }
    
    public function &nullable()
    {
        $this->nullable = true;
        if ($this->default === false)
            $this->default = null;
        return $this;
    }
    
    public function &zerofill()
    {
        $this->zerofill = true;
        return $this;
    }
    
    public function &defaultValue($value)
    {
        $this->default = $value;
        return $this;
    }
    
    /**
     * Nicer name wrapper for 'default', since that's a reserved word, but you can still call it.
     */
    
    public function __call($name, $args)
    {
        if ($name === 'default')
        {
            return $this->defaultValue($args[0]);
        }
        throw new ErrorException("Undefined method '$name'");
    }
    
    public function &index()
    {
        $this->index = 'index';
        return $this;
    }
    
    public function &unique()
    {
        $this->index = 'unique index';
        return $this;
    }
    
    public function &auto_increment()
    {
        $this->auto_increment = true;
        return $this;
    }
}