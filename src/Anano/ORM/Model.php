<?php

namespace Anano\ORM;

abstract class Model extends QueryBuilder
{
    public $table_name;         // Table name, defaults to plural form of lowercase class name.
    public $id_column = 'id';   // Can be used to override the name of the id column in the table.
    public $timestamps = true;  // Automatically update created_at and updated_at if exist.
    public $soft_delete = false;// Update deleted_at instead of deleting. Make sure column exists.
    
    private $fields;
    private $original_fields;
    
    
    public function __construct($id=null)
    {
        if (!$this->table_name)
            $this->table_name = strtolower(get_class($this)) . 's';
        
        if ($id)
        {
            $this->load($id);
        }
        else
        {
            // Initialise default values.
            foreach ($this->describe() as $col)
            {
                $def = null;
                if ($col['Default'] !== null)
                    $def = $col['Default'];
                $this->fields[$col['Field']] = $def;
            }
        }
    }
    
    public function __set($name, $value)
    {
        $this->fields[$name] = $value;
    }
    
    public function __get($name)
    {
        return $this->fields[$name];
    }
    
    public function load($id)
    {
        $this->fields = null;
        $this->original_fields = null;
        
        if ($result = $this->where($this->id_column, '=', $id)->result())
        {
            $this->fields = $result[0];
            $this->original_fields = $this->fields;
        }
    }
    
    public static function make($fields=array())
    {
        $model = new static;
        $model->fields = $fields;
        $model->original_fields = $fields;
        return $model;
    }
    
    public static function find($id)
    {
        return new static($id);
    }
    
    /**
     * Save the current model instance. By default only modified data is saved, and timestamp updated only if anything is changed.
     * This can be overridden with the $force argument.
     */
    
    public function save($force=false)
    {
        // If no id set, insert
        if (empty($this->fields[$this->id_column]))
        {
            if ($this->timestamps && array_key_exists('created_at', $this->fields))
                $this->fields['created_at'] = date('Y-m-d H:i:s');
            if ($this->timestamps && array_key_exists('updated_at', $this->fields))
                $this->fields['updated_at'] = date('Y-m-d H:i:s');
            
            if ($rv = $this->insert($this->fields))
                $this->fields[$this->id_column] = $this->lastInsertId();
            return $rv;
        }
        
        // If id set, update
        $changed_fields = array();
        
        foreach ($this->fields as $key => $val)
        {
            if ($val !== $this->original_fields[$key])
                $changed_fields[$key] = $val;
        }
        
        if ($force)
            $changed_fields = $this->fields;
        
        if (!empty($changed_fields))
        {
            if ($this->timestamps && array_key_exists('updated_at', $this->fields))
                $changed_fields['updated_at'] = date("Y-m-d H:i:s");
            
            return $this->where($this->id_column, '=', $this->fields[$this->id_column])->update($changed_fields);
        }
        
        return true;
    }
    
    /**
     * Update the updated_at timestamp without editing any data.
     */
    
    public function touch()
    {
        return $this->where($this->id_column, '=', $this->fields[$this->id_column])->update(array('updated_at' => date('Y-m-d H:i:s')));
    }
    
    public function delete($hard=false)
    {
        if ($this->soft_delete !== true || $hard)
            return $this->where($this->id_column, '=', $this->fields[$this->id_column])->destroy();
        else
            return $this->where($this->id_column, '=', $this->fields[$this->id_column])->update(array('deleted_at' => date('Y-m-d H:i:s')));
    }
}