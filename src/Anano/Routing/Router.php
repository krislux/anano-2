<?php

namespace Anano\Routing;

use ReflectionMethod;
use Anano\Error;
use Anano\Controller;
use Anano\Routing\Route;

class Router
{
    private static $routes = array();
    
    public static function add(Route $route)
    {
        self::$routes[] = $route;
    }
    
    public function run()
    {
        Route::get('/test/penis/{id?}', 'Main@test');
        
        $verb = $_SERVER['REQUEST_METHOD'];
        
        $current = substr($_SERVER['REQUEST_URI'], strlen( dirname($_SERVER['SCRIPT_NAME']) ) +1 );
        $current = explode('/', $current);
        $current_length = count($current);
        
        foreach (self::$routes as $route)
        {
            if ( ! in_array($verb, $route->getVerbs()) )
                continue;
            
            $parts = $route->getUriArray();
            $required_parts = $route->getRequiredPartsCount();
            
            if ($required_parts > $current_length)
                continue;
            
            for ($i = 1; $i < $required_parts; $i++)
            {
                if ($parts[$i] != $current[$i])
                    continue;
            }
            
            var_dump($route);
            $args = $this->parseArgs($current, $parts);
            return $this->runAction($route->getAction(), $args);
        }
        
        return Error::render(404);
    }
    
    protected function runAction($action, array $args=array())
    {
        if ( is_callable($action) )
            return $action;
        
        if ( is_string($action) )
        {
            $parts = explode('@', $action);
            
            if ( ! class_exists($parts[0]) )
                return false;
            
            $controller = new $parts[0];
            if ( ! $controller instanceof Controller )
                return false;
            
            if (isset($parts[1]))
            {
                // Specified method
                $method = $parts[1];
            }
            else
            {
                // Dynamic method
            }
        }

        if ( ! method_exists($controller, $method) )
        {
            if ( method_exists($controller, 'missing') )
                return $controller->missing();
            return false;
        }
        
        $response = null;
        
        $rem = new ReflectionMethod($controller, $method);
        if (method_exists($controller, $method))
        {
            $rem = new ReflectionMethod($controller, $method);
            if ($rem->isPublic() && count($args) >= $rem->getNumberOfRequiredParameters())
            {
                $filter = $controller->_filters_run($method);
                if ($filter === true)
                    $response = $rem->invokeArgs($controller, $args);
                elseif ($filter === false)
                    $response = Error::render(403);
                else
                    $response = $filter;
            }
        }
        else if (method_exists($controller, '__call'))
        {
            $response = $controller->$method($args);
        }
        
        if ($response)
            return $response;
        return Error::render(404);
    }
    
    protected function parseArgs(array $uriparts, array $routeparts)
    {
        $args = array();
        $max = count($routeparts);
        
        for ($i = 0; $i < $max; $i++)
        {
            if ($routeparts[$i][0] == '{')
            {
                $args[] = isset($uriparts[$i]) ? $uriparts[$i] : null;
            }
        }
        return $args;
    }
    
    
    
    public function ____run()
    {
        $response = null;
        
        if (is_subclass_of($this->route['class'], 'Controller'))
        {
            $class = new $this->route['class'];
            $method = $this->route['method'];
            $verb = strtolower($_SERVER['REQUEST_METHOD']);
            $restmethod = $verb . ucfirst($method);
            $args = $this->route['args'];
            
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
        }
        
        if (!$response)
            $response = Error::render(404);
        
        return $response;
    }
}