<?php

namespace Anano;

class Router
{
    private $route;

    public function __construct()
    {
        $args_str = substr($_SERVER['REQUEST_URI'], strlen( dirname($_SERVER['SCRIPT_NAME']) ) );
        if ($args_str === false) $args_str = $_SERVER['REQUEST_URI'];
        $args_str = trim($args_str, '/');

        // Remove query string
        $pos = strpos($args_str, '?');
        if ($pos !== false)
            $args_str = substr($args_str, 0, $pos);

        $raw_args = array_pad( explode('/', $args_str) , 2, '');

        // Convert underscores and dashes to camelCase
        $args = array_map(function($arg) {
            $parts = preg_split('/[\-_]/', $arg);
            $parts_num = count($parts);
            for ($i = 1; $i < $parts_num; $i++)
                $parts[$i] = ucfirst($parts[$i]);
            return implode('', $parts);
        }, $raw_args);

        if ($args[0])
            $args[0] = ucfirst($args[0]);

        require_if_exists("app/controllers/{$args[0]}Controller.php");

        // If class doesn't exist, assume default controller and move class to method
        if ( ! class_exists($args[0] . 'Controller', false))
        {
            $num_args = count($args);
            for ($i = $num_args; $i > 0; $i--)
            {
                $args[$i] = $args[$i-1];

                if ($args[$i] === '')
                    unset($args[$i]);
            }

            $args[0] = '/';
        }
        else
        {
            $raw_args = array_slice($raw_args, 1);
        }

        $routes = Config::get('routes');

        // Translate aliases
        foreach ($routes as $key => $val)
        {
            if ($args[0] == $key || $args[0] . '/' == $key)
                $args[0] = $val;
        }
        // Set default controller to 'index'
        if (empty($args[1]))
            $args[1] = 'index';
        $this->route = array(
            'class' => $args[0] . 'Controller',
            'method' => $args[1],
            'args' => array_slice($raw_args, 1)
        );
    }

    public function run()
    {
        $response = false;

        if (is_subclass_of($this->route['class'], 'Controller'))
        {
            $class = new $this->route['class'];
            $method = $this->route['method'];
            $verb = strtolower($_SERVER['REQUEST_METHOD']);
            $restmethod = $verb . ucfirst($method);
            $args = $this->route['args'];
            $method[0] = strtolower($method[0]);

            // Prioritise REST methods, i.e. getIndex, postSubmit, etc. Fall back to standard if not found.
            if (method_exists($class, $restmethod))
                $method = $restmethod;

            if (method_exists($class, $method))
            {
                $rem = new \ReflectionMethod($class, $method);

                if ($rem->isPublic() && count($args) >= $rem->getNumberOfRequiredParameters())
                {
                    $filter = $class->_filters_run($method);
                    if ($filter === true)
                        $response = $rem->invokeArgs($class, $args);
                    elseif ($filter === false)
                        $response = Error::render(403);
                    else
                        $response = $filter;
                }
            }
            else if (method_exists($class, '__call'))
            {
                $response = $class->$method($args);
            }
        }

        if ($response === false)
        {
            Error::addDebugInfo('Route', $this->route);
            Error::addDebugInfo('URL', $_SERVER['REQUEST_URI']);
            $response = Error::render(404);
        }

        return $response;
    }
}
