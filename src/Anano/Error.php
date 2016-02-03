<?php

namespace Anano;

use ErrorException;
use Anano\Response\View;

class Error
{
    private static $debug_info = [];

    /**
     * Render an error page from either a view path or route.
     *
     * The error to display is fetched from config/errors.
     * If $err contains an @ it is considered a route in the format ControllerName@MethodName. Otherwise a path to view.
     *
     * @param   int     $errno      Error number, e.g. 404
     * @return  View or mixed       If the error is a route, return value cannot be predicted.
     */

    public static function render($errno)
    {
        $err = Config::get('errors.' . $errno);

        if (strpos($err, '@') !== false)
        {
            list($class, $method) = explode('@', $err, 2);
            if (class_exists($class))
            {
                $class = new $class();
                if (method_exists($class, $method))
                {
                    return $class->$method();
                }
            }

            throw new ErrorException('Error route or file does not exist.');
        }
        else
        {
            $status = array
            (
                200 => 'OK',
                400 => 'Bad Request',
                401 => 'Unauthorized',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'Method Not Allowed',
            );

            $response = new View($err);

            if (Config::get('app.debug'))
                $response->attach( (new self)->getDebugInfo(), 'before' );

            $response->setHeaders(array("HTTP/1.0 $errno {$status[$errno]}" => array(null, $errno)));
            return $response;
        }
    }

    public static function addDebugInfo($name, $data)
    {
        if (Config::get('app.debug'))
        {
            self::$debug_info[$name] = print_r($data, true);
        }
    }

    private function getDebugInfo()
    {
        if ( empty(self::$debug_info) )
            return '';

        if ( View::exists('errors/debug') )
            $view = new View('errors/debug', ['data' => self::$debug_info]);
        else
            $view = print_r(self::$debug_info, true);

        return $view;
    }
}
