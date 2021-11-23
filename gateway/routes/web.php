<?php

/** @var \Laravel\Lumen\Routing\Router $router */

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Firebase\JWT\JWT;
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

$router->group(['prefix' => 'api/v1/store'], function () use ($router) {
    $router->get('historysaldo', 'AuthStoreController@getHistoryWithDrawOrDeposit');
    $router->post('/products', 'AuthStoreController@inserProduct');
    $router->get('listTransaction', 'AuthStoreController@getListTransaction');
    $router->post('/register', 'AuthStoreController@register');
    $router->post('/updated', 'AuthStoreController@updateStore');
    $router->post('/withdrawordeposit', 'AuthStoreController@withdrawORDeposit');
    $router->post('/deposit', 'AuthStoreController@deposit');
    $router->post('{id}', 'AuthStoreController@updateStoreFromAdmin');
    $router->post('', 'AuthStoreController@authStore');
    $router->get('/phone/{phone}', 'AuthStoreController@checkPhone');
    $router->post('/login/{phone}', 'AuthStoreController@login');
    $router->get('/products', 'AuthStoreController@getListProduct');
    $router->get('/statusopen/{status}', 'AuthStoreController@statusOpen');
    $router->post('/confirmorder/{idTransaction}', 'AuthStoreController@confirmOrder');
    $router->get('/admin', 'AuthStoreController@getListStoreFromAdmin');
    $router->get('{id}', 'AuthStoreController@getStore');
    $router->get('/{id_store}/activation/{status}', 'AuthStoreController@activation');
    $router->get('product/delete/{idProduct}', 'AuthStoreController@deleteProduct');
});

$router->group(['prefix' => 'api/v1/product'], function () use ($router) {
    $router->post('', 'AuthStoreController@inserProduct');
    $router->post('updated/{idProduct}', 'AuthStoreController@updateProduct');
    $router->get('', 'AuthCustomerController@getListProduct');
    $router->get('/store/{id}', 'AuthCustomerController@getListProductStore');
    $router->get('{idProduct}/{status}', 'AuthAdminController@statusDeleteProduct');
    // $router->post('/register','AuthStoreController@register');
    // $router->get('/phone/{phone}','AuthStoreController@checkPhone');
    // $router->post('/login/{phone}','AuthStoreController@login');
});

$router->group(['prefix' => 'api/v1/driver'], function () use ($router) {
    $router->get('', 'AuthDriverController@getDriverById');
    $router->post('', 'AuthDriverController@authDriver');
    $router->post('/register', 'AuthDriverController@register');
    $router->post('/login/{phone}', 'AuthDriverController@login');
    $router->post('/withdrawordeposit', 'AuthDriverController@withdrawORDeposit');
    $router->get('/status/{status}', 'AuthDriverController@statusDriver');
    $router->post('/confirmorder/{idTransaction}', 'AuthDriverController@confirmOrder');
    $router->get('/transaction/{idTransaction}/{code}', 'AuthDriverController@validationCode');
    $router->get('/transaction/{idTransaction}', 'AuthDriverController@finishTransaction');
    $router->post('/email', 'AuthDriverController@sendEmail');
    $router->get('/{id}/activation/{status}', 'AuthDriverController@changeStatusAktivation');
    $router->post('/{id}', 'AuthDriverController@updateDriver');
    $router->get('/current', 'AuthDriverController@getDriverTrans');
    $router->get('/history', 'AuthDriverController@getDriverHistory');
    $router->get('/saldo/history', 'AuthDriverController@getHistorySaldo');
});

$router->group(['prefix' => 'api/v1/customer'], function () use ($router) {
    $router->post('', 'AuthCustomerController@authCustomer');
    $router->post('/register', 'AuthCustomerController@register');
    $router->get('/phone/{phone}', 'AuthCustomerController@checkPhone');
    $router->get('', 'AuthCustomerController@getListCustomers');
    $router->post('/login/{phone}', 'AuthCustomerController@login');
    $router->post('/order', 'AuthCustomerController@order');
});

$router->group(['prefix' => 'api/v1/admin'], function () use ($router) {
    $router->get('driver/{id}', 'AuthDriverController@getDriver');
    $router->get('driver', 'AuthDriverController@getListDriverFromAdmin');
    $router->post('', 'AuthAdminController@register');
    $router->post('/login', 'AuthAdminController@login');
    $router->get('/promoCustomer', 'AuthAdminController@getPromo');
    $router->get('/transaction', 'AuthAdminController@getListTransaction');
    $router->get('/detailTransaction/{notrans}/{id_store}', 'AuthAdminController@getDetailTransaction');
    // $router->get('/phone/{phone}','AuthCustomerController@checkPhone');
    // $router->post('/login/{phone}','AuthCustomerController@login');
    // $router->post('/order','AuthCustomerController@order');
});


$router->get('/', function () use ($router) {
    return $router->app->version();
});
