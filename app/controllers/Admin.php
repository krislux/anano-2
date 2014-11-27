<?php

class Admin extends Controller
{
    function __construct()
    {
        $this->filter('csrf', array('on' => 'POST'));
        $this->filter('auth', array('except' => array('index', 'login')));
    }
    
    function index()
    {
        return 'Admin';
    }
    
    function login()
    {
        Session::put('userid', 1);
        return Response::redirect('admin/restricted');
    }
    
    function restricted()
    {
        return 'If you are seeing this page, you are logged in.';
    }
}