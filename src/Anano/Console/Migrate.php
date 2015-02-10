<?php

namespace Anano\Console;

use \Anano\Database\Migrations\Migration;

class Migrate
{
    const DIR = 'app/database/migrations/';
    
    public function up()
    {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            $migration->up();
            echo get_class($migration) . " up.\r\n";
        }
        echo "Done.\r\n";
    }
    
    public function down()
    {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            $migration->down();
            echo get_class($migration) . " down.\r\n";
        }
        echo "Done.\r\n";
    }
    
    public function reload()
    {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            $classname = get_class($migration);
            $migration->down();
            echo get_class($migration) . " down.\r\n";
            
            $migration->up();
            echo get_class($migration) . " up.\r\n";
        }
        echo "Done.\r\n";
    }
    
    public function make($args)
    {
        if (count($args) >= 1)
        {
            $table = strtolower($args[0]);
            $buffer = file_get_contents(__DIR__ . '/Templates/migrate_create.txt');
            $buffer = str_replace('%CCTABLE%', ucfirst($table), $buffer);
            $buffer = str_replace('%LCTABLE%', $table, $buffer);
            
            file_put_contents(self::DIR . 'create_' . $table . '.php', $buffer);
            
            echo "Done.\r\n";
        }
        else
            echo "Incorrect format. Use migrate:make <table>.\r\n";
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