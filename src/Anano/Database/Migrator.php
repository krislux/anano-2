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
                $temp = array();
                $temp[] = "`{$column->name}` " . self::typef($column->type);
                if (isset($column->size))
                    $temp[] = "({$column->size})";
                if (!isset($column->nullable) || !$column->nullable)
                    $temp[] = 'NOT NULL';
                if (isset($column->autoincrement) && $column->autoincrement)
                    $temp[] = 'AUTO_INCREMENT';
                if (isset($column->default) && $column->default)
                    $temp[] = "DEFAULT '{$column->default}'";
                if (isset($column->prkey) && $column->prkey)
                    $pk = $column->name;
                
                $lines[] = implode(' ', $temp);
            }
            
            if ($pk)
                $lines[] = "PRIMARY KEY (`$pk`)";
            
            $sql .= implode(",\r\n", $lines);
            
            $sql .= "\r\n)\r\nENGINE=InnoDB\r\n";
            
            if (isset($this->table->charset))
                $sql .= "CHARACTER SET = '{$this->table->charset}'";
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
                'int' => 'INT',
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