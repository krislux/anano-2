<?php

namespace Anano\Database\Migrations\Serializers;

use Anano\Database\Migrations\Table;
use ErrorException;

class MySql implements SerializerInterface
{
    private $sql;
    
    public function serialize(Table $table)
    {
        $sql = "CREATE TABLE `{$table->name}` \r\n";
        
        $lines = array();
        foreach ($table->columns as $column)
        {
            $parts = array();
            
            $parts[] = "`{$column->name}`";
            
            $parts[] = strtoupper($column->type) . ($column->size !== null ? "($column->size)" : '');
            
            if ($column->unsigned)
                $parts[] = 'UNSIGNED';
            
            if ($column->zerofill)
                $parts[] = 'ZEROFILL';
            
            if ($column->nullable !== true)
                $parts[] = 'NOT NULL';
            
            if ($column->auto_increment)
                $parts[] = 'AUTO_INCREMENT';
            
            if ($column->index !== false)
                $table->indices[] = array($column->name, $column->index);
            
            else if ($column->default !== false)
            {
                if ($column->default === null)
                {
                    if (!$column->nullable)
                        throw new ErrorException('Column default set to null but not nullable.');
                    
                    $parts[] = 'DEFAULT NULL';
                }
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
    
    public function truncate($name)
    {
        return "TRUNCATE TABLE `$name`";
    }
    
    public function drop($name)
    {
        return "DROP TABLE `$name`";
    }
}