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

$router->group(['prefix' => '/api/v1/customer'], function () use ($router) {
    $router->post('/auth', 'CustomerController@auth');
    $router->post('', 'CustomerController@insert');
    $router->post('/[{id}]', 'CustomerController@update');
    $router->post('/login/[{phone}]', 'CustomerController@login');
    $router->get('/{id}', 'CustomerController@getCustomer');
    $router->get('/activasi/[{id}]', 'CustomerController@active');
    $router->get('/phone/[{phone}]', 'CustomerController@phoneNumberAvailable');
    $router->delete('/[{id}]', 'CustomerController@delete');
    $router->get('', 'CustomerController@getListCustomer');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
