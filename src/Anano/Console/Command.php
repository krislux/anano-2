<?php

namespace Anano\Console;

use \Anano\Database\Migrations\Migration;

class Command
{
    const DIR = 'app/commands/';
    
    public function make($args)
    {
        if (count($args) >= 1)
        {
            $command = strtolower($args[0]);
            $buffer = file_get_contents(__DIR__ . '/Templates/command.txt');
            $buffer = str_replace('%CCCOMMAND%', ucfirst($command), $buffer);
            $buffer = str_replace('%LCCOMMAND%', $command, $buffer);
            
            $filepath = self::DIR . ucfirst($command) . 'Command.php';
            
            if (!file_exists($filepath))
            {
                try
                {
                    if (!is_dir(self::DIR))
                        mkdir(self::DIR, 666, true);
                    
                    file_put_contents($filepath, $buffer);
                }
                catch (\Exception $e)
                {
                    return "Unable to create file. Run 'sudo install' to set permissions.";
                }
                
                return "$filepath created.";
            }
            else
            {
                return "Command file already exists.";
            }
        }
        else
            return "Incorrect format. Use command:make <table>.";
    }
}