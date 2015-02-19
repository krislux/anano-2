<?php

namespace Anano;

class Console
{
    public function __construct($args)
    {
        $command = explode(':', $args[0]);
        
        if (count($command) == 2)
        {
            list($classname, $method) = $command;
            $classname = ucfirst($classname);
            
            $internal_path = "\\Anano\\Console\\$classname";
            if(class_exists($internal_path))
            {
                $class = new $internal_path;
            }
            else
            {
                $classname = $classname . 'Command';
                $user_path = ROOT_DIR . '/app/commands/' . $classname . '.php';
                if (file_exists($user_path))
                {
                    require $user_path;
                    $class = new $classname;
                }
            }
            
            if (method_exists($class, $method))
            {
                $rv = $class->$method( array_slice($args, 1) );
            }
            else
            {
                echo "Command '$method' not found.\r\n";
                return false;
            }
            
            echo $rv . "\r\n";
        }
        else if (count($command) == 1)
        {
            $command = $command[0];
            if (method_exists($this, $command))
            {
                $this->{$command}( array_slice($args, 1) );
            }
            else
            {
                echo "Command '$command' not found.\r\n";
                return false;
            }
        }
        else
        {
            echo "Incorrect command format.\r\n";
            return false;
        }
    }
    
    public function hash($input)
    {
        return \Anano\Crypto\Hash::make($input[0]);
    }
    
    public function clearcache()
    {
        foreach (glob('app/storage/cache/views/*.php') as $file)
        {
            unlink($file);
        }
        
        return "Done.";
    }
}