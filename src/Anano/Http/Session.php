<?php

namespace Anano\Http;

abstract class Session
{
    private static $flash_data = array();
    
    
    public static function start($name)
    {
        // Set cookie to httponly
        session_set_cookie_params(1440,
            ini_get('session.cookie_path'),
            ini_get('session.cookie_domain'),
            isset($_SERVER['HTTPS']),
            true);
        
        session_name($name);
        session_start();
        if (!self::get('csrf_token'))
            $_SESSION['csrf_token'] = sha1(microtime(true) * (rand() / getrandmax()));
    }
    
    public static function get($field, $default='')
    {
        if (isset($_SESSION[$field]))
            return $_SESSION[$field];
        else if (isset($_SESSION['flash'][$field]))
            return $_SESSION['flash'][$field];
        return $default;
    }
    
    public static function has($field)
    {
        if (isset($_SESSION['flash'][$field]) || isset($_SESSION[$field]))
            return true;
        return false;
    }
    
    public static function put($field, $value=null)
    {
        if (headers_sent())
            throw new ErrorException("Cannot write to session, headers sent.");
        
        if (is_array($field) && $value === null)
        {
            foreach ($field as $key => $val)
            {
                $_SESSION[$key] = $val;
            }
        }
        else
        {
            $_SESSION[$field] = $value;
        }
    }
    
    public static function forget()
    {
        $_SESSION = array();
        return session_destroy();
    }
    
    public static function refresh()
    {
        return session_regenerate_id(true);
    }
    
    
    public static function flash($name, $message=null)
    {
        if (is_array($name) && $message === null)
        {
            foreach ($name as $key => $val)
            {
                self::$flash_data[$key] = $val;
            }
        }
        else
        {
            self::$flash_data[$name] = $message;
        }
    }
    
    public static function end()
    {
        $_SESSION['flash'] = array();
        
        if (!empty(self::$flash_data))
        {
            $_SESSION['flash'] = self::$flash_data;
        }
    }
}