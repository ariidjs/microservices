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

$router->group(['prefix' => '/api/v1/products'], function () use ($router) {
    $router->get('list', 'ProductController@getListProduct');
    $router->get('store/{id}', 'ProductController@getProductStore');
    $router->get('stores/{id}', 'ProductController@getProductStoreFromAdmin');
    $router->post('', 'ProductController@insert');
    $router->get('[{id}]', 'ProductController@getProduct');
    $router->post('update/{id}', 'ProductController@update');
    $router->get('{id}/{status}', 'ProductController@delete');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
