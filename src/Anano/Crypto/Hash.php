<?php

/**
 * Password hasher and verifier.
 *
 * For PHP 5.5 and above it will use the excellent password_hash function with strong salt.
 * For versions below, it will fall back to a custom crypt() call that is only forwards
 * compatible - that is, you can verify old-version passwords with new version, but not vice versa.
 *
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
            $salt = '$2a$10$' . substr( md5(microtime(true)) , 0, 22);
            return crypt($password, $salt);
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
            $salt = '$2a$10$' . substr($hash, 7, 22);
            return $hash === crypt($password, $salt);
        }
    }
}
