<?php

namespace Anano\Response;

use Anano\Http\Session;

class Response
{
    protected $value;
    protected $status;  // HTTP status code
    protected $headers = array();

    public function __construct($value, $headers=null, $status = 200)
    {
        // Allow skipping headers parameter and using it for status instead.
        if (is_numeric($headers) && $status === 200)
            $status = $headers;
        else if (is_array($headers))
            $this->setHeaders($headers);

        $this->value = $value;
        $this->status = $status;
    }

    public function __toString()
    {
        if (is_numeric($this->value))
            return (string)$this->value;
        return $this->value;
    }

    public function getValue()
    {
        return $this->value;
    }


    /**
     * Executed before and after printing content. Reserved for subclasses that may need them.
     */

    public function before() {}
    public function after() {}


    public function &setHeaders(array $headers)
    {
        foreach ($headers as $key => $val)
            $this->headers[$key] = $val;
        return $this;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function clearHeaders()
    {
        $this->headers = array();
    }


    /**
     * Attach data
     */

    public function &flash($name, $message = null)
    {
        Session::flash($name, $message);
        return $this;
    }

    public function &status($code = null)
    {
        if ($code !== null)
        {
            $this->status = $code;
            return $this;
        }
        return $this->status;
    }


    /**
     * Types
     */

    public static function raw($data)
    {
        return new self($data);
    }

    public static function html($data)
    {
        return new self($data);
    }

    public static function json(array $data)
    {
        $headers = array('Content-Type' => 'application/json');
        return new self(json_encode($data), $headers);
    }

    public static function text($data)
    {
        $headers = array('Content-Type' => 'text/plain');
        return new self($data, $headers);
    }

    public static function download($data, $filename)
    {
        $headers = array(
            'Content-Type' => 'application/octet-stream',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename=' . $filename,
        );
        return new self($data, $headers);
    }

    public static function redirect($location, $code=303)
    {
        // Convert international domain names to ansi versions, as redirect header doesn't support IDN.
        $location = explode('?', $location, 2);
        if (function_exists('idn_to_ascii'))
            $location[0] = idn_to_ascii($location[0]);
        $location = implode('?', $location);

        $headers = array('Location' => array( url($location), $code));
        return new self('', $headers);
    }
}
