<?php

/**
 * Here you can define how to display errors.
 *
 * Standard HTTP error values such as 404 (only some are supported) can be mapped to either
 * routes or view paths. To render views is straightforward, just use the path like anywhere
 * else, e.g. 404 => 'errors/404'.
 * For routes, use the format ControllerName@MethodName. E.g. 404 => 'FrontController@index'
 */

return array(
    
    /**
     * Forbidden, for unauthorized request, i.e. failed a filter.
     */
    
    403 => 'errors/403',
    
    
    /**
     * Not found, fired on missing class, method or arguments.
     */
    
    404 => 'errors/404',
    
);