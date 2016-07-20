<?php

namespace Anano\Http;

use Exception;
use Config;

/**
 * Simple cURL-wrapper.
 */

class Curl
{
    private $curl;

    public function __construct($user_agent = '')
    {
        if (!extension_loaded('curl'))
            throw new Exception('cURL not loaded.');

        $this->curl = curl_init();
        $this->setopt(CURLOPT_USERAGENT, $user_agent);
        $this->setopt(CURLOPT_RETURNTRANSFER, true);
        $this->setopt(CURLINFO_HEADER_OUT, true);

        $pemfile = Config::get('app.cainfo', ROOT_DIR . '/cacert.pem');
        if (file_exists($pemfile))
            $this->setopt(CURLOPT_CAINFO, $pemfile);
    }

    public function __destruct()
    {
        $this->close();
    }

    public function get($url, $data=array())
    {
        if (!empty($data))
            $url .= '?' . http_build_query($data);
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_HTTPGET, TRUE);

        return curl_exec($this->curl);
    }

    public function post($url, $data=array())
    {
        $this->setopt(CURLOPT_URL, $url);
        $this->setopt(CURLOPT_POST, TRUE);
        $this->setopt(CURLOPT_POSTFIELDS, $data);

        return curl_exec($this->curl);
    }

    public function &setopt($key, $value)
    {
        curl_setopt($this->curl, $key, $value);
        return $this;
    }

    public function &basicAuth($username, $password) {
        $this->setopt(CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setopt(CURLOPT_USERPWD, $username . ':' . $password);
        return $this;
    }

    public function info($opt = 0)
    {
        if ($opt)
            return curl_getinfo($this->curl, $opt);
        return curl_getinfo($this->curl);
    }

    public function error()
    {
        return curl_error($this->curl);
    }

    public function getInstance()
    {
        return $this->curl;
    }

    public function close()
    {
        curl_close($this->curl);
    }
}
