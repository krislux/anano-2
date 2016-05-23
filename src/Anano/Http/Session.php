<?php

namespace Anano\Http;

use Config;

abstract class Session
{
    private static $started = false;

    private static $flash_data = array();

    public static function start()
    {
        if (self::$started)
            return;

        // Set cookie to httponly
        session_set_cookie_params(3600,
            ini_get('session.cookie_path'),
            ini_get('session.cookie_domain'),
            isset($_SERVER['HTTPS']),
            true);

        $name = Config::get('app.session', 'SESSION');
        session_name($name);
        session_start();
        self::$started = true;

        if (!self::get('csrf_token'))
            $_SESSION['csrf_token'] = sha1(microtime(true) * (rand() / getrandmax()));

        if (Config::get('app.debug'))
            header('X-Session-Started: yes');
    }

    public static function get($field, $default='')
    {
        self::start();

        if (isset($_SESSION[$field]))
            return $_SESSION[$field];
        else if (isset($_SESSION['flash'][$field]))
            return $_SESSION['flash'][$field];
        return $default;
    }

    public static function has($field)
    {
        self::start();

        if (isset($_SESSION['flash'][$field]) || isset($_SESSION[$field]))
            return true;
        return false;
    }

    public static function put($field, $value=null)
    {
        self::start();

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
        if (self::$started)
        {
            $_SESSION = array();
            return session_destroy();
        }
    }

    public static function refresh()
    {
        if (self::$started)
        {
            return session_regenerate_id(true);
        }
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
        if (self::$started)
        {
            $_SESSION['flash'] = array();

            if (!empty(self::$flash_data))
            {
                $_SESSION['flash'] = self::$flash_data;
            }
        }
    }
}
