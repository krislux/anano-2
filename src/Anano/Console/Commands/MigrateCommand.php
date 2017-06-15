<?php namespace Anano\Console\Commands;

use ErrorException;
use Anano\Console\Command;
use Anano\Console\Template;
use Anano\Database\Migrations\MigrationInterface;

class MigrateCommand extends Command
{
    /**
     * Run the migrations, creating the tables.
     * You can pass a table name to run only that migration.
     */
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
    
    /**
     * Reverse the migrations, dropping the tables.
     * You can pass a table name to reverse only that migration.
     */
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
    
    /**
     * Reload the migrations, dropping and remaking the tables.
     * You can pass a table name to reload only that migration.
     */
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
    
    /**
     * Create a migration file for the given table name.
     *      --dir  Optional folder to place the file in.
     *             Otherwise the first item of `migration_dirs` will be used. 
     */
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
        $dir = $this->getOption('dir', $dirs[0]);
        $filename = 'create_' . $table . '.php';

        if (file_put_contents(rtrim($dir, '/')  .'/'. $filename, $buffer)) {
            return sprintf('Migration `%s` created in %s', $filename, $dir);
        }
        return "An error occured. Make sure you have write permissions for `$dir`";
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
