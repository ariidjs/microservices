<?php

namespace App\Http\Controllers;

use App\Models\Customers;
use App\Services\AuthServiceAdmin;
use App\Services\FcmService;
use App\Services\ServiceAdmin;
use App\Services\ServiceBenefit;
use App\Services\ServiceCustomer;
use App\Services\ServiceDetailTransaction;
use App\Services\ServiceDriver;
use App\Services\ServiceManagement;
use App\Services\ServiceProduct;
use App\Services\ServicePromo;
use App\Services\ServiceSaldoDriver;
use App\Services\ServiceSaldoStore;
use App\Services\ServiceStore;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class AuthAdminController extends BaseController
{
    use ApiResponser;
    private $serviceAdmin;
    private $authServiceAdmin;
    private $serviceTransaction;
    private $serviceCustomer;
    private $serviceProduct;
    private $serviceStore;
    private $serviceBenefit;
    private $serviceSaldoDriver;
    private $serviceSaldoStore;
    private $serviceDriver;
    private $TIME_EXPIRE = 3;
    private $serviceDetailTransaction;
    private $DELETE = 1;
    private $serviceManagement;
    private $AKTIF = 0;
    private $servicePromo;
    private $ACTIVE = 1;
    private $PENDING = 0;
    private $fcmService;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    private $AUTHKEYFCM = "key=AAAAC-0CIus:APA91bGZfiR7Q8hIO4W_gCTegqugpbiPnf8Ygnn72lyNtg1MoGt2Q3OkSNH_aOBefIjiEWcXl1VUbsLlWKAziWPBJiol_RBI1X2IDkfG9MY9YbR_wuHMO8FOTUFuSE-dYY8OjsLq6din";
    public function __construct(ServicePromo $servicePromo,ServiceManagement $serviceManagement,ServiceAdmin $serviceAdmin,
     AuthServiceAdmin $authServiceAdmin, ServiceTransaction $serviceTransaction, ServiceCustomer $serviceCustomer,
    ServiceProduct $serviceProduct,ServiceStore $serviceStore,ServiceDetailTransaction $serviceDetailTransaction,FcmService $fcmService,
    ServiceBenefit $serviceBenefit,ServiceDriver $serviceDriver,ServiceSaldoStore $serviceSaldoStore,ServiceSaldoDriver $serviceSaldoDriver)
    {
        $this->serviceAdmin = $serviceAdmin;
        $this->authServiceAdmin = $authServiceAdmin;
        $this->serviceTransaction = $serviceTransaction;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceProduct = $serviceProduct;
        $this->serviceStore = $serviceStore;
        $this->serviceDetailTransaction = $serviceDetailTransaction;
        $this->serviceManagement = $serviceManagement;
        $this->servicePromo = $servicePromo;
        $this->serviceBenefit = $serviceBenefit;
        $this->serviceDriver = $serviceDriver;
        $this->serviceSaldoDriver = $serviceSaldoDriver;
        $this->serviceSaldoStore = $serviceSaldoStore;
        $this->fcmService = $fcmService;
    }

    public function validationJWT($request)
    {

        $jwt = request()->header('Authorization');
        $jwt = str_replace('Bearer ', '', $jwt);
        $fcm = $request->header('fcm');
        try {
            $data = JWT::decode($jwt, $this->key, array('HS256'));
            return [
                "jwt" => $jwt,
                "data" => (array)$data
            ];
        } catch (ExpiredException $ex) {
            return response()->json([
                'success' => false,
                'message' => "unathorized failed"
            ], 400);
        }
    }

    public function register(Request $request)
    {
        // return "Hello";
        $name = $request->input('name');
        $username = $request->input('username');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        $avatar = $request->file('avatar');

        if ($avatar) {
            $photoName = time() . $avatar->getClientOriginalName();
        } else {
            $photoName = 'default.png';
        }

        $body = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'avatar' => $photoName,
            'role' => $role
        ];

        $response = json_decode($this->successResponse($this
            ->serviceAdmin
            ->register($body))
            ->original, true);

        if ($response["success"]) {
            if ($avatar) {
                $avatar->move('images', $photoName);
            }
            return $response;
        }
    }

    public function login(Request $request)
    {

        $username = $request->input("username");
        $password = $request->input("password");

        $body = [
            "username" => $username,
            "password" => $password
        ];

        $response =  json_decode($this->successResponse($this
            ->authServiceAdmin
            ->login($body))
            ->original, true);



        if ($response["success"]) {
            $payload = array(
                "id" => $response['data']['id'],
                "name" => $response['data']['name'],
                "avatar" => $response['data']['avatar'],
                "role" => $response['data']['role'],
                "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
            );
            $jwt = JWT::encode($payload,  $this->key);
            $response['data']['jwt'] = $jwt;
            return $response;
        }
    }

    public function getPromo()
    {
        $response =  json_decode($this->successResponse($this
            ->serviceTransaction
            ->getListTransaction())
            ->original, true);

        $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getLisCustomer())
            ->original, true);

        if (isset($response["data"]) && isset($customer["data"])) {
            $listTransaction = $response["data"];
            $listCustomer = $customer["data"];
            $transactionWithUserId = $this->inner_join($listTransaction, $listCustomer);

            $groups = array();
            foreach ($transactionWithUserId as $item) {
                $key = $item['id'];
                $total = 1;
                $total++;
                if (!array_key_exists($key, $groups)) {
                    $groups[$key] = array(
                        'id' => $item['id'],
                        'total_price' => $item['total_price'],
                        'level' => $item["level"],
                        'fcm' => $item["fcm"],
                        'transaction' => 1,
                        'name' => $item["name"],
                        'phone' => $item["phone"],
                        'level' => $item["level"],

                    );
                } else {
                    $groups[$key]['total_price'] = $groups[$key]['total_price'] + $item['total_price'];
                    $groups[$key]['transaction'] = $groups[$key]['transaction'] + 1;
                }
            }

            $data = array();
            foreach ($groups as $key => $value) {
                array_push($data, $value);
            }

            foreach ($data as $key => $value) {
                $total_price = $value["total_price"];
                if ($total_price <= 2000) {
                    $data[$key]["total_price"] = 1;
                } else if ($total_price > 2000 && $total_price <= 5000) {
                    $data[$key]["total_price"] = 0.8;
                } else if ($total_price > 5000 && $total_price <= 7000) {
                    $data[$key]["total_price"] = 0.6;
                } else if ($total_price > 7000 && $total_price <= 10000) {
                    $data[$key]["total_price"] = 0.4;
                } else if ($total_price > 10000) {
                    $data[$key]["total_price"] = 0.2;
                }

                //convert total__order
                $transaction = $value["transaction"];
                // echo $transaction.PHP_EOL;
                if ($transaction < 3) {
                    $data[$key]["transaction"] = 0.2;
                } else if ($transaction >= 3 && $transaction <= 5) {
                    $data[$key]["transaction"] = 0.4;
                } else if ($transaction >= 6 && $transaction <= 7) {
                    $data[$key]["transaction"] = 0.6;
                } else if ($transaction >= 8 && $transaction <= 10) {
                    $data[$key]["transaction"] = 0.8;
                } else if ($transaction > 10) {
                    $data[$key]["transaction"] = 1;
                }

                // convert rating
                $level = $value["level"];
                // echo $level.PHP_EOL;
                if ($level == "Silver") {
                    $data[$key]["level"] = 0.7;
                } else if ($level == "Gold") {
                    $data[$key]["level"] = 0.8;
                } else if ($level == "Platinum") {
                    $data[$key]["level"] = 0.9;
                }
            }

            $columnTotalPrice = array_column($data, "total_price");
            $columnTotalTransaction = array_column($data, "transaction");
            $columnLevel = array_column($data, "level");
            $maxTotalPrice = max($columnTotalPrice);
            $maxTotalTransaction = max($columnTotalTransaction);
            $maxLevel = max($columnLevel);

            foreach ($data as $key => $value) {
                $level = $value["level"] / $maxLevel;
                $totalPrice = $value["total_price"] / $maxTotalPrice;
                $totalTransaction = $value["transaction"] / $maxTotalTransaction;
                $data[$key]["level"] = $level;
                $data[$key]["total_price"] = $totalPrice;
                $data[$key]["transaction"] = $totalTransaction;
            }

            foreach ($data as $key => $value) {
                $totalSAW = ($value["level"] * 0.25) + ($value["total_price"] * 0.25) + ($value["transaction"] * 0.5);
                $data[$key]["saw"] = $totalSAW;
            }

            usort($data, function ($a, $b) {
                if ($a['saw'] == $b['saw']) {
                    return 0;
                }
                return ($a['saw'] > $b['saw']) ? -1 : 1;
            });
            return $data;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found',
            ], 404);
        }
    }

    public function statusDeleteProduct($idProduct, $status)
    {
        if ($status == $this->AKTIF) {
            return json_decode($this->successResponse($this
                ->serviceProduct
                ->changeStatusDeleteProduct($idProduct, $this->AKTIF))
                ->original, true);
        } else if ($status == $this->DELETE) {
            return json_decode($this->successResponse($this
                ->serviceProduct
                ->changeStatusDeleteProduct($idProduct, $this->DELETE))
                ->original, true);
        }
    }


    function inner_join(array $left, array $right)
    {
        $out = array();
        foreach ($left as $left_record) {
            foreach ($right as $right_record) {
                if ($left_record["id_customer"] == $right_record["id"]) {
                    $out[] = array_merge($left_record, $right_record);
                }
            }
        }
        return $out;
    }

    public function updateCustomerAdmin(Request $request, $id)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $address = $request->input('address');
        $level = $request->input('level');

        $body =[
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "address" => $address,
            "level" => $level,
        ];

        $update = json_decode($this->successResponse($this
            ->serviceCustomer
            ->update($body,$id))
            ->original, true);


        if ($update["success"]) {
            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'update failed',
            ], 401);
        }
    }

    public function getListTransaction(Request $request){
        $this->validationJWT($request);



        $response = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getListTransaction())
        ->original, true);

        return $response;

       $data = collect($response["data"])->map(function($item,$key){
            $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($item["id_customer"]))
            ->original, true);

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
            'data' => $data
        ], 200);
    }

    public function getListTransactionAdmin(Request $request){
        $this->validationJWT($request);

        $response = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getListTransactionAdmin())
        ->original, true);


       $data = collect($response["data"])->map(function($item,$key){
            $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($item["id_customer"]))
            ->original, true);

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
            'data' => $data
        ], 200);
    }

    // listTransaction berdasarkan id customer
    public function getListTransactionCustomer(Request $request,$idCustomer){
        $this->validationJWT($request);

        $response = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getListTransactionCustomer($idCustomer))
        ->original, true);

        $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($idCustomer))
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



    public function getDetailTransaction(Request $request,$notrans,$id_store){
        $this->validationJWT($request);

        $response = json_decode($this->successResponse($this
        ->serviceDetailTransaction
        ->getDetail($notrans,$id_store))
        ->original,true);

        return $response;
    }

    public function getManagementSystem(){
        // return "hello";

        $response = json_decode($this->successResponse($this
        ->serviceManagement
        ->getManagement())
        ->original,true);

        return $response;
    }

    public function updateManagementSystem(Request $request){

        $distance = $request->input('distance');
        $total_order = $request->input('total_order');
        $rating = $request->input('rating');
        $jumlah_transaksi = $request->input('jumlah_transaksi');
        $level_pelanggan = $request->input('level_pelanggan');
        $total_transaksi = $request->input('total_transaksi');

        $body = [
            "distance"=>$distance,
            "total_order"=>$total_order,
            "rating"=>$rating,
            "jumlah_transaksi"=>$jumlah_transaksi,
            "level_pelanggan"=>$level_pelanggan,
            "total_transaksi"=>$total_transaksi,
        ];


        $response = json_decode($this->successResponse($this
        ->serviceManagement
        ->update($body))
        ->original,true);

        return $response;
    }

    public function promo(Request $request){
        $customer = json_decode($this->successResponse($this
        ->serviceCustomer
        ->getLisCustomer())
        ->original,true);

      $transaction = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getInfoDetailTransaction())
        ->original,true);

        $management = json_decode($this->successResponse($this
        ->serviceManagement
        ->getManagement())
        ->original, true);

        $jumlahTransaksiRange = explode(",", $management["data"]["jumlah_transaksi"]);
        $levelRange = explode(",", $management["data"]["level_pelanggan"]);
        $totalTransaksiRange = explode(",", $management["data"]["total_transaksi"]);

       $listCustomer = $this->inner_join($transaction["data"],$customer["data"]);

        // $listCustomer = array(
        //     array("id_customer"=>2,"level"=>"Platinum","total_transaction"=>45,"total_price"=>2740000),
        //     array("id_customer"=>4,"level"=>"Gold","total_transaction"=>1,"total_price"=>45000),
        //     array("id_customer"=>5,"level"=>"Silver","total_transaction"=>1,"total_price"=>20000),
        // );

        $c1 = 0.25;
        $c2 = 0.5;
        $c3 = 0.25;

        foreach ($listCustomer as $key => $value) {
            // echo $distance.PHP_EOL;
            $jumlahTransaksi = $value["total_transaction"];
            if ($jumlahTransaksi <= $jumlahTransaksiRange[0]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.2;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[0] && $jumlahTransaksi <= $jumlahTransaksiRange[1]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.4;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[1] && $jumlahTransaksi <= $jumlahTransaksiRange[2]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.6;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[2] && $jumlahTransaksi <= $jumlahTransaksiRange[3]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.8;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[3]) {
                $listCustomer[$key]["total_transactionSaw"] = 1;
            }

            //convert total__order
            $level = $value["level"];
            // echo $totalOrder.PHP_EOL;
            if ($level == trim($levelRange[0])) {
                $listCustomer[$key]["levelSaw"] = 0.7;
            } else if ($level == trim($levelRange[1])) {
                $listCustomer[$key]["levelSaw"] = 0.8;
            } else if ($level == trim($levelRange[2])) {
                $listCustomer[$key]["levelSaw"] = 0.9;
            }

            // convert rating
            $total_price = $value["total_price"];
            // echo $rating.PHP_EOL;
            if ($total_price <= $totalTransaksiRange[0]) {
                $listCustomer[$key]["total_priceSaw"] = 0.2;
            } else if ($total_price > $totalTransaksiRange[0] && $total_price <= $totalTransaksiRange[1]) {
                $listCustomer[$key]["total_priceSaw"] = 0.4;
            } else if ($total_price > $totalTransaksiRange[1] && $total_price <= $totalTransaksiRange[2]) {
                $listCustomer[$key]["total_priceSaw"] = 0.6;
            } else if ($total_price > $totalTransaksiRange[2] && $total_price <= $totalTransaksiRange[3]) {
                $listCustomer[$key]["total_priceSaw"] = 0.8;
            } else if ($total_price > $totalTransaksiRange[3]) {
                $listCustomer[$key]["total_priceSaw"] = 1;
            }
        }

        // return $listCustomer;

        if(sizeof($listCustomer) > 0){
            $columnJumlahTransaksi = array_column($listCustomer, "total_transactionSaw");
            $columnlevel = array_column($listCustomer, "levelSaw");
            $columnTotalPrice = array_column($listCustomer, "total_priceSaw");
            $maxJumlahTransaksi = max($columnJumlahTransaksi);
            $maxlevel = max($columnlevel);
            $maxTotalPrice = max($columnTotalPrice);

            // return $columnlevel;

            // return $listCustomer;


            foreach ($listCustomer as $key => $value) {
                $totalPrice = $value["total_priceSaw"] / $maxTotalPrice;
                $level = $value["levelSaw"] / $maxlevel;
                $jumlahTransaksi = $value["total_transactionSaw"] / $maxJumlahTransaksi;
                $listCustomer[$key]["levelSaw"] = $level;
                $listCustomer[$key]["total_priceSaw"] = $totalPrice;
                $listCustomer[$key]["total_transactionSaw"] = $jumlahTransaksi;
            }

            // return $listCustomer;


            foreach ($listCustomer as $key => $value) {
                $totalSAW = ($value["total_transactionSaw"] * $c1)
                +($value["levelSaw"] * $c3)+
                ($value["total_priceSaw"] * $c2);
                $listCustomer[$key]["saw"] = $totalSAW;
            }

            // return $listCustomer;
            usort($listCustomer,function($a,$b){
                if ($a['saw'] == $b['saw']) {
                    return 0;
                }
                return ($a['saw'] > $b['saw']) ? -1 : 1;
            });

            if(sizeof($listCustomer) > 1){
                for($i=0;$i<2;$i++){
                    $body = $request->only([
                            'promoName','promoDescription','promoPrice','date','expired'
                    ]);
                    $body['idCustomer'] = $listCustomer[$i]["id_customer"];

                    json_decode($this->successResponse($this
                    ->servicePromo
                    ->savePromo($body))
                    ->original,true);
                }

                return response()->json([
                    "message" =>"success",
                    "status"=>true,
                ],201);
            }else{
                $body = $request->only([
                        'promoName','promoDescription','promoPrice','date','expired'
                ]);
                $body['idCustomer'] = $listCustomer[0]["id_customer"];
                json_decode($this->successResponse($this
                ->servicePromo
                ->savePromo($body))
                ->original,true);

                return response()->json([
                    "message" =>"success",
                    "status"=>true,
                ],201);
            }
        }else{
            return response()->json([
                "message" =>"data tidak ditemukan",
                "status"=>false,
                "data"=>null
            ],201);
        }
    }

    public function getListDriver(Request $request)
    {
        $this->validationJWT($request);
        return json_decode($this->successResponse($this
            ->serviceDriver
            ->getListDriverFromAdmin())
            ->original, true);
    }

    public function searchCustomerPromo(Request $request){
        $customer = json_decode($this->successResponse($this
                ->serviceCustomer
                ->getLisCustomer())
                ->original,true);

        $transaction = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getInfoDetailTransaction())
        ->original,true);

        $management = json_decode($this->successResponse($this
        ->serviceManagement
        ->getManagement())
        ->original, true);

        $jumlahTransaksiRange = explode(",", $management["data"]["jumlah_transaksi"]);
        $levelRange = explode(",", $management["data"]["level_pelanggan"]);
        $totalTransaksiRange = explode(",", $management["data"]["total_transaksi"]);

        $listCustomer = $this->inner_join($transaction["data"],$customer["data"]);

        // return $listCustomer;

        // $listCustomer = array(
        //     array("id_customer"=>2,"level"=>"Platinum","total_transaction"=>45,"total_price"=>2740000),
        //     array("id_customer"=>4,"level"=>"Gold","total_transaction"=>1,"total_price"=>45000),
        //     array("id_customer"=>5,"level"=>"Silver","total_transaction"=>1,"total_price"=>20000),
        // );

        $c1 = 0.25;
        $c2 = 0.5;
        $c3 = 0.25;

        foreach ($listCustomer as $key => $value) {
            // echo $distance.PHP_EOL;
            $jumlahTransaksi = $value["total_transaction"];
            if ($jumlahTransaksi <= $jumlahTransaksiRange[0]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.2;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[0] && $jumlahTransaksi <= $jumlahTransaksiRange[1]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.4;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[1] && $jumlahTransaksi <= $jumlahTransaksiRange[2]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.6;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[2] && $jumlahTransaksi <= $jumlahTransaksiRange[3]) {
                $listCustomer[$key]["total_transactionSaw"] = 0.8;
            } else if ($jumlahTransaksi > $jumlahTransaksiRange[3]) {
                $listCustomer[$key]["total_transactionSaw"] = 1;
            }

            //convert total__order
            $level = $value["level"];
            // echo $totalOrder.PHP_EOL;
            if ($level == trim($levelRange[0])) {
                $listCustomer[$key]["levelSaw"] = 0.7;
            } else if ($level == trim($levelRange[1])) {
                $listCustomer[$key]["levelSaw"] = 0.8;
            } else if ($level == trim($levelRange[2])) {
                $listCustomer[$key]["levelSaw"] = 0.9;
            }

            // convert rating
            $total_price = $value["total_price"];
            // echo $rating.PHP_EOL;
            if ($total_price <= $totalTransaksiRange[0]) {
                $listCustomer[$key]["total_priceSaw"] = 0.2;
            } else if ($total_price > $totalTransaksiRange[0] && $total_price <= $totalTransaksiRange[1]) {
                $listCustomer[$key]["total_priceSaw"] = 0.4;
            } else if ($total_price > $totalTransaksiRange[1] && $total_price <= $totalTransaksiRange[2]) {
                $listCustomer[$key]["total_priceSaw"] = 0.6;
            } else if ($total_price > $totalTransaksiRange[2] && $total_price <= $totalTransaksiRange[3]) {
                $listCustomer[$key]["total_priceSaw"] = 0.8;
            } else if ($total_price > $totalTransaksiRange[3]) {
                $listCustomer[$key]["total_priceSaw"] = 1;
            }
        }

        // return $listCustomer;

        if(sizeof($listCustomer) > 0){
            $columnJumlahTransaksi = array_column($listCustomer, "total_transactionSaw");
            $columnlevel = array_column($listCustomer, "levelSaw");
            $columnTotalPrice = array_column($listCustomer, "total_priceSaw");
            $maxJumlahTransaksi = max($columnJumlahTransaksi);
            $maxlevel = max($columnlevel);
            $maxTotalPrice = max($columnTotalPrice);

            // return $columnlevel;

            // return $listCustomer;


            foreach ($listCustomer as $key => $value) {
                $totalPrice = $value["total_priceSaw"] / $maxTotalPrice;
                $level = $value["levelSaw"] / $maxlevel;
                $jumlahTransaksi = $value["total_transactionSaw"] / $maxJumlahTransaksi;
                $listCustomer[$key]["levelSaw"] = $level;
                $listCustomer[$key]["total_priceSaw"] = $totalPrice;
                $listCustomer[$key]["total_transactionSaw"] = $jumlahTransaksi;
            }

            // return $listCustomer;


            foreach ($listCustomer as $key => $value) {
                $totalSAW = ($value["total_transactionSaw"] * $c1)
                +($value["levelSaw"] * $c3)+
                 ($value["total_priceSaw"] * $c2);
                $listCustomer[$key]["saw"] = $totalSAW;
            }

            // return $listCustomer;


            usort($listCustomer,function($a,$b){
                if ($a['saw'] == $b['saw']) {
                    return 0;
                }
                return ($a['saw'] > $b['saw']) ? -1 : 1;
            });
            $body = $request->only([
                'idCustomer', 'promoName','promoDescription','promoPrice','date','expired'
            ]);
            return $listCustomer;
        }else{
            return response()->json([
                "message" =>"data tidak ditemukan",
                "status"=>false,
                "data"=>null
            ],201);
        }


    }

    public function activationStore(Request $request, $id_store, $status)
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
                    ->changeStatusAktivation($id_store, $this->PENDING))
                    ->original, true);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'authentifikasi failed',
            ], 400);
        }
    }

    public function getDetailTransactionAdmin(Request $request,$notrans){
        $this->validationJWT($request);

        // return $notrans;
        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->getDetailTransaction($notrans))
            ->original, true);
    }

    public function dashboard(){

        $countCustomer = json_decode($this->successResponse($this
        ->serviceCustomer
        ->count())
        ->original,true);

        $countDriver = json_decode($this->successResponse($this
        ->serviceDriver
        ->count())
        ->original,true);

        $countStore = json_decode($this->successResponse($this
        ->serviceStore
        ->count())
        ->original,true);

        $totalBenefit = json_decode($this->successResponse($this
        ->serviceBenefit
        ->getTotalBenefit())
        ->original,true);

        $dashboard = json_decode($this->successResponse($this
        ->serviceTransaction
        ->chartDashboard())
        ->original,true);

        $chartBenefit = json_decode($this->successResponse($this
        ->serviceBenefit
        ->chartBenefit())
        ->original,true);


        return response()->json([
            'success' => true,
            'message' => 'login success',
            'data' => [
                "customer"=>$countCustomer["data"],
                "driver"=>$countDriver["data"],
                "store"=>$countStore["data"],
                "benefit"=>$totalBenefit["data"],
                "chart"=>$dashboard["data"],
                "charBenefit"=>$chartBenefit["data"]
            ]
        ], 201);



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
        $latitude = $request->input("latitude");
        $longititude = $request->input("longititude");
        $address = $request->input("address");

        $this->validationJWT($request);

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

        return  json_decode($this->successResponse($this
        ->serviceStore
        ->update($body, $id))
        ->original, true);
    }

    public function listBenefit(){
        return json_decode($this->successResponse($this
        ->serviceBenefit
        ->listBenefit())
        ->original,true);
    }

    public function getListPromo(){
        return json_decode($this->successResponse($this
        ->servicePromo
        ->getListPromo())
        ->original,true);
    }

    public function getInfoStore($idStore){
       $product = json_decode($this->successResponse($this
        ->serviceProduct
        ->getListProductStore($idStore))
        ->original, true);

        $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getStore($idStore))
            ->original, true);

        return response()->json([
                'success' => true,
                'message' => 'success',
                'store' => $store["data"],
                'product' => $product["data"]
        ], 201);


    }

    public function getInfoDriver($idDriver){
        $transaction = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getListTransactionDriver($idDriver))
        ->original, true);

        $driver = json_decode($this->successResponse($this
            ->serviceDriver
            ->getDriver($idDriver))
            ->original, true);

        return response()->json([
                'success' => true,
                'message' => 'success',
                'driver' => $driver["data"],
                'transaction' => $transaction["data"]
        ], 201);
    }

    public function getListRequestSaldo(){
       $saldoDriver = json_decode($this->successResponse($this
        ->serviceSaldoDriver
        ->getListSaldoDriver())
        ->original, true);

        // if($saldoDriver["success"])

        $saldoStore = json_decode($this->successResponse($this
            ->serviceSaldoStore
            ->getListSaldoStore())
            ->original, true);

        if($saldoDriver["success"] && $saldoStore["success"]){
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => [
                    "driver"=>$saldoDriver["data"],
                    "store"=>$saldoStore["data"],
                ]
            ], 201);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'failed',
                'data' => null
            ], 201);
        }
    }

    public function updateSaldoStore($id,$type){
       $info = json_decode($this->successResponse($this
            ->serviceSaldoStore
            ->getDetail($id))
            ->original,true);

       $store = json_decode($this->successResponse($this
        ->serviceStore
        ->getStore($info["data"]["id_store"]))
        ->original,true);



        if($info["success"]){

            if($type == "failed"){
                $updateStatusSaldo = json_decode($this->successResponse($this
                ->serviceSaldoStore
                ->updateStatus($info["data"]["id"],"failed"))
                ->original,true);

                $dataFcm = [
                    "title" => "Permintaan anda telah ditolak",
                    "content"=>[
                        "title" => "Silahkan hubungi pihak customer service jika anda butuh info lebig lanjut",
                    ],
                ];
               $this->pushFcm($dataFcm, $store["data"]["fcm"]);

                return response()->json([
                    'success' => true,
                    'message' => 'success update ',
                ], 201);
            }

          $saldoStore = json_decode($this->successResponse($this
            ->serviceStore
            ->updateSaldoStore($info["data"]["id_store"],$info["data"]["saldo"],$info["data"]["type"]))
            ->original, true);

            $updateStatusSaldo = json_decode($this->successResponse($this
            ->serviceSaldoStore
            ->updateStatus($info["data"]["id"],"success"))
            ->original,true);

            if($saldoStore["success"] && $updateStatusSaldo["success"]){
                $dataFcm = [
                    "title" => "Permintaan ".$info["data"]["type"]." telah success di proses",
                    "content"=>[
                        "title" => "Silahkan cek history saldo anda saat ini",
                    ],
                ];
               $this->pushFcm($dataFcm, $store["data"]["fcm"]);
                return response()->json([
                    'success' => true,
                    'message' => 'success update ',
                ], 201);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'failed',
                ], 401);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 401);
        }
    }

    public function updateSaldoDriver($id,$type){

        $info = json_decode($this->successResponse($this
        ->serviceSaldoDriver
        ->getDetail($id))
        ->original,true);

        $driver = json_decode($this->successResponse($this
        ->serviceDriver
        ->getDriver($info["data"]["id_driver"]))
        ->original,true);

        if($info["success"]){

            if($type == "failed"){
                $updateStatusSaldo = json_decode($this->successResponse($this
                ->serviceSaldoDriver
                ->updateStatus($info["data"]["id"],"failed"))
                ->original,true);

                $dataFcm = [
                    "title" => "Permintaan anda telah ditolak",
                    "content"=>[
                        "title" => "Silahkan hubungi pihak customer service jika anda butuh info lebig lanjut",
                    ],
                ];
               $this->pushFcm($dataFcm, $driver["data"]["fcm"]);

                return response()->json([
                    'success' => true,
                    'message' => 'success update ',
                ], 201);
            }

            $saldoDriver = json_decode($this->successResponse($this
            ->serviceDriver
            ->updateSaldoDriver($info["data"]["id_driver"],$info["data"]["saldo"],$info["data"]["type"]))
            ->original, true);

            $updateStatusSaldo = json_decode($this->successResponse($this
            ->serviceSaldoDriver
            ->updateStatus($info["data"]["id"],"success"))
            ->original,true);


              if($saldoDriver["success"] && $updateStatusSaldo["success"]){
                $dataFcm = [
                    "title" => "Permintaan ".$info["data"]["type"]." telah success di proses",
                    "content"=>[
                        "title" => "Silahkan cek history saldo anda saat ini",
                    ],
                ];
               $this->pushFcm($dataFcm, $driver["data"]["fcm"]);
                  return response()->json([
                      'success' => true,
                      'message' => 'success update ',
                  ], 201);
              }else{
                  return response()->json([
                      'success' => false,
                      'message' => 'failed',
                  ], 401);
              }
          }else{
              return response()->json([
                  'success' => false,
                  'message' => 'failed',
              ], 401);
          }
    }

    private function pushFcm($data, $fcm)
    {
        $headers = [
            'Authorization' => $this->AUTHKEYFCM,
            'Content-Type' => 'application/json'
        ];
        $body = [
            "data" => [
                "title" => $data["title"],
                "content" => $data["content"]
            ],
            "to" => $fcm
        ];


       return json_decode($this->successResponse($this
            ->fcmService
            ->pushNotification($body, $headers))
            ->original, true);
    }

    public function updatePassword(Request $request){
        $validation = $this->validationJWT($request);


        $data = $request->only([
            "oldPassword","newPassword"
        ]);


       return $update = json_decode($this->successResponse($this
        ->serviceAdmin
        ->updatePassword($validation["data"]["id"],$data))
        ->original, true);

        return dd($data);

    }

    public function chartDashboard(Request $request){
        $this->validationJWT($request);
        $chartBenefit = json_decode($this->successResponse($this
            ->serviceBenefit
            ->chartBenefit())
            ->original,true);
        $chartTransaksi = json_decode($this->successResponse($this
            ->serviceTransaction
            ->chartDashboard())
            ->original,true);

        $chartTransaksi["benefit"] = $chartBenefit["data"];
        return $chartTransaksi;
    }

}
