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

$router->group(['prefix'=>'api/v1/saldo/'],function() use($router){
   $router->post('','SaldoController@insert');
    $router->get('{id}','SaldoController@getRiwayatSaldo');
    $router->get('store/[{id}]','ProductController@getProductStore');
    $router->post('list','ProductController@getListProduct');
    $router->post('update[/{id}]','ProductController@update');
    $router->delete('[{id}]','ProductController@delete');
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});
