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


$router->group(['prefix'=>'api/v1/detailTransaction'],function() use($router){
    $router->post('','DetailTransactionsController@insert');
    $router->get('{notrans}/store/{idStore}','DetailTransactionsController@getNotransaction');
    $router->get('{notrans}','DetailTransactionsController@getDetailTransaction');
//    $router->post('update[/{id}]','ProductController@update');
//    $router->delete('[{id}]','ProductController@delete');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
