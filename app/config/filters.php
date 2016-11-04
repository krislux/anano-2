<?php

/**
 * Filters can be used in your controller classes to control when a page can be shown.
 * Any filter function must return TRUE for the normal route to run.
 * You can return FALSE to display a standard 403 error, or any instance of Response
 * or a string to define your own error.
 */

return array(
    
    /**
     * Cross-site request forgery protection filter.
     */
    
    'csrf' => function() {
        return Input::get('token') && Input::get('token') === Session::get('csrf_token');
    },
    
    
    /**
     * Simplest possible authorization filter. You should change this depending on your login code.
     */
    
    'auth' => function() {
        return Session::get('userid', 0) > 0;
    },
    
    
    /**
     * Basic auth filter. Pass in desired username/password as an array as the third parameter of Controller::filter()
     * or modify this filter as you'd like, e.g. hardcoding credentials here, or reading from a config file.
     */

    'auth-basic' => function($creds) {
        if ( ! isset($_SERVER['PHP_AUTH_USER']) ||
             ! isset($_SERVER['PHP_AUTH_PW']) ||
            strtolower($_SERVER['PHP_AUTH_USER']) !== $creds['username'] ||
            $_SERVER['PHP_AUTH_PW']               !== $creds['password'] )
        {
            return Response::raw()
                ->header('WWW-Authenticate', 'Basic realm="Anano Realm"')
                ->status(401);
        }
        return true;
    },
    
    
    /**
     * IP whitelist filter. For any real use, you may wish to create a custom config file for the list.
     */
    
    'ip' => function() {
        $whitelist = array('127.0.0.1', '::1');
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    },
    
    
    /**
     * If you want to test filters in a controller, this will always deny access to any affected method.
     */
    
    'test' => function() {
        return false;
    },
);