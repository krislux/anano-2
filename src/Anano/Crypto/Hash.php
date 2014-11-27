<?php

/**
 * Password hasher and verifier.
 *
 * For PHP 5.5 and above it will use the excellent blowfish encryption. For 5.4.* and below it
 * will fall back to the much worse crypt() function with random salt.
 *
 * In an attempt to make it slightly more secure, the fallback hash double-encrypts the password,
 * including both a crypt and a crypt of the SHA1 of the password. But I'm no cryptographer, I
 * honestly don't know how much difference it makes.
 *
 * Remember when porting the site that passwords generated on PHP <=5.4 will not work on >=5.5
 * and vice versa.
 */

namespace Anano\Crypto;

class Hash
{
    public static function make($password)
    {
        if (function_exists('password_hash'))
        {
            return password_hash($password, PASSWORD_BCRYPT);
        }
        else
        {
            $salt = substr( md5(microtime(true)) , 0, 16);
            return self::hash($password, $salt);
        }
    }
    
    public static function verify($password, $hash)
    {
        if (function_exists('password_hash'))
        {
            return password_verify($password, $hash);
        }
        else
        {
            $salt = substr($hash, 0, 16);
            return $hash === self::hash($password, $salt);
        }
    }
    
    private static function hash($password, $salt)
    {
        $hash = crypt($password, $salt) . crypt(sha1($password . $salt), $salt);
        return $salt.$hash;
    }
}