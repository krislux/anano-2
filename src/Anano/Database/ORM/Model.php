<?php

namespace Anano\Database\ORM;

use Exception;

abstract class Model extends QueryBuilder
{
    public $table_name;         // Table name, defaults to plural form of lowercase class name.
    public $id_column = 'id';   // Can be used to override the name of the id column in the table.
    public $timestamps = true;  // Automatically update created_at and updated_at if exist.
    public $soft_delete = false;// Update deleted_at instead of deleting. Make sure column exists.

    private $fields;
    private $original_fields;


    public function __construct($id = null)
    {
        if (!$this->table_name)
            $this->table_name = strtolower(get_class($this)) . 's';

        if ($id !== null)
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

    public function describe()
    {
        static $table;
        if ( ! $table)
        {
            $table = parent::describe();
        }
        return $table;
    }

    public function __set($name, $value)
    {
        if(is_array($value))
            throw new Exception('Flat data expected, array given.');
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

        if ($result = $this->where($this->id_column, '=', $id)->get()->current()->toArray())
        {
            $this->fields = $result;
            $this->original_fields = $result;
        }
        else
            return false;
    }

    public static function make($fields = array())
    {
        $model = new static;
        $model->fields = $fields;
        $model->original_fields = $fields;
        return $model;
    }

    /**
     * Return a model instance from an id.
     */

    public static function find($id)
    {
        return new static($id);
    }

    /**
     * Return an indexed field from a set of results instead of associative.
     * Useful for function statements like COUNT(), where you can't use the magic method unless you alias it.
     */

    public function field($index)
    {
        $fields = array_values($this->fields);
        return $fields[$index];
    }

    /**
     * Set fields as associative array instead of individually.
     */

    public function &data(array $arr)
    {
        foreach ($arr as $key => $val)
        {
            $this->fields[$key] = $val;
        }

        return $this;
    }

    /**
     * Convert a model instance to a simple associative array.
     */

    public function toArray()
    {
        if ( ! is_array($this->fields)) return null;

        $ar = array();
        foreach ($this->fields as $key => $val)
            $ar[$key] = $val;
        return $ar;
    }

    /**
     * Save the current model instance. By default only modified data is saved, and timestamp updated only if anything is changed.
     * This can be overridden with the $force argument.
     */

    public function save($force = false)
    {
        // If no id set, insert
        if (empty($this->fields[$this->id_column]))
        {
            if ($this->timestamps && array_key_exists('created_at', $this->fields))
                $this->fields['created_at'] = date('Y-m-d H:i:s');

            $rv = $this->insert($this->fields);
            if ($rv !== false)
                $this->fields[$this->id_column] = (int)$this->lastInsertId();
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

        if ( ! empty($changed_fields))
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

    public function delete($hard = false)
    {
        if ($this->soft_delete !== true || $hard)
            return $this->where($this->id_column, '=', $this->fields[$this->id_column])->destroy();
        else
            return $this->where($this->id_column, '=', $this->fields[$this->id_column])->update(array('deleted_at' => date('Y-m-d H:i:s')));
    }

    public function truncate()
    {
        return parent::truncate();
    }
}
