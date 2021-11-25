<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceCustomer;
use App\Services\ServiceCustomer;
use App\Services\ServiceProduct;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\JWT;

class AuthCustomerController extends BaseController
{
    use ApiResponser;
    private $authServiceCustomer;
    private $serviceCustomer;
    private $serviceProduct;
    private $serviceTransaction;
    public function __construct(AuthServiceCustomer $authServiceCustomer, ServiceCustomer $serviceCustomer, ServiceProduct $serviceProduct, ServiceTransaction $serviceTransaction)
    {
        $this->authServiceCustomer = $authServiceCustomer;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceProduct = $serviceProduct;
        $this->serviceTransaction = $serviceTransaction;
    }

    public function authCustomer(Request $request)
    {

        $fcm = $request->input("fcm");
        $body = [
            "fcm" => $fcm
        ];

        return json_decode($this->successResponse($this
            ->authServiceCustomer
            ->auth($body))
            ->original, true);
    }

    public function register(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $image = $request->file('avatar');
        $address = $request->input('address');
        $fcm = $request->input('fcm');

        if ($image) {
            $avatar = time() . $image->getClientOriginalName();
            $image->move('images', $avatar);
        } else {
            $avatar = 'default.png';
        }



        $body = [
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "avatar" => $avatar,
            "address" => $address,
            "fcm" => $fcm
        ];

        // return "Hello";

        return $this->successResponse($this
            ->serviceCustomer
            ->register($body));
        return json_decode($this->successResponse($this
            ->serviceCustomer
            ->register($body))
            ->original, true);
    }


    public function checkPhone($phone)
    {
        return json_decode($this->successResponse($this
            ->authServiceCustomer
            ->checkPhone($phone))
            ->original, true);
    }


    public function login(Request $request, $phone)
    {

        $body = [
            'fcm' => $request->input('fcm')
        ];
        $response=json_decode($this->successResponse($this
            ->authServiceCustomer
            ->login($phone, $body))
            ->original, true);

        if($response["success"]){
            $payload = array(
                "id" => $response['data']['id'],
                "name" => $response['data']['name']
            );
            $jwt = JWT::encode($payload, env('APP_KEY'));
            $response['data']['jwt'] = $jwt;
            return $response;

        }
    }

    public function getListProduct()
    {
        return json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProduct())
            ->original, true);
    }

    public function getListProductStore($id)
    {

        return json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProductStore($id))
            ->original, true);
    }

    public function order(Request $request)
    {
        $data = (array)json_decode($request->getContent());

        $dataProduct = [];
        foreach ($data["data_product"] as $value) {
            array_push($dataProduct, (array)$value);
        }
        $data["data_product"] = $dataProduct;
        // return $dataProduct;
        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->orderCustomer($data))
            ->original, true);
        // return $dataproduct;
        // return var_dump($data);
    }

    public function getListCustomers()
    {
        return json_decode($this->successResponse($this
            ->serviceCustomer
            ->getLisCustomer())
            ->original, true);
    }
}
