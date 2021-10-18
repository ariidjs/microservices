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


$router->group(['prefix'=>'api/v1/transaksi'],function() use($router){
    $router->post('','TransactionController@insertCustomer');
    $router->put('[{id}]','TransactionController@updateStatus');
    $router->post('/driver/{id}','TransactionController@statusFromDriver');
    $router->get('/done/{id}','TransactionController@transactionFinish');
    $router->get('{id}/kode/{kode}','TransactionController@validationCodeFromDriver');
    $router->post('/{id}','TransactionController@statusFromStore');
    // $router->post('update[/{id}]','TransactionController@update');
    // $router->delete('[{id}]','TransactionController@delete');
});

$router->get('/', function () use ($router) {
    return $router->app->version();
});
