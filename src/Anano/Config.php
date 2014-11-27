<?php

namespace Anano;

class Config
{
    /**
     * Get configuration file with automatic environment loader.
     *
     * You can define an environment by creating a subfolder in /app/config
     * with the hostname of the environment computer in lowercase.
     * Any config files copied to that folder will be loaded instead of the default.
     *
     * @param   string  $name       Config file and possible section to load, format: file.section
     * @param   array   $default    [Optional] array of default values to merge and fill blanks.
     */
    
    public static function get($name, array $default=null)
    {
        static $loaded = array();
        
        if (strpos($name, '.'))
        {
            $ar = explode('.', $name);
            $file = $ar[0];
        }
        else
        {
            $file = $name;
        }
        
        if (isset($loaded[$file]))
        {
            $config = $loaded[$file];
        }
        else
        {
            $hn = hostname();
            $path = ROOT_DIR . "/app/config/$hn/$file.php";
            if (!$hn || !file_exists($path))
                $path = ROOT_DIR . "/app/config/$file.php";
            
            $config = require $path;
        }
        
        // Save to memory for quicker subsequent fetching
        $loaded[$file] = $config;
        
        if ($default)
        {
            $config = array_unique( array_merge($default, $config) );
        }
        
        if (isset($ar))
        {
            $cnt = count($ar);
            for ($i = 1; $i < $cnt; $i++)
            {
                $config = $config[$ar[$i]];
            }
        }
        
        return $config;
    }
}