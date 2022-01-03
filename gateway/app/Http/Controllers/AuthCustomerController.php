<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceCustomer;
use App\Services\ServiceCustomer;
use App\Services\ServiceProduct;
use App\Services\ServiceStore;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;

class AuthCustomerController extends BaseController
{
    use ApiResponser;
    private $authServiceCustomer;
    private $serviceCustomer;
    private $serviceProduct;
    private $serviceTransaction;
    private $serviceStore;
    private $TIME_EXPIRE = 3;
    private $JWT_EXPIRED = false;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    public function __construct(AuthServiceCustomer $authServiceCustomer, ServiceCustomer $serviceCustomer, ServiceProduct $serviceProduct, ServiceTransaction $serviceTransaction, ServiceStore $serviceStore)
    {
        $this->authServiceCustomer = $authServiceCustomer;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceProduct = $serviceProduct;
        $this->serviceTransaction = $serviceTransaction;
        $this->serviceStore = $serviceStore;
    }

    private function auth($fcm)
    {
        $body = [
            "fcm" => $fcm
        ];

        return json_decode($this->successResponse($this
            ->authServiceCustomer
            ->auth($body))
            ->original, true);
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

    public function validationJWT($request)
    {
        $jwt = request()->header('Authorization');
        $jwt = str_replace('Bearer ', '', $jwt);
        $fcm = $request->header('fcm');
        try {
            $data = JWT::decode($jwt, $this->key, array('HS256'));
            return [
                "expired" => $this->JWT_EXPIRED,
                "jwt" => $jwt,
                "data" => (array)$data
            ];
        } catch (ExpiredException $ex) {
            $data = $this->auth($fcm);
            $payload = array(
                "id" => $data['data']['id'],
                "email" => $data['data']['email'],
                "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            return [
                "expired" => !$this->JWT_EXPIRED,
                "data" => $payload,
                "jwt" => $jwt
            ];
        }
    }

    public function login(Request $request, $phone)
    {

        $response = json_decode($this->successResponse($this
            ->authServiceCustomer
            ->checkPhone($phone))
            ->original, true);


        if($response['success']) 
        {
            $body = [
                'fcm' => $request->input('fcm')
            ];
            $login = json_decode($this->successResponse($this
                ->authServiceCustomer
                ->login($phone, $body))
                ->original, true);
    
            if($login["success"]){
                $payload = array(
                    "id" => $login['data']['id'],
                    "name" => $login['data']['name'],
                    "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
                );
                $jwt = JWT::encode($payload, $this->key);
                $login['data']['jwt'] = $jwt;
                return $login;
    
            }
        }
        
    }

    public function getListProduct(Request $request)
    {
        $this->validationJWT($request);
        return json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProduct())
            ->original, true);
    }

    public function getListProductStore(Request $request,$id)
    {
        $this->validationJWT($request);
        return json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProductStore($id))
            ->original, true);
    }

    public function order(Request $request)
    {
        $this->validationJWT($request);
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
    public function getListStore(Request $request)
    {
        $this->validationJWT($request);
        return json_decode($this->successResponse($this
        ->serviceStore
        ->getListStore())
        ->original, true);
    }
}
