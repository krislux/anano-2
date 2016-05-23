<?php

namespace Anano\Http;

abstract class Input
{
    public static function get($field, $default='')
    {
        if (is_array($field))
        {
            $output = [];
            foreach ($field as $f)
            {
                if (isset($_REQUEST[$f]))
                    $output[] = $_REQUEST[$f];
                else
                    $output[] = $default;
            }
            return $output;
        }
        else
        {
            if (isset($_REQUEST[$field]))
                return $_REQUEST[$field];
        }
        return $default;
    }

    public static function has($field)
    {
        return isset($_REQUEST[$field]);
    }

    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
