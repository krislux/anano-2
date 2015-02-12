<?php

namespace Anano\Http;

abstract class Cookie
{
    public static function get($field, $default='')
    {
        if (isset($_COOKIE[$field]))
            return $_COOKIE[$field];
        return $default;
    }
    
    public static function put($field, $value, $expire=null, $secure=false, $http_only=true)
    {
        if (headers_sent())
            throw new ErrorException("Cannot write to session, headers sent.");
        
        if (!$expire)
            $expire = time() + 604800; // 7 days default.
        else if (is_string($expire))
            $expire = strtotime($expire);
        else if ($expire < time())
            $expire = time() + $expire;
        
        setcookie($field, $value, $expire, '/', $_SERVER['HTTP_HOST'], $secure, $http_only);
    }
    
    public static function delete($field)
    {
        static::put($field, '');
        setcookie($field, '', time() - 3600, '/', $_SERVER['HTTP_HOST']);
        unset($_COOKIE[$field]);
    }
}