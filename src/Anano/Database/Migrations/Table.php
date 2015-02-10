<?php

namespace Anano\Database\Migrations;

use Anano\Database\DatabaseInterface;
use Anano\Database\Migrations\Serializers\SerializerInterface;
use ErrorException;

class Table
{
    private $serializer;
    private $database;
    
    public $name;
    public $columns = array();
    public $indices = array();
    
    public function __construct($name, SerializerInterface $serializer, DatabaseInterface $database=null)
    {
        $this->name = $name;
        $this->serializer = $serializer;
        $this->database = $database;
    }
    
    public function &primary($name)
    {
        $column = new Column($name, 'int');
        $column->auto_increment();
        $column->unsigned();
        $this->columns[] = $column;
        $this->indices[] = array($name, 'primary key');
        return $column;
    }
    
    public function &integer($name, $size=null)
    {
        $column = new Column($name, 'int', $size);
        $this->columns[] = $column;
        return $column;
    }
    
    public function &string($name, $size=255)
    {
        $column = new Column($name, 'varchar', $size);
        $this->columns[] = $column;
        return $column;
    }
    
    public function &text($name)
    {
        $column = new Column($name, 'text');
        $this->columns[] = $column;
        return $column;
    }
    
    public function &dateTime($name)
    {
        $column = new Column($name, 'datetime');
        $this->columns[] = $column;
        return $column;
    }
    
    public function &decimal($name, $size, $decimals=2)
    {
        $column = new Column($name, 'decimal', $size . ',' . $decimals);
        $this->columns[] = $column;
        return $column;
    }
    
    
    public function index($cols)
    {
        $this->indices[] = array($cols, 'index');
    }
    
    public function unique($cols)
    {
        $this->indices[] = array($cols, 'unique index');
    }
    
    
    
    /**
     * Output
     */
    
    public function save()
    {
        if ($this->database)
        {
            $sql = $this->serializer->serialize($this);
            try{
                return $this->database->query($sql);
            }
            catch (\Exception $err)
            {
                echo 'FAILED: ';
            }
        }
        else
            throw new ErrorException('Cannot save, no database object passed. Did you mean to use Table->sql() to save manually?');
    }
    
    public function sql()
    {
        return $this->serializer->serialize($this);
    }
    
    /**
     * Cleanup functions
     */
    
    public function truncate()
    {
        if (!$this->database)
            throw new ErrorException('Truncate requires a database object.');
        
        $sql = $this->serializer->truncate($this->name);
        try{
            return $this->database->query($sql);
        }
        catch (\Exception $err)
        {
            echo 'FAILED: ';
        }
    }
    
    public function drop()
    {
        if (!$this->database)
            throw new ErrorException('Drop requires a database object.');
        
        $sql = $this->serializer->drop($this->name);
        try{
            return $this->database->query($sql);
        }
        catch (\Exception $err)
        {
            echo 'FAILED: ';
        }
    }
}