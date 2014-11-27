<?php

namespace Anano\Input;

abstract class Session
{
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
        return $default;
    }
    
    public static function put($field, $value)
    {
        if (headers_sent())
            throw new ErrorException("Cannot write to session, headers sent.");
        $_SESSION[$field] = $value;
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
}