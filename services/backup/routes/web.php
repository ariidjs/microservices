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

$router->group(['prefix'=>'api/v1/admins/'],function() use($router){
    $router->get('admin','AdminsController@getListStoreFromAdmin');
    $router->post('auth','AdminsController@auth');
    $router->get('logout/[{id}]','AdminsController@logOut');
    $router->post('','AdminsController@insert');
    $router->post('{id}','AdminsController@updated');
    $router->get('phone/{phone}','AdminsController@phoneNumberAvailable');
    $router->get('[{id}]','AdminsController@getStore');
    $router->get('baned/{id}','AdminsController@banedStore');
    $router->get('{id}/saldo/{saldo}','AdminsController@updatedSaldo');
    $router->post('login/[{phone}]','AdminsController@login');
    $router->get('','AdminsController@getListStore');
    $router->get('activation/[{id}]','AdminsController@active');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
