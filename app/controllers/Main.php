<?php

class Main extends Controller
{
    /**
     * You can use the constructor to add filters to the entire controller class.
     * Filters must evaluate TRUE for the controller to run. You can view and add filters in config/filters.php
     * 
     * To only filter some routes, you can pass the filter function an array as the second parameter containing
     * any or all of the keys 'on', containing http verbs, or 'only' and 'except' containing method names.
     */
    
    function __construct()
    {
        $this->filter('csrf', array('on' => 'POST'));
    }
    
    /**
     * Main entrypoint to your app. Responds to all HTTP verbs.
     */
    
    function index()
    {
        return new View('home', array('title' => 'Anano'));
    }
    
    /**
     * Test REST-controller. Responds only to 'GET' HTTP verb. Can take arguments, but does not require them.
     */
    
    function getTest($arg1=null, $arg2=null)
    {
        return View::make('home');  // Same as new View
    }
    
    /**
     * Test REST-controller. Responds only to 'POST' HTTP verb. Requires an argument and returns 404 if not provided.
     */
    
    function postTest($arg1)
    {
        return Response::json( array('data' => Input::get('string')) );
    }
}