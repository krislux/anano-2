<?php

namespace Anano\Routing;

class Router
{
    private static $routes;
    
    public static function register($path, $s)
    {
        
    }
    
    public function run()
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