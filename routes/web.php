<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$router->group(['prefix'=>'/api/v1'], function() use($router){
    $router->group(['middleware' => 'auth'], function () use ($router){
        $router->get('/', ['uses'=>'AuthController@index', 'as' => 'home']);
    });

    $router->post('/register', ['uses'=>'AuthController@register', 'as' => 'register']);
    $router->post('/login', ['uses'=>'AuthController@login', 'as' => 'login']);
    $router->post('/logout', ['uses'=>'AuthController@logout', 'as' => 'logout']);
});
