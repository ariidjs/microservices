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

$router->group(['prefix' => '/api/v1/promo'], function () use ($router) {
    $router->post('', 'PromoController@insert');
    $router->post('{id}', 'PromoController@update');
    $router->get('', 'PromoController@getData');
    $router->get('{id}', 'PromoController@getPromo');
    $router->get('status/{id}', 'PromoController@updateStatus');
    $router->get('customer/{id}', 'PromoController@getPromoCustomer');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
