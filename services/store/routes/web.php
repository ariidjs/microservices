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
    return $router->app->version();
});

$router->group(['prefix'=>'api/v1/store/'],function() use($router){
    $router->get('admin','StoreController@getListStoreFromAdmin');
    $router->post('auth','StoreController@auth');
    $router->get('logout/[{id}]','StoreController@logOut');
    $router->post('','StoreController@insert');
    $router->post('{id}','StoreController@updated');
    $router->get('phone/{phone}','StoreController@phoneNumberAvailable');
    $router->get('{id}','StoreController@getStore');
    $router->get('baned/{id}','StoreController@banedStore');
    $router->get('{id}/saldo/{saldo}','StoreController@updatedSaldo');
    $router->post('login/[{phone}]','StoreController@login');
    $router->get('','StoreController@getListStore');
    $router->get('activation/[{id}]','StoreController@active');
    $router->get('{id}/status/{status}','StoreController@onOffStore');

});
