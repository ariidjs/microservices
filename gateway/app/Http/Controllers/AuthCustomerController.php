<?php

namespace App\Http\Controllers;

use App\Http\Controllers\RSA as ControllersRSA;
use App\Services\AuthServiceCustomer;
use App\Services\ServiceCustomer;
use App\Services\ServiceProduct;
use App\Services\ServicePromo;
use App\Services\ServiceStore;
use App\Services\ServiceRating;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
use phpseclib3\Crypt\RSA;

class AuthCustomerController extends BaseController
{
    use ApiResponser;
    private $authServiceCustomer;
    private $serviceCustomer;
    private $serviceProduct;
    private $serviceTransaction;
    private $serviceStore;
    private $servicePromo;
    private $serviceRating;
    private $TIME_EXPIRE = 3;
    private $JWT_EXPIRED = false;
    private $RSAencrypt;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    public function __construct(AuthServiceCustomer $authServiceCustomer, ServiceCustomer $serviceCustomer, ServiceProduct $serviceProduct, ServiceTransaction $serviceTransaction, ServiceStore $serviceStore,ServicePromo $servicePromo,ServiceRating $serviceRating)
    {
        $this->authServiceCustomer = $authServiceCustomer;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceProduct = $serviceProduct;
        $this->serviceTransaction = $serviceTransaction;
        $this->serviceStore = $serviceStore;
        $this->servicePromo = $servicePromo;
        $this->serviceRating = $serviceRating;
    }



    private function auth($fcm,$id)
    {
        $body = [
            "fcm" => $fcm,
            "id" => $id,
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

        $data = (array)JWT::decode($jwt, $this->key, array('HS256'));
        if (time() >= strtotime($data["time"])) {
            $data = $this->auth($fcm,$data["id"]);
            $payload = array(
                "id" => $data['data']['id'],
                "email" => $data['data']['email'],
                "time" => date('d-m-Y H:i', strtotime("+3 min"))
            );
            $jwt = JWT::encode($payload, $this->key);
            return [
                "expired" => !$this->JWT_EXPIRED,
                "data" => $payload,
                "jwt" => $jwt
            ];
        }else{
            return [
                "expired" => $this->JWT_EXPIRED,
                "jwt" => $jwt,
                "data" => $data
            ];
        }
    }

    private function encRSA($M){
        $data[0] =1;
        for($i=0;$i<=35;$i++){
            $rest[$i]=pow($M,1)%119;
            if($data[$i]>119){
                $data[$i+1]=$data[$i]*$rest[$i]%119;
            }else{
                $data[$i+1]=$data[$i]*$rest[$i];
            }
        }
        $get=$data[35]%119;
        return $get;
    }

    private function decRSA($E){

        $data[0] =1;
        for($i=0;$i<=11;$i++){
            $rest[$i]=pow($E,1)%119;
            if($data[$i]>119){
                $data[$i+1]=$data[$i]*$rest[$i]%119;
            }else{
                $data[$i+1]=$data[$i]*$rest[$i];
            }
        }
        $get=$data[11]%119;
        return $get;
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
                    "time" => date('d-m-Y H:i', strtotime("+3 min"))
                );
                $jwt = JWT::encode($payload, $this->key);
                $login['data']['jwt'] = $jwt;
                return $login;

            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'Failed',
            ], 401);
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

    public function getDetailOrder(Request $request){
        $validation = $this->validationJWT($request);

        //
      return json_decode($this->successResponse($this
            ->serviceTransaction
            ->getDetailCusotmerTransaksi($validation["data"]["id"]))
            ->original,true);




    }

    public function getListTransactionCustomer(Request $request){
        $validation =$this->validationJWT($request);

        $response = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getListTransactionCustomer($validation["data"]["id"]))
        ->original, true);

        $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($validation["data"]["id"]))
            ->original, true);

       $data = collect($response["data"])->map(function($item,$key) use($customer){
            $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getStore($item["id_store"]))
            ->original, true);
            $item["customer_name"] =$customer["data"]["name"];
            $item["store_name"]= $store["data"]["store_name"];
            return $item;
        });

        // $response["data"]["customer_name"]=$customer["data"]["name"];

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $data,
            'customer'=>$customer["data"]
        ], 200);
    }
    public function cancelTransaction(Request $request,$id){
        $this->validationJWT($request);

        return json_decode($this->successResponse($this
        ->serviceTransaction
        ->cancelFromCustomer($id))
        ->original, true);
    }

    public function getListPromoCustomer(Request $request){
        $validation = $this->validationJWT($request);

        return json_decode($this->successResponse($this
            ->servicePromo
            ->getListPromoCustomer($validation["data"]["id"]))
            ->original,true);
    }

    public function updatePhotoProfile(Request $request){
        $validation = $this->validationJWT($request);
        $image = $request->file('image');

        if ($image) {
            $pathimage = time() . $image->getClientOriginalName();
        }

        $body = [
            'image' => $pathimage,
        ];

        $response = json_decode($this->successResponse($this
            ->serviceCustomer
            ->updateProfile($validation["data"]["id"],$body))
            ->original, true);

        if($response["success"]){
            if ($image) {
                $image->move('images', $pathimage);
            }
            $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($validation["data"]["id"]))
            ->original, true);

            return response()->json([
                'success'=>true,
                'message'=>'success',
                'image'=>$customer["data"]["image"]
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'failed',
            ],401);
        }
    }

    public function updateCustomer(Request $request){
        $validation = $this->validationJWT($request);

        $name = $request->input('name');
        $email = $request->input('email');
        $address = $request->input('address');

        $data = [
            "name"=>$name,
            "email"=>$email,
            "address"=>$address,
        ];

        return json_decode($this->successResponse($this
        ->serviceCustomer
        ->updateCustomer($validation["data"]["id"],$data))
        ->original,true);



    }

    public function updateRatingDriver(Request $request){

        $validation = $this->validationJWT($request);
        $id_driver = $request->input("id_driver");
        $id_customer = $validation["data"]["id"];
        $rating = $request->input("rating");

        $data = [
            "id_driver" => $id_driver,
            "rating" => $rating,
            "id_customer" => $id_customer
        ];

        return json_decode($this->successResponse($this
                ->serviceRating
                ->updateRating($data))
                ->original, true);
    }
}
