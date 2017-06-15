<?php namespace Anano\Console\Commands;

use ErrorException;
use Anano\Console\Command;
use Anano\Console\Template;
use Anano\Database\Migrations\MigrationInterface;

class MigrateCommand extends Command
{
    public function up($table = null)
    {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            if ($table && $table != $migration->table)
                continue;
            
            $migration->up();
            echo get_class($migration) . " up." . PHP_EOL;
        }
        return "Done.";
    }
    
    public function down($table = null)
    {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            if ($table && $table != $migration->table)
                continue;
            
            $migration->down();
            echo get_class($migration) . " down." . PHP_EOL;
        }
        return "Done.";
    }
    
    public function reload($table = null)
    {
        $migrations = $this->getMigrations();
        
        foreach ($migrations as $migration)
        {
            if ($table && $table != $migration->table)
                continue;
            
            $classname = get_class($migration);
            $migration->down();
            echo get_class($migration) . " down." . PHP_EOL;
            
            $migration->up();
            echo get_class($migration) . " up." . PHP_EOL;
        }
        return "Done.";
    }
    
    public function make($table)
    {
        if ( ! preg_match('/^[a-zA-Z_]+[\w]*$/', $table)) {
            throw new ErrorException("Invalid table name `$table`. Name must conform to standard variable naming requirements.");
        }
        
        $buffer = new Template('migrate_create', [
            'name' => ucfirst(snake_to_camel($table)),
            'lname' => $table
        ]);

        $dirs = $this->getConfig('migration_dirs');
        file_put_contents(rtrim($dirs[0], '/') . '/create_' . $table . '.php', $buffer);
        
        return "Done.";
    }
    
    private function getMigrations()
    {
        $output = array();

        $files = [];
        foreach ($this->getConfig('migration_dirs') as $dir) {
            foreach (glob(rtrim($dir, '/') . '/*.php') as $file) {
                $files[] = $file;
            }
        }
        
        foreach ($files as $file)
        {
            require $file;
            
            $classname = basename($file, '.php');
            
            if ($classname == strtolower($classname))
            {
                $classparts = array_map('ucfirst', explode('_', $classname));
                $classname = implode('', $classparts);
            }
            
            $class = new $classname;
            
            if ($class instanceof MigrationInterface)
            {
                if ( ! isset($class->disabled) || ! $class->disabled)
                {
                    $output[] = $class;
                }
            }
        }
        
        return $output;
    }
}
