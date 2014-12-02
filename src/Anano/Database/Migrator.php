<?php

namespace Anano\Database;

class Migrator
{
    private static $driver;
    private $table;
    
    public function __construct($driver='mysql')
    {
        static::$driver = $driver;
    }
    
    /**
     * Load table structure from JSON input, either file or string.
     */
    
    public function &fromJson($source)
    {
        if (($this->table = json_decode($this->loadString($source))) === null)
            throw new \ErrorException('Invalid JSON');
        return $this;
    }
    
    /**
     * Save structure to JSON file
     */
    
    public function toJson($fileName)
    {
        if ($this->table)
        {
            $json = json_encode($this->table);
            file_put_contents($fileName, $json);
        }
        return true;
    }
    
    /**
     * Build the query to create the table.
     * If $db parameter supplied, use that driver to execute the query, otherwise return SQL.
     */
    
    public function buildQuery(Database $db=null)
    {
        switch (static::$driver)
        {
        case 'mysql':
            
            $sql = "CREATE TABLE `{$this->table->name}` (\r\n";
            $lines = array();
            $pk = null;
            
            foreach ($this->table->columns as $column)
            {
                if (!isset($column->slug))
                    $column->slug = preg_replace('/[^\w]/', '', strtolower($column->name) );
                
                $temp = array();
                $temp[] = "\t`{$column->slug}` " . self::typef($column->type);
                if ($column->type == 'bool')
                {
                    $column->size = 1;
                    $column->unsigned = true;
                }
                if (isset($column->size))
                    $temp[] = "({$column->size})";
                if (isset($column->unsigned) && $column->unsigned)
                    $temp[] = 'UNSIGNED';
                if (!isset($column->nullable) || !$column->nullable)
                    $temp[] = 'NOT NULL';
                if (isset($column->autoincrement) && $column->autoincrement)
                    $temp[] = 'AUTO_INCREMENT';
                if (isset($column->default))
                    $temp[] = "DEFAULT '{$column->default}'";
                if (isset($column->prkey) && $column->prkey)
                    $pk = $column->name;
                
                $lines[] = implode(' ', $temp);
            }
            
            if ($pk)
                $lines[] = "\tPRIMARY KEY (`$pk`)";
            
            $sql .= implode(",\r\n", $lines);
            
            $sql .= "\r\n)\r\nENGINE = InnoDB\r\n";
            
            if (isset($this->table->charset))
                $sql .= "CHARACTER SET = '{$this->table->charset}'\r\n";
            if (isset($this->table->collation))
                $sql .= "COLLATE = '{$this->table->collation}'\r\n";
            
            break;
        }
        
        if ($db !== null)
        {
            return $db->query($sql);
        }
        return $sql;
    }
    
    /**
     * Format an internal datatype as real datatype depending on DB driver.
     */
    
    private static function typef($typeName)
    {
        $typeName = strtolower($typeName);
        
        switch (static::$driver)
        {
        case 'mysql':
            static $mysql_types = array(
                'bool' => 'TINYINT',
                'tinyint' => 'TINYINT',
                'int' => 'INT',
                'double' => 'DOUBLE',
                'string' => 'VARCHAR',
                'text' => 'TEXT',
                'binary' => 'BLOB',
                'enum' => 'SET',
                'date' => 'DATE',
                'time' => 'TIME',
                'datetime' => 'DATETIME',
            );
            return $mysql_types[$typeName];
            break;
        }
    }
    
    /**
     * If source is file or URL, return content - otherwise just return input.
     */
    
    private function loadString($source)
    {
        if (is_file($source) || strpos($source, '://'))
        {
            return file_get_contents($source);
        }
        return $source;
    }
}