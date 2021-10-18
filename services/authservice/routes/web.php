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

$router->get('/', function () use ($router) {
    return 'auth serpis';
});

$router->group(['prefix'=>'api/v1/auth/store'],function() use($router){
    $router->post('','StoreController@authStore');
    $router->get('/phone/{phone}','StoreController@checkPhone');
    $router->post('/login/{phone}','StoreController@login');
});


$router->group(['prefix'=>'api/v1/auth/driver'],function() use($router){
    $router->post('','DriverController@authDriver');
    $router->get('/phone/{phone}','DriverController@checkPhone');
    $router->post('/login/{phone}','DriverController@login');
});

$router->group(['prefix'=>'api/v1/auth/customer'],function() use($router){
    $router->post('','CustomerController@authCustomer');
    $router->get('/phone/{phone}','CustomerController@checkPhone');
    $router->post('/login/{phone}','CustomerController@login');
});

$router->group(['prefix'=>'api/v1/auth/admin'],function() use($router){
    $router->post('','AdminController@login');
    // $router->get('/phone/{phone}','CustomerController@checkPhone');
    // $router->post('/login/{phone}','CustomerController@login');
});
