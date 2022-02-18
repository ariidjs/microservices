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

$router->group(['prefix' => 'api/v1/driver/'], function () use ($router) {
    $router->post('updaterating', 'DriverController@updateRatingDriver');
    $router->get('count', 'DriverController@countDriver');
    $router->get('admin', 'DriverController@getListDriversFromAdmin');
    $router->post('auth', 'DriverController@auth');
    $router->get('logout/{id}', 'DriverController@logOut');
    $router->post('', 'DriverController@insert');
    $router->post('{id}', 'DriverController@updated');
    $router->get('phone/{phone}', 'DriverController@phoneNumberAvailable');
    $router->get('{id}', 'DriverController@getDrivers');
    $router->get('baned/{id}', 'DriverController@banedDriver');
    $router->get('{id}/saldo/{saldo}/{type}', 'DriverController@updatedSaldo');
    $router->get('{id}/tax/{saldo}', 'DriverController@taxSaldo');
    $router->post('login/[{phone}]', 'DriverController@login');
    $router->get('', 'DriverController@getListDriver');
    $router->get('{id_driver}/activation/{status}', 'DriverController@ChangeStatusUser');
    $router->get('{id}/status/{status}', 'DriverController@driverWork');
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});
