<?php

namespace App\Http\Controllers;


use App\Services\AuthServiceStore;
use App\Services\ServiceDetailTransaction;
use App\Services\ServiceProduct;
use App\Services\ServiceSaldoStore;
use App\Services\ServiceStore;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;


class AuthStoreController extends BaseController
{
    use ApiResponser;
    private $authServiceStore;
    private $serviceStore;
    private $serviceSaldo;
    private $serviceTransaction;
    private $serviceDetailTransaction;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    private $TIME_EXPIRE = 3;
    private $serviceProduct;
    private $JWT_EXPIRED = false;
    private $WITHDRAW = 2;
    private $DEPOSIT = 1;
    private $DELETE = -1;
    private $ACTIVE = 1;
    private $PENDING = 0;

    private $SUPER_ADMIN = "super_admin";
    private $ADMIN = "admin";


    public function __construct(AuthServiceStore $authServiceStore, ServiceStore $serviceStore, ServiceProduct $serviceProduct, ServiceSaldoStore $serviceSaldoStore, ServiceTransaction $serviceTransaction,ServiceDetailTransaction $serviceDetailTransaction)
    {
        $this->authServiceStore = $authServiceStore;
        $this->serviceStore = $serviceStore;
        $this->serviceProduct = $serviceProduct;
        $this->serviceSaldo = $serviceSaldoStore;
        $this->serviceTransaction = $serviceTransaction;
        $this->serviceDetailTransaction = $serviceDetailTransaction;
    }

    private function auth($fcm)
    {
        $body = [
            "fcm" => $fcm
        ];
        return json_decode($this->successResponse($this
            ->authServiceStore
            ->auth($body))
            ->original, true);
    }

    public function authStore(Request $request)
    {
        $data = $this->auth($request->input("fcm"));
        if ($data['success']) {
            $payload = array(
                "id" => $data['data']['id'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            $data['jwt'] = $jwt;
            return $data;
        }
    }

    public function getListTransaction(Request $request){
        $validation = $this->validationJWT($request);

        return json_decode($this->successResponse($this
                    ->serviceTransaction
                    ->getListTransactionStore($validation["data"]["id"]))
                    ->original, true);
    }

    public function inserProduct(Request $request)
    {
        $validation = $this->validationJWT($request);

        // return var_dump($validation);
        // return $validation["data"]["id"];


        $name_product = $request->input('name_product');
        $category = $request->input('category');
        $price = $request->input('price');
        $price_promo = $request->input('price_promo');
        $image1 = $request->file('image1');
        $image2 = $request->file('image2');
        $image3 = $request->file('image3');
        $image4 = $request->file('image4');
        $description = $request->input('description');
        $status_delete = 0;

        if ($image1) {
            $fotoProduct1 = time() . $image1->getClientOriginalName();
        } else {
            $fotoProduct1 = '';
        }

        if ($image2) {
            $fotoProduct2 = time() . $image2->getClientOriginalName();
        } else {
            $fotoProduct2 = '';
        }

        if ($image3) {
            $fotoProduct3 = time() . $image3->getClientOriginalName();
        } else {
            $fotoProduct3 = '';
        }

        if ($image4) {
            $fotoProduct4 = time() . $image4->getClientOriginalName();
        } else {
            $fotoProduct4 = '';
        }

        $body = [
            'id_store' => $validation["data"]["id"],
            'name_product' => $name_product,
            'category' => $category,
            'price' => $price,
            'price_promo' => $price_promo,
            'image1' => $fotoProduct1,
            'image2' => $fotoProduct2,
            'image3' => $fotoProduct3,
            'image4' => $fotoProduct4,
            'description' => $description,
            'status_delete' => $status_delete,
        ];


        $response = json_decode($this->successResponse($this
            ->serviceProduct
            ->insertProduct($body))
            ->original, true);

        if ($response["success"]) {
            if ($image1) {
                $image1->move('images', $fotoProduct1);
            }
            if ($image2) {
                $image2->move('images', $fotoProduct2);
            }
            if ($image3) {
                $image3->move('images', $fotoProduct3);
            }
            if ($image4) {
                $image4->move('images', $fotoProduct4);
            }



            if ($validation['expired']) {
                $response["data"]["jwt"] = $validation['jwt'];
            } else {
                $response["jwt"] = null;
            }

            return $response;
        }

    }

    public function getHistoryWithDrawOrDeposit(Request $request){
        $validation =$this->validationJWT($request);
        return json_decode($this->successResponse($this
        ->serviceSaldo
        ->getHistoryWithDrawOrDeposit($validation["data"]["id"]))
        ->original,true);

    }


    public function deleteProduct(Request $request,$idProduct){
       $this->validationJWT($request);

        return json_decode($this->successResponse($this
        ->serviceProduct
        ->changeStatusDeleteProduct($idProduct,1))
        ->original, true);
    }



    public function register(Request $request)
    {
        $owner_name = $request->input("owner_name");
        $store_name = $request->input("store_name");
        $phone = $request->input("phone");
        $email = $request->input("email");
        $fcm = $request->input("fcm");
        $description_store = $request->input("description_store");
        $nik_ktp = $request->input("nik_ktp");
        $photo_ktp = $request->file("photo_ktp");
        $photo_store = $request->file("photo_store");
        $latitude = $request->input("latitude");
        $longititude = $request->input("longititude");
        $address = $request->input("address");


        if ($photo_ktp) {
            $ktp = time() . $photo_ktp->getClientOriginalName();
        } else {
            $ktp = 'default.png';
        }

        if ($photo_store) {
            $store = time() . $photo_store->getClientOriginalName();
        } else {
            $store = 'default.png';
        }

        $body = [
            "owner_name" => $owner_name,
            "store_name" => $store_name,
            "phone" => $phone,
            "email" => $email,
            "fcm" => $fcm,
            "description_store" => $description_store,
            "nik_ktp" => $nik_ktp,
            "photo_ktp" => $ktp,
            "latitude" => $latitude,
            "longititude" => $longititude,
            "address" => $address,
            "photo_store" => $store
        ];

        $response = json_decode($this->successResponse($this
            ->serviceStore
            ->register($body))
            ->original, true);

        if ($response["success"]) {
            if($photo_ktp){
                $photo_ktp->move('images', $ktp);
            }
            if($photo_store){
                $photo_store->move('images', $store);
            }
            return $response;
        }
    }

    public function checkPhone($phone)
    {
        return json_decode($this->successResponse($this
            ->authServiceStore
            ->checkPhone($phone))
            ->original, true);
    }

    public function getListProduct(Request $request)
    {
        $validation = $this->validationJWT($request);
        return json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProductStore($validation["data"]["id"]))
            ->original, true);
    }

    public function updateProduct(Request $request, $idProduct)
    {
        // return "Hello";
        $jwt = $request->header("jwt");
        $fcm = $request->header('fcm');

        try {
            JWT::decode($jwt, $this->key, array('HS256'));
            return $this->update($request, $jwt, $this->JWT_EXPIRED, $idProduct);
        } catch (ExpiredException $ex) {
            $data = $this->auth($fcm);
            $payload = array(
                "id" => $data['data']['id_store'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            return $this->update($request, $jwt, !$this->JWT_EXPIRED, $idProduct);
        }
    }
    public function update($request, $jwt, $expired, $idProduct)
    {
        $name_product = $request->input('name_product');
        $category = $request->input('category');
        $price = $request->input('price');
        $price_promo = $request->input('price_promo');
        $image1 = $request->file('image1');
        $image2 = $request->file('image2');
        $image3 = $request->file('image3');
        $image4 = $request->file('image4');
        $description = $request->input('description');

        $store = JWT::decode($jwt, $this->key, array('HS256'));

        if ($image1) {
            $fotoProduct1 = time() . $image1->getClientOriginalName();
        } else {
            $fotoProduct1 = '';
        }

        if ($image2) {
            $fotoProduct2 = time() . $image2->getClientOriginalName();
        } else {
            $fotoProduct2 = '';
        }

        if ($image3) {
            $fotoProduct3 = time() . $image3->getClientOriginalName();
        } else {
            $fotoProduct3 = '';
        }

        if ($image4) {
            $fotoProduct4 = time() . $image4->getClientOriginalName();
        } else {
            $fotoProduct4 = '';
        }

        $body = [
            'id_store' => $store->id,
            'name_product' => $name_product,
            'category' => $category,
            'price' => $price,
            'price_promo' => $price_promo,
            'image1' => $fotoProduct1,
            'image2' => $fotoProduct2,
            'image3' => $fotoProduct3,
            'image4' => $fotoProduct4,
            'description' => $description,
            'status_delete' => 0,
        ];


        $response = json_decode($this->successResponse($this
            ->serviceProduct
            ->updatedProduct($body, $idProduct))
            ->original, true);


        if ($response["success"]) {
            if ($image1) {
                $image1->move('images', $fotoProduct1);
            }
            if ($image2) {
                $image2->move('images', $fotoProduct2);
            }
            if ($image3) {
                $image3->move('images', $fotoProduct3);
            }
            if ($image4) {
                $image4->move('images', $fotoProduct4);
            }

            if ($expired) {
                $response["jwt"] = $jwt;
            } else {
                $response["jwt"] = null;
            }

            return $response;
        }
    }

    public function login(Request $request, $phone)
    {

        $body = [
            'fcm' => $request->input('fcm')
        ];

        $response = json_decode($this->successResponse($this
            ->authServiceStore
            ->login($phone, $body))
            ->original, true);

        if ($response['success']) {
            $payload = array(
                "id" => $response['data']['id_store'],
                "owner_name" => $response['data']['owner_name'],
                "store_name" => $response['data']['store_name'],
                "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            $response['data']['jwt'] = $jwt;
            return $response;
        }
    }

    public function confirmOrder(Request $request, $idTransaction)
    {
        $this->validationJWT($request);
        $body = [
            "status" => $request->input("status")
        ];
        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->confirmStore($idTransaction, $body))
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
                "id" => $data['data']['id_store'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
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

    public function withdrawORDeposit(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_store = $validation['data']['id'];
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $type = $request->input('type');
        $image = $request->file('image');
        $namabank = $request->input('nama_bank');
        $namaAcount = $request->input('nama');
        if ($image) {
            $foto = time() . $image->getClientOriginalName();
        } else {
            $foto = '';
        }

        if($type == $this->WITHDRAW){
            $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getStore($id_store))
            ->original, true);

            if($store["data"]){
                if($store["data"]["saldo"] < $saldo){
                    return response()->json([
                        'success'=>false,
                        'message'=>'saldo anda tidak mencukupi untuk melakukan withdraw'
                    ],400);
                }
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'data store not found'
                ],400);
            }
        }

        $body = [
            'id_store' => $id_store,
            'norek' => $norek,
            'saldo' => $saldo,
            'type' => $type,
            'nama_bank' => $namabank,
            'image' => $foto,
            'nama' => $namaAcount
        ];

        $response = json_decode($this->successResponse($this
            ->serviceSaldo
            ->withdrawORDeposit($body))
            ->original, true);

        if ($response["success"]) {
            if ($image) {
                $image->move('images', $foto);
            }
            if ($validation['expired']) {
                $response["jwt"] = $validation["jwt"];
            } else {
                $response["jwt"] = null;
            }
            return $response;
        }
    }

    public function updateStore(Request $request)
    {
        $validation = $this->validationJWT($request);


        $store_name = $request->input('store_name');
        $phone = $request->input('phone');
        $photo_store = $request->file('photo_store');
        $address = $request->input('address');
        $latitude = $request->input('latitude');
        $longititude = $request->input('longititude');
        $description_store = $request->input('description_store');


        if ($photo_store) {
            $fotoStore = time() . $photo_store->getClientOriginalName();
        } else {
            $fotoStore = null;
        }

        $body = [
            'store_name' => $store_name,
            'phone' => $phone,
            'photo_store' => $fotoStore,
            'address' => $address,
            'latitude' => $latitude,
            'longititude' => $longititude,
            'description_store' => $description_store
        ];

        $response = json_decode($this->successResponse($this
            ->serviceStore
            ->update($body, $validation["data"]["id"]))
            ->original, true);

        if ($response["success"]) {
            if ($photo_store) {
                $photo_store->move('images', $fotoStore);
            }

            if ($validation["expired"]) {
                $response["jwt"] = $validation["jwt"];
            } else {
                $response["jwt"] = null;
            }

            return $response;
        }
    }

    public function statusOpen(Request $request, $status)
    {


        $validation = $this->validationJWT($request);

        // return $validation["data"]["id"];
        return json_decode($this->successResponse($this
            ->serviceStore
            ->statusOpen($status, $validation["data"]["id"]))
            ->original, true);
    }

    public function getListStoreFromAdmin(Request $request)
    {
        return json_decode($this->successResponse($this
            ->serviceStore
            ->getListStoreFromAdmin())
            ->original, true);
    }

    public function updateStoreFromAdmin(Request $request, $id)
    {
        $owner_name = $request->input("owner_name");
        $store_name = $request->input("store_name");
        $phone = $request->input("phone");
        $email = $request->input("email");
        $fcm = $request->input("fcm");
        $description_store = $request->input("description_store");
        $nik_ktp = $request->input("nik_ktp");
        $photo_ktp = $request->file("photo_ktp");
        $photo_store = $request->file("photo_store");
        $latitude = $request->input("latitude");
        $longititude = $request->input("longititude");
        $address = $request->input("address");

        $validation = $this->validationJWT($request);

        if (isset($validation["data"]["role"])) {
            if ($validation["data"]["role"] == $this->SUPER_ADMIN || $validation["data"]["role"] == $this->ADMIN) {


                if ($photo_ktp && $photo_store) {
                    $ktp = time() . $photo_ktp->getClientOriginalName();
                    $fotoStore = time() . $photo_store->getClientOriginalName();
                    $body = [
                        "owner_name" => $owner_name,
                        "store_name" => $store_name,
                        "phone" => $phone,
                        "email" => $email,
                        "fcm" => $fcm,
                        "description_store" => $description_store,
                        "nik_ktp" => $nik_ktp,
                        "photo_ktp" => $ktp,
                        "latitude" => $latitude,
                        "longititude" => $longititude,
                        "address" => $address,
                        "photo_store" => $fotoStore
                    ];
                } else if ($photo_ktp) {
                    $ktp = time() . $photo_ktp->getClientOriginalName();
                    $body = [
                        "owner_name" => $owner_name,
                        "store_name" => $store_name,
                        "phone" => $phone,
                        "email" => $email,
                        "fcm" => $fcm,
                        "description_store" => $description_store,
                        "nik_ktp" => $nik_ktp,
                        "photo_ktp" => $ktp,
                        "latitude" => $latitude,
                        "longititude" => $longititude,
                        "address" => $address,
                    ];
                } else if ($photo_store) {
                    $fotoStore = time() . $photo_store->getClientOriginalName();
                    $body = [
                        "owner_name" => $owner_name,
                        "store_name" => $store_name,
                        "phone" => $phone,
                        "email" => $email,
                        "fcm" => $fcm,
                        "description_store" => $description_store,
                        "nik_ktp" => $nik_ktp,
                        "latitude" => $latitude,
                        "longititude" => $longititude,
                        "address" => $address,
                        "photo_store" => $fotoStore
                    ];
                } else {
                    $body = [
                        "owner_name" => $owner_name,
                        "store_name" => $store_name,
                        "phone" => $phone,
                        "email" => $email,
                        "fcm" => $fcm,
                        "description_store" => $description_store,
                        "nik_ktp" => $nik_ktp,
                        "latitude" => $latitude,
                        "longititude" => $longititude,
                        "address" => $address,
                    ];
                }

                $response = json_decode($this->successResponse($this
                    ->serviceStore
                    ->update($body, $id))
                    ->original, true);

                if ($response["success"]) {
                    if ($photo_store) {
                        $photo_store->move('images', $fotoStore);
                    }
                    if ($photo_ktp) {
                        $photo_ktp->move('images', $ktp);
                    }
                    return $response;
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'authentification failed',
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not found',
            ], 404);
        }
    }

    public function getStore($id)
    {

        $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getStore($id))
            ->original, true);

        $product = json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProductStoreFromAdmin($id))
            ->original, true);


        if (isset($store)) {
            if (isset(($product))) {
                $store["product"] = $product;
            } else {
                $store["product"] = null;
            }
            return $store;
        }
    }

    public function activation(Request $request, $id_store, $status)
    {
        $validation = $this->validationJWT($request);

        if ($validation["data"]["role"] == "admin" || $validation["data"]["role"] == "super_admin") {
            if ($status == $this->ACTIVE) {
                return json_decode($this->successResponse($this
                    ->serviceStore
                    ->changeStatusAktivation($id_store, $this->ACTIVE))
                    ->original, true);
            } else {
                return json_decode($this->successResponse($this
                    ->serviceStore
                    ->changeStatusAktivation($id_store, $this->DELETE))
                    ->original, true);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'authentifikasi failed',
            ], 400);
        }
    }

    public function getDetailTransaction(Request $request,$notrans,$id_store){
        $this->validationJWT($request);

        // return $notrans;

        $response = json_decode($this->successResponse($this
        ->serviceDetailTransaction
        ->getDetail($notrans,$id_store))
        ->original,true);

        return $response;
    }
}
