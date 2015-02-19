<?php

namespace Anano\Database\Migrations\Serializers;

use Anano\Database\Migrations\Table;
use ErrorException;

class MySql implements SerializerInterface
{
    private $sql;
    
    /**
     * Converts a Table object into a CREATE statement for the relevant RDBMS, in this case MySQL.
     * @param   Table   $table  Table object to serialize.
     * @return  string          A valid SQL CREATE statement.
     */
    
    public function serialize(Table $table)
    {
        $sql = "CREATE TABLE `{$table->name}` \r\n";
        
        $lines = array();
        foreach ($table->columns as $column)
        {
            $parts = array();
            
            if ($column->type == 'enum')
            {
                $parts[] = "`{$column->name}`";
                $parts[] = "ENUM('". implode("','", (array)$column->data) ."')";
            }
            else
            {
                $parts[] = "`{$column->name}`";
                $parts[] = strtoupper($column->type) . ($column->size !== null ? "($column->size)" : '');
            }
            
            if ($column->unsigned)
                $parts[] = 'UNSIGNED';
            
            if ($column->zerofill)
                $parts[] = 'ZEROFILL';
            
            if ($column->nullable !== true && $column->default !== null)
                $parts[] = 'NOT NULL';
            
            if ($column->auto_increment)
                $parts[] = 'AUTO_INCREMENT';
            
            if ($column->index !== false)
                $table->indices[] = array($column->name, $column->index);
            
            else if ($column->default !== false)
            {
                if ($column->default === null)
                    $parts[] = 'DEFAULT NULL';
                else if (strtolower($column->default) == 'now')
                    $parts[] = 'DEFAULT CURRENT_TIMESTAMP';
                else
                    $parts[] = "DEFAULT '{$column->default}'";
            }
            
            $lines[] = implode(' ', $parts);
        }
        
        foreach ($table->indices as $index)
        {
            $cols = implode('`, `', (array)$index[0]);
            $index_name = implode('_', (array)$index[0]);
            $index = $index[1];
            
            if ($index == 'primary key')
                $lines[] = strtoupper($index) . " (`$cols`)";
            else
                $lines[] = strtoupper($index) . " `$index_name` (`$cols`)";
        }
        
        $sql .= "(\r\n". implode(",\r\n", $lines) ."\r\n)";
        
        return $sql;
    }
    
    /**
     * Serialize a TRUNCATE (or empty table) statement.
     */
    
    public function truncate($name)
    {
        return "TRUNCATE TABLE `$name`";
    }
    
    /**
     * Serialize a DROP (or delete table) statement.
     */
    
    public function drop($name)
    {
        return "DROP TABLE `$name`";
    }
}