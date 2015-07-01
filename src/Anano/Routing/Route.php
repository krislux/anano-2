<?php

namespace Anano\Routing;

use Anano\Routing\Router;

class Route
{
    protected $uri;
    
    protected $verbs;
    
    protected $action;
    
    protected $wheres;
    
    public function __construct($verbs, $uri, $action)
    {
        $this->verbs = (array)$verbs;
        $this->uri = trim($uri, '/');
        $this->action = $action;
    }
    
    public function getVerbs()
    {
        return $this->verbs;
    }
    
    public function getUri()
    {
        return $this->uri;
    }
    
    public function getUriArray()
    {
        return explode('/', $this->uri);
    }
    
    public function getRequiredPartsCount()
    {
        $count = 0;
        foreach ($this->getUriArray() as $uri)
        {
            if (substr($uri, strlen($uri) - 2) !== '?}')
                $count++;
        }
        return $count;
    }
    
    public function getAction()
    {
        return $this->action;
    }
    
    /**
     * Creators
     */
    
    public static function auto($uri, $controller)
    {
        $verbs = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
        Router::add( new self($verbs, $uri, $controller) );
    }
    
    public static function any($uri, $action)
    {
        $verbs = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE');
        Router::add( new self($verbs, $uri, $action) );
    }
    
    public static function get($uri, $action)
    {
        Router::add( new self('GET', $uri, $action) );
    }
    
    public static function post($uri, $action)
    {
        Router::add( new self('POST', $uri, $action) );
    }
    
    public static function put($uri, $action)
    {
        Router::add( new self('PUT', $uri, $action) );
    }
    
    public static function patch($uri, $action)
    {
        Router::add( new self('PATCH', $uri, $action) );
    }
    
    public static function delete($uri, $action)
    {
        Router::add( new self('DELETE', $uri, $action) );
    }
}