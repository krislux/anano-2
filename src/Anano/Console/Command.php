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
            
            if (!is_dir(self::DIR))
                mkdir(self::DIR, 666, true);
            
            file_put_contents(self::DIR . ucfirst($command) . 'Command.php', $buffer);
            
            echo "Done.\r\n";
        }
        else
            echo "Incorrect format. Use migrate:make <table>.\r\n";
    }
}