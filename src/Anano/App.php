<?php

namespace Anano;

use Anano\Config;
use Anano\Response\Response;
use Anano\Http\Session;
use ErrorException;

final class App
{
    private static $binds;
    private static $config;
    private static $profile_start;
    private static $current_route;

    private $content_type = 'text/html';

    /**
     * Initialises framework, including setting up autoloading.
     */

    public static function init()
    {
        // For benchmarking
        self::$profile_start = microtime(true);

        require_once 'Config.php';
        require_once 'Helpers.php';

        // Load start files
        require_if_exists('app/start/global.php');
        require_if_exists('app/start/'. hostname() .'.php');

        if (Config::get('app.debug'))
        {
            @error_reporting(E_ALL);
            @ini_set('display_errors', 1);
        }

        date_default_timezone_set(Config::get('app.timezone'));

        // Set default headers as defined in app config
        foreach (Config::get('app.headers', []) as $key => $val)
        {
            if ($val === null)
                header_remove($key);
            else
                header($key . ': '. $val);
        }

        self::$binds = Config::get('app.binds');

        $aliases = Config::get('aliases');
        foreach ($aliases as $key => $val)
            class_alias($val, $key);

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

                $cfg->set_model_directory(ROOT_DIR . '/app/models');
                $cfg->set_connections($connections);

                $cfg->set_default_connection( Config::get('database.default') );
            });
        }

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
        if (Config::get('app.absolute-urls', false))
            return get_scheme() . '://' . $_SERVER['HTTP_HOST'] . $root;
        return $root;
    }

    /**
     * Simple IoC factory. Look to config/app.php for bindings.
     */

    public static function make($alias, array $args=array())
    {
        $name = static::$binds[$alias];

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

    public static function bind($alias, $class=null)
    {
        if (is_array($alias))
        {
            foreach ($alias as $key => $val)
            {
                static::$binds[$key] = $val;
            }
        }
        else
        {
            static::$binds[$alias] = $class;
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
     * Pass in a function, get an object of how long it took to run in different formats
     */

    public static function benchmark($lambda, $iterations=1000)
    {
        $t1 = microtime(true);

        for ($i = 0; $i < $iterations; $i++)
            $lambda();

        $t2 = microtime(true);

        return array(
            'ms' => ($t2 - $t1) * 1000 / $iterations,
            's' => ($t2 - $t1) / $iterations,
            'total' => ($t2 - $t1)
        );
    }

    public static function current($rooted_relative=true)
    {
        $url = $_SERVER['REQUEST_URI'];

        $pos = strpos($url, '?');
        if ($pos !== false)
            $url = substr($url, 0, $pos);

        if ($rooted_relative)
            $url = substr($url, strlen(self::root()));

        return $url;
    }

    public function run()
    {
        set_error_handler(function($errno, $errstr, $errfile, $errline, array $errcontext) {
            throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
        });

        $router = self::make('Router');
        $response = $router->run();

        $this->end($response);
    }

    public function end($response = null)
    {
        if($response instanceof Response)
        {
            if (function_exists('http_response_code'))
                http_response_code($response->status());

            foreach ($response->getHeaders() as $key => $val)
            {
                if ($key == 'Content-Type')
                {
                    $this->content_type = $val;
                }

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
        try
        {
            print $response;
        }
        catch (ErrorException $e)
        {
            throw new ErrorException('Unable to convert controller response to string. ' . __FILE__ . ' line ' . __LINE__);
        }

        if ($response instanceof Response)
        {
            $response->after();
        }

        if (!$response instanceof Response || $response->getHeaders() === array())
        {
            $this->profile_output();
        }

        if (Config::get('app.session'))
            Session::end();

        exit;
    }

    private function profile_output()
    {
        if ($this->content_type == 'text/html' && Config::get('app.debug') && Config::get('app.profile'))
        {
            $t1 = self::$profile_start;
            $t2 = microtime(true);

            echo "\r\n<!-- Profiling info. You can disable this in app/config/app.php -->\r\n";
            echo '<div id="anano_profiler" style="position: fixed; bottom: 10px; right: 10px; font-size: 9px; font-family: sans-serif;">';
            echo round(($t2-$t1)*1000, 2) ,' ms &bull; ';
            echo round(memory_get_peak_usage(false) / 1024/1024, 2) ,' (', round(memory_get_peak_usage(true) / 1024/1024, 2) ,') MB &bull; ';
            echo count((new \Anano\Database\Database)->getQueryLog()) . ' queries';
            echo '</div>';
        }
    }
}
