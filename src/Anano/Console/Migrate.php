<?php

namespace Anano\Console;

use \Anano\Database\Migrations\Migration;

class Migrate
{
    const DIR = 'app/database/migrations/';
    
    public function up($args)
    {
        $table = count($args) ? $args[0] : null;
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            if ($table && $table != $migration->table)
                continue;
            
            $migration->up();
            echo get_class($migration) . " up.";
        }
        return "Done.";
    }
    
    public function down($args)
    {
        $table = count($args) ? $args[0] : null;
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            if ($table && $table != $migration->table)
                continue;
            
            $migration->down();
            echo get_class($migration) . " down.";
        }
        return "Done.";
    }
    
    public function reload($args)
    {
        $table = count($args) ? $args[0] : null;
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            if ($table && $table != $migration->table)
                continue;
            
            $classname = get_class($migration);
            $migration->down();
            echo get_class($migration) . " down.";
            
            $migration->up();
            echo get_class($migration) . " up.";
        }
        return "Done.";
    }
    
    public function make($args)
    {
        if (count($args) >= 1)
        {
            $table = strtolower($args[0]);
            $buffer = file_get_contents(__DIR__ . '/Templates/migrate_create.txt');
            $buffer = str_replace('%CCTABLE%', ucfirst(snake_to_camel($table)), $buffer);
            $buffer = str_replace('%LCTABLE%', $table, $buffer);
            
            file_put_contents(self::DIR . 'create_' . $table . '.php', $buffer);
            
            return "Done.";
        }
        else
            return "Incorrect format. Use migrate:make <table>.";
    }
    
    private function getMigrations()
    {
        $output = array();
        
        foreach (glob(self::DIR . '*.php') as $file)
        {
            require $file;
            
            $classname = basename($file, '.php');
            
            if ($classname == strtolower($classname))
            {
                $classparts = array_map('ucfirst', explode('_', $classname));
                $classname = implode('', $classparts);
            }
            
            $class = new $classname;
            
            if ($class instanceof Migration)
            {
                if (!isset($class->disabled) || !$class->disabled)
                {
                    $output[] = $class;
                }
            }
        }
        
        return $output;
    }
}