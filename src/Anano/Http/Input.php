<?php

namespace Anano\Http;

abstract class Input
{
    /**
     * Get one or more values from the current request by keys.
     * @return string for single key, array for array of keys, or mixed if default is used.
     */
    public static function get($field, $default = '')
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

    /**
     * Get a single value from the current request, but with a prioritized list of possible keys/aliases.
     * Useful for allowing several key names or shorthands, e.g. $search = Input::prefer('search', 's');
     * If you want to include a default value, fields must be passed as an array. Otherwise as argument list.
     * @return string
     */
    public static function prefer($fieldlist, $default = '')
    {
        if ( ! is_array($fieldlist))
        {
            $fieldlist = func_get_args();
            $default = null;
        }

        foreach ($fieldlist as $f)
        {
            if (isset($_REQUEST[$f]))
                return $_REQUEST[$f];
        }

        return $default;
    }

    /**
     * Get if a key exists in the current request.
     * @return bool
     */
    public static function has($field)
    {
        return isset($_REQUEST[$field]);
    }

    /**
     * Get the HTTP verb (GET, POST, etc) of the current request.
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
}
