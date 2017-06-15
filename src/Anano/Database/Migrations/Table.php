<?php

namespace Anano\Database\Migrations;

use Anano\Database\DatabaseInterface;
use Anano\Database\Migrations\Column;
use Anano\Database\Migrations\Serializers\SerializerInterface;
use ErrorException;

class Table
{
    private $serializer;
    private $database;

    public $name;
    public $comment = false;
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

    public function &tinyInteger($name, $size=null)
    {
        $column = new Column($name, 'tinyint', $size);
        $this->columns[] = $column;
        return $column;
    }

    public function &bigInteger($name, $size=null)
    {
        $column = new Column($name, 'bigint', $size);
        $this->columns[] = $column;
        return $column;
    }

    public function &string($name, $size=255)
    {
        $column = new Column($name, 'varchar', $size);
        $this->columns[] = $column;
        return $column;
    }

    public function &fixedString($name, $size)
    {
        $column = new Column($name, 'char', $size);
        $this->columns[] = $column;
        return $column;
    }

    public function &text($name)
    {
        $column = new Column($name, 'text');
        $this->columns[] = $column;
        return $column;
    }

    public function &binary($name, $size=255)
    {
        $column = new Column($name, 'varbinary', $size);
        $this->columns[] = $column;
        return $column;
    }

    public function &fixedBinary($name, $size)
    {
        $column = new Column($name, 'binary', $size);
        $this->columns[] = $column;
        return $column;
    }

    public function &dateTime($name)
    {
        $column = new Column($name, 'datetime');
        $this->columns[] = $column;
        return $column;
    }

    public function &date($name)
    {
        $column = new Column($name, 'date');
        $this->columns[] = $column;
        return $column;
    }

    public function &decimal($name, $size, $decimals=2)
    {
        $column = new Column($name, 'decimal', $size . ',' . $decimals);
        $this->columns[] = $column;
        return $column;
    }

    public function &enum($name, array $options)
    {
        $column = new Column($name, 'enum', null, $options);
        $this->columns[] = $column;
        return $column;
    }


    /**
     * Specials / shorthands
     */

    public function timestamps()
    {
        $this->dateTime('created_at');
        $this->dateTime('updated_at')->default(null);
    }

    public function comment($str)
    {
        $this->comment = $str;
    }


    /**
     * Indices
     */

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
                echo "FAILED: ({$err->getMessage()}) ";
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

    private function _truncate()
    {
        $sql = $this->serializer->truncate($this->name);
        try{
            return $this->database->query($sql);
        }
        catch (\Exception $err)
        {
            echo "FAILED: ({$err->getMessage()}) ";
        }
    }

    private function _drop()
    {
        $sql = $this->serializer->drop($this->name);
        try{
            return $this->database->query($sql);
        }
        catch (\Exception $err)
        {
            echo "FAILED: ({$err->getMessage()}) ";
        }
    }


    /**
     * Static shorthands
     */

    public static function create($name, $serializer, $database, $callback)
    {
        $table = new static($name, $serializer, $database);
        $callback($table);
        $table->save();
    }

    public static function truncate($name, $serializer, $database)
    {
        $table = new static($name, $serializer, $database);
        $table->_truncate();
    }

    public static function drop($name, $serializer, $database)
    {
        $table = new static($name, $serializer, $database);
        $table->_drop();
    }
}
