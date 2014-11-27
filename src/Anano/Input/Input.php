<?php

namespace Anano\Input;

class Input
{
    public static function get($field, $default='')
    {
        if (isset($_REQUEST[$field]))
            return $_REQUEST[$field];
        return $default;
    }
    
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}