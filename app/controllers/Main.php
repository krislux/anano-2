<?php

use Anano\Http\Curl;

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
        $this->filter('csrf', ['on' => 'POST']);
        $this->filter('auth', ['except' => ['getLogin', 'postLogin']]);
    }

    function getLogin()
    {
        return new View('login');
    }

    function postLogin()
    {
        // Replace with something proper.
        if (
            strtolower(Input::get('username')) == 'admin'
         && Input::get('password') == '1234')
        {
            Session::put('userid', 1);
            return Response::redirect('/list');
        }

        return new View('login', ['invalid' => true]);
    }

    function index()
    {
        return new View('home', array('title' => 'Home'));
    }

    function getList()
    {
        $players = json_decode( $this->server('getData') );

        return new View('list', array(
            'players' => $players,
            'title' => 'Liste'
        ));
    }

    function getLogout()
    {
        Session::forget('userid');
        return Response::redirect('/login');
    }

    function server($method, $data = array())
    {
        $base_url = 'http://localhost:8080/';

        $curl = new Curl;
        $buffer = $curl->get($base_url . $method, $data);

        if ( ! $buffer)
            die('Unable to reach server. Should use cached copy.');

        return Response::raw($buffer, 'application/json');
    }

}
