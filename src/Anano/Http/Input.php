<?php

namespace Anano\Http;

abstract class Input
{
    public static function all()
    {
        return self::getData();
    }

    /**
     * Get one or more values from the current request by keys.
     * @return string for single key, array for array of keys, or mixed if default is used.
     */
    public static function get($field, $default = '')
    {
        $data = self::getData();

        if (is_array($field)) {
            $output = [];
            foreach ($field as $f) {
                if (isset($data[$f])) {
                    $output[] = $data[$f];
                }
                else {
                    $output[] = $default;
                }
            }
            return $output;
        }
        else {
            if (isset($data[$field])) {
                return $data[$field];
            } 
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
        $data = self::getData();

        if ( ! is_array($fieldlist)) {
            $fieldlist = func_get_args();
            $default = null;
        }

        foreach ($fieldlist as $f) {
            if (isset($data[$f])) {
                return $data[$f];
            }
        }

        return $default;
    }

    /**
     * Get if a key exists in the current request.
     * @return bool
     */
    public static function has($field)
    {
        $data = self::getData();
        return isset($data[$field]);
    }

    /**
     * Get the HTTP verb (GET, POST, etc) of the current request.
     * @return string
     */
    public static function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Get any format of input data as an array.
     */
    private static function getData()
    {
        static $data = null;

        // Only parse once
        if ($data === null) {
            $content_type = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : null;
            if ($pos = strpos($content_type, ';')) {
                $content_type = substr($content_type, 0, $pos);
            }

            switch ($content_type) {
                case 'multipart/form-data':
                default:
                    $data = $_REQUEST;
                    break;
                case 'application/x-www-form-urlencoded':
                    parse_str(file_get_contents('php://input'), $data);
                    break;
                case 'application/json':
                    $data = json_decode(file_get_contents('php://input'), true);
                    break;
                case 'application/xml':
                case 'text/xml':
                    $data = (array)simplexml_load_string(file_get_contents('php://input'));
                    $data = array_map('trim', $data);
                    break;
            }
        }

        return $data;
    }
}
