<?php

namespace Anano;

final class App
{
    private static $config;
    private static $profile_start;
    
    /**
     * Initialises framework, including setting up autoloading.
     */
    
    public static function init()
    {
        // For benchmarking
        self::$profile_start = microtime(true);
        
        require_once 'Config.php';
        require_once 'Helpers.php';
        
        if (Config::get('app.debug'))
        {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }
        
        header_remove('X-Powered-By');
        date_default_timezone_set(Config::get('app.timezone'));
        
        $aliases = Config::get('aliases');
        foreach ($aliases as $key => $val)
            class_alias($val, $key);
        
        $session = Config::get('app.session');
        if ($session)
            \Session::start($session);
        
        // ActiveRecord set up if included.
        if (defined('PHP_ACTIVERECORD_VERSION_ID'))
        {
            \ActiveRecord\Config::initialize(function($cfg)
            {
                foreach (Config::get('database.connections') as $key => $val)
                {
                    extract($val);
                    $connections[$key] = "$driver://$username:$password@$host/$database";
                }
                
                $cfg->set_model_directory('app/models');
                $cfg->set_connections($connections);
                
                $cfg->set_default_connection( Config::get('database.default') );
            });
        }
        
        // Load start files
        require_if_exists('app/start/global.php');
        require_if_exists('app/start/'. hostname() .'.php');
        
        return new static;
    }
    
    /**
     * Get the root path for URLs, not local file loading. Use the ROOT_DIR constant for that.
     */
    
    public static function root()
    {
        static $root;
        if ($root === null)
            $root = rtrim( dirname($_SERVER['PHP_SELF']), '\\/' );
        return $root;
    }
    
    /**
     * Simple IoC factory. Look to config/app.php for bindings.
     */
    
    public static function make($alias, array $args=array())
    {
        $binds = Config::get('app.binds');
        $name = $binds[$alias];
        
        if (empty($args))
        {
            return new $name();
        }
        else
        {
            $class = new \ReflectionClass($name);
            return $class->newInstanceArgs($args);
        }
    }
    
    public static function &singleton($alias, array $args=array())
    {
        static $singletons = array();
        if (!isset($singletons[$alias]))
            $singletons[$alias] = self::make($alias, $args);
        return $singletons[$alias];
    }
    
    /**
     * Benchmark function for testing and optimisation purposes.
     * Pass in a function, get how many milliseconds it takes to run it $iterations times.
     */
    
    public static function benchmark($lambda, $iterations=1000)
    {
        $t1 = microtime(true);
        
        for ($i = 0; $i < $iterations; $i++)
            $lambda();

        $t2 = microtime(true);
        
        return ($t2 - $t1) * 1000;
    }
    
    public function __construct()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            throw new \ErrorException($errstr, $errno, 0, $errfile, $errline);
        });
        
        $router = self::make('Router');
        $response = $router->run();
        
        if($response instanceof \Response)
        {
            foreach ($response->getHeaders() as $key => $val)
            {
                if (is_array($val))
                {
                    if ($val[0] === null)
                        header ($key, true, $val[1]);
                    else
                        header("$key: {$val[0]}", true, $val[1]);
                }
                else
                    header("$key: $val", true);
            }
            
            $response->before();
        }
        
        // Main output - this should be the only place to print anything during a normal execution.
        print $response;
        
        if ($response instanceof \Response)
        {
            $response->after();
        }
        
        if (!$response instanceof \Response || $response->getHeaders() === array())
        {
            $this->profile_output();
        }
    }
    
    private function profile_output()
    {
        if (Config::get('app.debug') && Config::get('app.profile'))
        {
            $t1 = self::$profile_start;
            $t2 = microtime(true);
            
            echo "\r\n<!-- Profiling info. You can disable this in app/config/app.php -->\r\n";
            echo '<div style="position: absolute; bottom: 10px; right: 10px; font-size: 9px; font-family: sans-serif;">';
            echo round(($t2-$t1)*1000, 2) ,' ms &bull; ';
            echo round(memory_get_peak_usage(false) / 1024/1024, 2) ,' (', round(memory_get_peak_usage(true) / 1024/1024, 2) ,') MB';
            echo '</div>';
        }
    }
}