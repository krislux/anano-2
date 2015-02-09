<?php

/**
 * Shorthand for dumping data and halting execution. Useful for debugging.
 */

function dd($data)
{
    var_dump($data);
    exit;
}

/**
 * Tiny helper for method chaining new objects in earlier PHP.
 */

function with($obj)
{
    return $obj;
}

/**
 * Soft include. Ignores it if the file doesn't exist.
 */

function require_if_exists($file)
{
    $path = ROOT_DIR . "/$file";
    if (file_exists($path))
        require $path;
}

/**
 * Convert snake_case URLs into camelCase for controllers.
 */

function snake_to_camel($str)
{
    $ar = explode('_', $str);
    $max = count($ar);
    for ($i = 1; $i < $max; $i++)
        $ar[$i] = ucfirst($ar[$i]);
    return implode('', $ar);
}

/**
 * Return the current Cross-Site Request Forgery protection token.
 * Also available in template code with the shorthand @token.
 */

function token()
{
    return Session::get('csrf_token');
}

/**
 * Convert any local URL (doesn't contain a URL-scheme) into a fully qualified absolute URL
 * while accounting for subfolders.
 */

function url($url)
{
    if (strpos($url, '://') === false)
    {
        if ($url[0] !== '/')
            $url = '/' . $url;
        
        if (isset($_SERVER['REQUEST_SCHEME']))
        {
            $scheme = $_SERVER['REQUEST_SCHEME'];
        }
        else
        {
            $scheme = 'http';
            if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'])
                $scheme = 'https';
        }
        
        $url = $scheme . '://' . $_SERVER['HTTP_HOST'] . App::root() . $url;
    }
    return $url;
}

/**
 * Return the hostname or computername of the current machine, used for automatic environments.
 * Note that we always use a lowercased version. This is to avoid hard to track bugs when
 * switching between filename case sensitive Unix environments and case insensitive Windows ones.
 */

function hostname()
{
    static $hostname;
    if ($hostname === null)
        $hostname = strtolower( gethostname() ?: getenv('COMPUTERNAME') );
    return $hostname;
}