<?php

/**
 * Shorthand for dumping data and halting execution. Useful for debugging.
 */

function dd()
{
    foreach (func_get_args() as $data)
        var_dump($data);
    exit;
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

        $url = App::root(true) . $url;
    }
    return $url;
}

/**
 * Get the currently used scheme/protocol. No ://
 */

function get_scheme()
{
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

    return $scheme;
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

/**
 * Small helper for menus. Prints a certain class if the current URL begins
 * with the supplied string. Use: <a href="/test" class="{{ activeClass('test') }}"></a>
 *
 * @param   string  $url        Check if current URL begins with this.
 * @param   string  $classname  The string to print, usually a class.
 * @param   bool    $print_html Whether to also print `class=""`. Not useful for elements with multiple classes.
 */

function activeClass($url, $classname='active', $print_html=false)
{
    $route = \Anano\App::current() . '/';
    $url .= '/';
    if ($url[0] !== '/')
        $url = '/' . $url;
    if ($url == $route || strpos($route, $url . '/') === 0)
    {
        if ($print_html)
            return 'class="'. $classname .'"';
        return $classname;
    }
    return '';
}

/**
 * Create a random alphanumeric string. Good for password or token generation.
 * @param  int  $length       Character-length of the generated string.
 * @param  bool $punctuation  Include punctiation in string. Always URI-safe.
 */

function str_random($length, $punctuation = false)
{
    $output = '';

    if ($punctuation)
        $exp = '/[0-9A-Za-z\.\-_\~]/';
    else
        $exp = '/[0-9A-Za-z]/';

    for ($i = 0; $i < $length; $i++)
    {
        do
            $chr = chr(rand(33, 126));
        while ( ! preg_match($exp, $chr));

        $output .= $chr;
    }

    return $output;
}

/**
 * Retrieve values from an array by dotted query, e.g ['person' => ['name' => 'Kris']]
 * can be retrieved with array_get($array, 'person.name')
 * @param array  $array    The array to pluck from.
 * @param string $query    Dotted query of values to get.
 * @param mixed  $default  Value returned if specified item is not found.
 */
function array_get(array $array, $query, $default = null)
{
    $query = explode('.', $query);

    foreach ($query as $q)
    {
        if (isset($array[$q]))
            $array = $array[$q];
        else
            return $default;
    }

    return $array;
}
