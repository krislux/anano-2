<?php

namespace Anano\Response;

class Response
{
    protected $value;
    protected $headers = array();
    
    public function __construct($value, array $headers=null)
    {
        if ($headers)
            $this->setHeaders($headers);
        $this->value = $value;
    }
    
    public function __toString()
    {
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
    
    
    public function setHeaders(array $headers)
    {
        foreach ($headers as $key => $val)
            $this->headers[$key] = $val;
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
     * Types
     */
    
    public static function html($data)
    {
        return new self($data);
    }
    
    public static function json(array $data)
    {
        $headers = array('Content-Type' => 'applicaton/json');
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
        $headers = array('Location' => array( url($location), $code));
        return new self('', $headers);
    }
}