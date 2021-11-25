<?php

namespace App\Http\Controllers;


use App\Services\ProductService;
use App\Services\DetailTransactionService;
use App\Services\StoreService;
use \Illuminate\Http\Request;
use \Illuminate\Support\Facades\Hash;
use \App\Models\Transaction;
use \App\Models\Store;
use App\Services\FcmService;
use App\Services\ServiceCustomer;
use App\Services\ServiceDriver;
use \App\Traits\ApiResponser;
use Kreait\Firebase\Database;
use Kreait\Firebase\Factory;
use Laravel\Lumen\Routing\Controller;


class TransactionController extends Controller
{
    use ApiResponser;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $productService;
    public $storeService;
    public $detailTransactionService;
    private $fcmService;
    private $TRANSACTION_CANCEL = 0;
    private $TRANSACTION_WAITING_STORE = 1;
    private $TRANSACTION_ACCEPT_STORE = 2;
    private $TRANSACTION_WAITING_DRIVER = 3;
    private $TRANSACTION_DRIVER_FOUND = 4;
    private $TRANSACTION_DRIVER_IN_STORE = 5;
    private $TRANSACTION_DONE = 6;
    private $configFirebase;
    private $databaseFirebase;
    private $serviceCustomer;
    private $serviceDriver;
    private $AUTHKEYFCM = "key=AAAAC-0CIus:APA91bGZfiR7Q8hIO4W_gCTegqugpbiPnf8Ygnn72lyNtg1MoGt2Q3OkSNH_aOBefIjiEWcXl1VUbsLlWKAziWPBJiol_RBI1X2IDkfG9MY9YbR_wuHMO8FOTUFuSE-dYY8OjsLq6din";


    public function __construct(
        ProductService $productService,
        StoreService $storeService,
        DetailTransactionService $detailTransactionService,
        FcmService $fcmService,
        ServiceCustomer $serviceCustomer,
        ServiceDriver $serviceDriver
    ) {
        // $factory = (new Factory)->withServiceAccount('../../../config/firebaseConfig.json');
        // $this->configFirebase = $factory;
        $this->productService = $productService;
        $this->storeService = $storeService;
        $this->detailTransactionService = $detailTransactionService;
        $this->fcmService = $fcmService;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceDriver = $serviceDriver;
        $factory = (new Factory)
            ->withServiceAccount(__DIR__ . '/firebaseKey.json')
            ->withDatabaseUri('https://proyek-akhir-1b6f2-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->auth = $factory->createAuth();
        $this->databaseFirebase = $factory->createDatabase();
        // $this->databaseFirebase = $databaseFirebase;
    }

    function generateRandomString($length = 5)
    {
        return substr(str_shuffle(str_repeat($x = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length / strlen($x)))), 1, $length);
    }

    public function searchDriver($latitude, $longitude, $listDriver)
    {
        // $listDriver = array(
        //     array("id_driver"=>1,"rating"=>4,"total_order"=>4,"coordinate"=>"-0.8948229819361365,100.36756917663485","fcm"=>"fQWvUY-8SL2mdFNPYy95_d:APA91bGWmptRIa2x9pE8yZN4v64eFvPQYDvHAA0hCctAddQpifS-bHMkjFl3G9xHv5JtXLnygsOMcNa36ysYLLlvvpmQC4T-jw8T5OfoPZwMvw_KejiKkVn8pTww35jiG8AjrUAhD4KK"),
        //     array("id_driver"=>2,"rating"=>3,"total_order"=>3,"coordinate"=>"-0.8923167624595327,100.36766181489392","fcm"=>"sadddddddddddddddddddddd"),
        //     array("id_driver"=>3,"rating"=>5,"total_order"=>41,"coordinate"=>"-0.9237285327683936, 100.37486082016031","fcm"=>"saddddddddddddddddddddddddddddd"),
        // );


        foreach ($listDriver as $key => $value) {
            //        convert coordinate
            $distance = $this->haversineGreatCircleDistance($latitude, $longitude, explode(",", $value["coordinate"])[0], explode(",", $value["coordinate"])[1]);
            // echo $distance.PHP_EOL;
            if ($distance <= 200) {
                $listDriver[$key]["coordinate"] = 1;
            } else if ($distance > 200 && $distance <= 300) {
                $listDriver[$key]["coordinate"] = 0.8;
            } else if ($distance > 300 && $distance <= 400) {
                $listDriver[$key]["coordinate"] = 0.6;
            } else if ($distance > 400 && $distance <= 500) {
                $listDriver[$key]["coordinate"] = 0.4;
            } else if ($distance > 500) {
                $listDriver[$key]["coordinate"] = 0.2;
            }

            //convert total__order
            $totalOrder = $value["total_order"];
            // echo $totalOrder.PHP_EOL;
            if ($totalOrder < 3) {
                $listDriver[$key]["total_order"] = 1;
            } else if ($totalOrder >= 3 && $totalOrder <= 5) {
                $listDriver[$key]["total_order"] = 0.8;
            } else if ($totalOrder >= 6 && $totalOrder <= 7) {
                $listDriver[$key]["total_order"] = 0.6;
            } else if ($totalOrder >= 8 && $totalOrder <= 10) {
                $listDriver[$key]["total_order"] = 0.4;
            } else if ($totalOrder > 10) {
                $listDriver[$key]["total_order"] = 0.2;
            }

            // convert rating
            $rating = $value["rating"];
            // echo $rating.PHP_EOL;
            if ($rating <= 3.0) {
                $listDriver[$key]["rating"] = 0.2;
            } else if ($rating >= 3.1 && $rating <= 3.5) {
                $listDriver[$key]["rating"] = 04;
            } else if ($rating >= 3.6 && $rating <= 4.0) {
                $listDriver[$key]["rating"] = 0.6;
            } else if ($rating >= 4.1 && $rating <= 4.5) {
                $listDriver[$key]["rating"] = 0.8;
            } else if ($rating >= 4.6) {
                $listDriver[$key]["rating"] = 1;
            }
        }

        $columnCoordinate = array_column($listDriver, "coordinate");
        $columnTotalOrder = array_column($listDriver, "total_order");
        $columnRating = array_column($listDriver, "rating");
        $maxCoordinate = max($columnCoordinate);
        $maxTotalOrder = max($columnTotalOrder);
        $maxRating = max($columnRating);

        foreach ($listDriver as $key => $value) {
            $rating = $value["rating"] / $maxRating;
            $totalOrder = $value["total_order"] / $maxTotalOrder;
            $coordinate = $value["coordinate"] / $maxCoordinate;
            $listDriver[$key]["total_order"] = $totalOrder;
            $listDriver[$key]["rating"] = $rating;
            $listDriver[$key]["coordinate"] = $coordinate;
        }

        foreach ($listDriver as $key => $value) {
            $totalSAW = ($value["coordinate"] * 0.5) + ($value["total_order"] * 0.25) + ($value["rating"] * 0.25);
            $listDriver[$key]["saw"] = $totalSAW;
        }

        $maxSaw = max(array_column($listDriver, "saw"));

        $result = array();
        foreach ($listDriver as $key => $value) {
            if ($value['saw'] == $maxSaw) {
                $result = $value;
            }
        }

        return $result;

        // $this->fcm($result["fcm"]);

        // return response()->json([
        //     'success'=>true,
        //     'message'=>'success',
        //     "listdata"=>$listDriver,
        //     "result"=>$result
        // ],201);
        //        return $result;
    }

    function haversineGreatCircleDistance(
        $latitudeFrom,
        $longitudeFrom,
        $latitudeTo,
        $longitudeTo,
        $earthRadius = 6371000
    ) {
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return $angle * $earthRadius;
    }




    function inner_join(array $left, array $right, $on)
    {
        $out = array();
        foreach ($left as $left_record) {
            foreach ($right as $right_record) {
                if ($left_record[$on] == $right_record[$on]) {
                    $out[] = array_merge($left_record, $right_record);
                }
            }
        }
        return $out;
    }

    public function insertCustomer(Request $request)
    {
        $dataCustomer = json_decode($request->getContent());
        $dataProductFromCustomer = json_decode(json_encode($dataCustomer->data_product), true);
        // return var_dump($dataProductFromCustomer);
        // return $dataCustomer->id_store;
        $product = json_decode($this->successResponse($this
            ->productService
            ->getProductStore($dataCustomer->id_store))
            ->original, true);



        $productFilter = $this->inner_join($product["data"], $dataProductFromCustomer, "id");
        $date = date_create();
        date_timestamp_set($date, time());
        $noTransaction = $dataCustomer->id_customer . date_format($date, "YmdHis");
        $total_price = 0;
        $driver_price = $dataCustomer->driver_price;
        $code_validation = $this->generateRandomString();
        $dataSubProduct = [];
        foreach ($productFilter as $value) {
            if ($value["price_promo"]) {
                $total_price += $value["price_promo"];
                array_push($dataSubProduct, array("notransaksi" => $noTransaction, "id_product" => $value["id"], "price_product" => $value["price_promo"], "count" => $value["count"]));
            } else {
                $total_price += $value["price"];
                array_push($dataSubProduct, ["notransaksi" => $noTransaction, "id_product" => $value["id"], "price_product" => $value["price"], "count" => $value["count"]]);
            }
        }



        // return $driver_price;
        // return var_dump($productFilter);

        //  return $product;

        // return var_dump($product["data"]);
        $store = json_decode($this->successResponse($this
            ->storeService
            ->getStore($dataCustomer->id_store))
            ->original, true);



        $transaction = Transaction::create([
            'notransaksi' => $noTransaction,
            'id_customer' => $dataCustomer->id_customer,
            'id_driver' => 0,
            'id_store' => $dataCustomer->id_store,
            'status' => $this->TRANSACTION_WAITING_STORE,
            'total_price' => $total_price,
            'driver_price' => $driver_price,
            'alamat_user' => $dataCustomer->address,
            'latitude' => $dataCustomer->latitude,
            'longitude' => $dataCustomer->longititude,
            'status_delete' => 0,
            'kode_validasi' => $code_validation
        ]);

        $dataFcmStore = [
            "title" => "Store notification",
            "content" => "Ada orderan ".$noTransaction
        ];

        $this->pushFcm($dataFcmStore,$store["data"]["fcm"]);

        if ($transaction) {
            return json_decode($this->successResponse($this
                ->detailTransactionService
                ->insert($dataSubProduct))
                ->original, true);
        } else {
            return json_encode($dataSubProduct);
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

    public function getListTransaction($idStore)
    {
        $list = Transaction::whereIdStore($idStore)->get();

        if ($list) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $list
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found'
            ], 400);
        }
    }

    public function statusFromStore(Request $request, $id)
    {
        // $latitude = $request->input('latitude');
        // $longititude = $request->input('longititude');
        $status = $request->input('status');

        // return $status;
        $transaction = json_decode(Transaction::whereId($id)->first());
        // return var_dump($transaction);
        $key = $this->databaseFirebase->getReference('DriversLocation')->getChildKeys();
        $dataDriver = [];
        foreach ($key as $value) {
            array_push($dataDriver, $this->databaseFirebase->getReference('DriversLocation')->getChild($value)->getValue());
        }

        // return $dataDriver;
        // return var_dump($dataDriver);
        // return $ref;

        // return var_dump($ref);
        $driver = $this->searchDriver($transaction->latitude, $transaction->longitude, $dataDriver);
        // return $driver;
        // return var_dump($driver);

        $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($transaction->id_customer))
            ->original, true)["data"];
        $driver = json_decode($this->successResponse($this
            ->serviceDriver
            ->getDriver($driver["id_driver"]))
            ->original, true)["data"];

        $store = json_decode($this->successResponse($this
            ->storeService
            ->getStore($transaction->id_store))
            ->original, true)["data"];

        // return var_dump($store);
        if ($status == $this->TRANSACTION_ACCEPT_STORE || $status == $this->TRANSACTION_WAITING_DRIVER) {
            $updated = Transaction::whereId($id)->update([
                "status" => $this->TRANSACTION_WAITING_DRIVER
            ]);
            $dataFcmCustomer = [
                "title" => "Store notification",
                "content" => "sedang mencari driver"
            ];
            $dataFcmDriver = [
                "title" => "Driver notification",
                "content" => [
                    "transaksi" => $transaction,
                    "store" => [
                        "store_name" => $store["store_name"],
                        "phone" => $store["phone"],
                        "latitude" => $store["latitude"],
                        "longititude" => $store["longititude"],
                        "address" => $store["address"]
                    ]
                ]
            ];


            $notifCustomer = $this->pushFcm($dataFcmCustomer, $customer["fcm"]);
            $notifDriver = $this->pushFcm($dataFcmDriver, $driver["fcm"]);
            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                    'notifDriver' => $notifDriver,
                    'notifCustomer' => $notifCustomer,

                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'failed',
                    'notifDriver' => $notifDriver,
                    'notifCustomer' => $notifCustomer,
                ], 404);
            }
        }

        if ($status == $this->TRANSACTION_CANCEL) {
            $updated = Transaction::whereId($id)->update([
                "status" => $this->TRANSACTION_CANCEL
            ]);

            $dataFcm = [
                "title" => "Store notification",
                "content" => "pesanan anda dibatalkan"
            ];

            $notifCustomer = $this->pushFcm($dataFcm, $customer["fcm"]);

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                    'notifCustomer' => $notifCustomer,
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'failed',
                ], 404);
            }
        }
    }

    public function statusFromDriver(Request $request,$id){
        // return $id;
        $status = $request->input('status');
        $id_driver = $request->input('id_driver');
        $transaction = json_decode(Transaction::whereId($id)->first());

        // return var_dump($transaction);

        $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($transaction->id_customer))
            ->original,true)["data"];

        $store =json_decode($this->successResponse($this
            ->storeService
            ->getStore($transaction->id_store))
            ->original,true)["data"];

        $detailTransaction =json_decode($this->successResponse($this
            ->detailTransactionService
            ->getNotransaksi($transaction->notransaksi,$transaction->id_store))
            ->original,true)["data"];


        $filterDetailTransaction = [];
        foreach ($detailTransaction as $key => $value) {
            array_push($filterDetailTransaction,[
                "id_product"=>$value["id_product"],
                "price_product"=>$value["price_product"],
                "count"=>$value["count"],
                "name_product"=>$value["name_product"],
                "category"=>$value["category"],
                "image1"=>$value["image1"],
                "image2"=>$value["image2"],
                "image3"=>$value["image3"],
                "image4"=>$value["image4"],
                "description"=>$value["description"]
            ]);
        }
        if($status == $this->TRANSACTION_DRIVER_FOUND){
            $updated = Transaction::whereId($id)->update([
                "status"=>$this->TRANSACTION_DRIVER_FOUND,
                "id_driver"=>$id_driver
            ]);
            $dataFcmCustomer = [
                "title"=>"customer notification",
                "content"=>"Driver ditemukan"
            ];
            $dataFcmStore = [
                "title"=>"Store notification",
                "content"=>"Driver ditemukan"
            ];

            $notifCustomer = $this->pushFcm($dataFcmCustomer,$customer["fcm"]);
            $notifStore = $this->pushFcm($dataFcmStore,$store["fcm"]);

            if($updated){
                $transaction = json_decode(Transaction::whereId($id)->first());
                $driver =json_decode($this->successResponse($this
                    ->serviceDriver
                    ->statusWork(0, $id_driver))
                    ->original,true);
                if($driver) {
                    return response()->json([
                        'success'=>true,
                        'message'=>'success',
                        // 'notifStore'=>$notifStore,
                        // 'notifCustomer'=>$notifCustomer,
                        'transaction'=>[
                            "id"=> $transaction->id,
                            "notransaksi"=>$transaction->notransaksi,
                            "total_price"=>$transaction->total_price,
                            "driver_price"=>$transaction->driver_price,
                            "address_customer"=>$transaction->alamat_user,
                            "customer_name"=>$customer["name"],
                            "customer_phone"=>$customer["phone"],
                            "status"=>$transaction->status,
                            "latitude"=>$transaction->latitude,
                            "longitude"=>$transaction->longitude
                        ],
                        'store'=>[
                            "id_store"=>$store["id_store"],
                            "owner_name"=>$store["owner_name"],
                            "store_name"=>$store["store_name"],
                            "phone"=>$store["phone"],
                            "rating"=>$store["rating"],
                            "photo_store"=>$store["photo_store"],
                            "latitude"=>$store["latitude"],
                            "longititude"=>$store["longititude"],
                            "address"=>$store["address"],
                        ],
                        'detail_transaksi'=>$filterDetailTransaction

                    ],201);

                }
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'Gagal menerima order',
                ],404);
            }

        }

        if($status == $this->TRANSACTION_WAITING_DRIVER){
            return $this->statusFromStore($request,$id);
        }

    }

    public function driverTrans($id)
    {
        // return $id;
        $transaction = json_decode(Transaction::where('id_driver' ,$id)->where('status','!=',6)->first());

        // return response()->json([
        //     'success'=>true,
        //     'message'=>'success',
        //     'data' => 'data']);


        if($transaction) {
            $customer = json_decode($this->successResponse($this
                ->serviceCustomer
                ->getCustomer($transaction->id_customer))
                ->original,true)["data"];

            $store =json_decode($this->successResponse($this
                ->storeService
                ->getStore($transaction->id_store))
                ->original,true)["data"];

            $detailTransaction =json_decode($this->successResponse($this
                ->detailTransactionService
                ->getNotransaksi($transaction->notransaksi,$transaction->id_store))
                ->original,true)["data"];

            $filterDetailTransaction = [];
            foreach ($detailTransaction as $key => $value) {
                array_push($filterDetailTransaction,[
                    "id_product"=>$value["id_product"],
                    "price_product"=>$value["price_product"],
                    "count"=>$value["count"],
                    "name_product"=>$value["name_product"],
                    "category"=>$value["category"],
                    "image1"=>$value["image1"],
                    "image2"=>$value["image2"],
                    "image3"=>$value["image3"],
                    "image4"=>$value["image4"],
                    "description"=>$value["description"]
                ]);
            }


            return response()->json([
                'success'=>true,
                'message'=>'success',
                // 'notifStore'=>$notifStore,
                // 'notifCustomer'=>$notifCustomer,
                'transaction'=>[
                    "id"=> $transaction->id,
                    "notransaksi"=>$transaction->notransaksi,
                    "total_price"=>$transaction->total_price,
                    "driver_price"=>$transaction->driver_price,
                    "address_customer"=>$transaction->alamat_user,
                    "customer_name"=>$customer["name"],
                    "customer_phone"=>$customer["phone"],
                    'status'=>$transaction->status,
                    "latitude"=>$transaction->latitude,
                    "longitude"=>$transaction->longitude
                ],
                'store'=>[
                    "id_store"=>$store["id_store"],
                    "owner_name"=>$store["owner_name"],
                    "store_name"=>$store["store_name"],
                    "phone"=>$store["phone"],
                    "rating"=>$store["rating"],
                    "photo_store"=>$store["photo_store"],
                    "latitude"=>$store["latitude"],
                    "longititude"=>$store["longititude"],
                    "address"=>$store["address"],
                ],
                'detail_transaksi'=>$filterDetailTransaction

            ],200);
        }else {
            return response()->json([
                'success'=>false,
                'message'=>'Transaksi sudah selesai'],404);
        }

    }

    public function getHistoryDriver($id)
    {
        $transaction = Transaction::where('id_driver' ,$id)->where('status', 6)->get();
        if ($transaction) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $transaction
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada history'
            ], 404);
        }
    }

    public function validationCodeFromDriver($id,$kode){
        $transaction = $transaction = json_decode(Transaction::whereId($id)->first());

        $detailTransaction =json_decode($this->successResponse($this
            ->detailTransactionService
            ->getNotransaksi($transaction->notransaksi,$transaction->id_store))
            ->original,true)["data"];


        $filterDetailTransaction = [];
        foreach ($detailTransaction as $key => $value) {
            array_push($filterDetailTransaction,[
                "id_product"=>$value["id_product"],
                "price_product"=>$value["price_product"],
                "count"=>$value["count"],
                "name_product"=>$value["name_product"],
                "category"=>$value["category"],
                "image1"=>$value["image1"],
                "image2"=>$value["image2"],
                "image3"=>$value["image3"],
                "image4"=>$value["image4"],
                "description"=>$value["description"]
            ]);
        }
        if($transaction){
            if($transaction->kode_validasi == $kode){
                $customer = json_decode($this->successResponse($this
                    ->serviceCustomer
                    ->getCustomer($transaction->id_customer))
                    ->original,true)["data"];
                Transaction::whereId($id)->update([
                    "status"=>$this->TRANSACTION_DRIVER_IN_STORE
                ]);
                $dataFcmCustomer = [
                    "title"=>"customer notification",
                    "content"=>"Driver sudah sampai ditoko"
                ];
                $transaction = $transaction = json_decode(Transaction::whereId($id)->first());
                $notifCustomer = $this->pushFcm($dataFcmCustomer,$customer["fcm"]);
                return response()->json([
                    'success'=>true,
                    'message'=>'success',
                    'transaction'=>[
                        "id"=> $transaction->id,
                        "notransaksi"=>$transaction->notransaksi,
                        "total_price"=>$transaction->total_price,
                        "driver_price"=>$transaction->driver_price,
                        "address_customer"=>$transaction->alamat_user,
                        "status"=>$transaction->status,
                        "customer_phone"=>$customer["phone"],
                        "customer_name"=>$customer["name"],
                        "latitude"=>$transaction->latitude,
                        "longitude"=>$transaction->longitude
                    ],
                    'detail_transaksi'=>$filterDetailTransaction
                ],200);
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'Kode yang anda masukkan salah!',
                ],404);
            }
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'Transaksi tidak ditemukan',
            ],404);
        }

    }

    public function transactionFinish($id){
        $transaction = $transaction = json_decode(Transaction::whereId($id)->first());


        if($transaction){
            $driver =json_decode($this->successResponse($this
                ->serviceDriver
                ->statusWork(1, $transaction->id_driver))
                ->original,true);

            $customer = json_decode($this->successResponse($this
                ->serviceCustomer
                ->getCustomer($transaction->id_customer))
                ->original,true)["data"];
            Transaction::whereId($id)->update([
                "status"=>$this->TRANSACTION_DONE
            ]);

            $dataFcmCustomer = [
                "title"=>"customer notification",
                "content"=>"Pesanan anda telah selesai terimakasih telah berbelanja"
            ];
            $notifCustomer = $this->pushFcm($dataFcmCustomer,$customer["fcm"]);
            return response()->json([
                'success'=>true,
                'message'=>'success',
                // 'notif'=>$notifCustomer
            ],200);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'Transaksi tidak ditemukan!',
            ],404);
        }
    }

    public function insert(Request $request)
    {
        $data = $request->getContent();
        $data = json_decode($data);
        $id_driver = $data->data->id_driver;
        $id_store = $data->data->id_toko;
        $status = $data->data->status;
        $driver_price = $data->data->driver_price;
        $address_user =  $data->data->alamat_user;
        $latitude = $data->data->latitude;
        $longitude = $data->data->longitude;
        $id_user = $data->data->id_user;
        $date = date_create();
        date_timestamp_set($date, time());
        $noTransaction = $id_user . date_format($date, "YmdHis");
        $data_product = $data->data->data_product;
        $arrayProduct = array();
        $code_validation = $this->generateRandomString();

        foreach ($data_product as $value) {
            $value = (array)$value;
            array_push($arrayProduct, $value);
        }

        $responseData = json_decode($this->successResponse($this
            ->productService
            ->getListFilterProduct($arrayProduct))
            ->original, true);

        $dataResponse = $responseData["data"];
        $total_price = 0;
        $dataSubProduct = array();


        foreach ($dataResponse as $value) {
            if ($value["price_promo"]) {
                $total_price += $value["price_promo"];
                array_push($dataSubProduct, array("notransaksi" => $noTransaction, "id_product" => $value["id"], "price_product" => $value["price_promo"]));
            } else {
                $total_price += $value["price"];
                array_push($dataSubProduct, ["notransaksi" => $noTransaction, "id_product" => $value["id"], "price_product" => $value["price"]]);
            }
        }

        $transaction = Transaction::create([
            'notransaksi' => $noTransaction,
            'id_user' => $id_user,
            'id_driver' => $id_driver,
            'id_toko' => $id_store,
            'status' => $status,
            'total_price' => $total_price,
            'driver_price' => $driver_price,
            'alamat_user' => $address_user,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status_delete' => 0,
            'kode_validasi' => $code_validation
        ]);

        if ($transaction) {

            // insert data detailTransaction
            $dataArray = array();
            foreach ($data_product as $value) {
                $data = (array) $value;
                $data["notransaksi"] = $noTransaction;
                array_push($dataArray, $data);
            }
            $responseDetailTransaction = json_decode($this->successResponse($this
                ->detailTransactionService
                ->insert($dataSubProduct))
                ->original, true);


            // fcm toko
            //get fcm store from service store
            $responseStore = json_decode($this->successResponse($this
                ->storeService
                ->getStore($id_store))
                ->original, true);
            $store = $responseStore["data"];
            $this->fcm($store["token_fcm"]);


            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed'
            ], 400);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');
        $kode_validasi = $request->input('kode_validasi');
        $id_driver = $request->input('id_driver');

        $transaksi = Transaction::whereId($id)->first();

        if ($kode_validasi) {
            if ($kode_validasi == $transaksi->kode_validasi && $id_driver == $transaksi->id_driver) {
                $transaksi->update([
                    "status" => $status,
                ]);

                if ($transaksi) {
                    return response()->json([
                        'success' => true,
                        'message' => 'status success update',
                    ], 201);
                } else {
                    return response()->json([
                        'success' => true,
                        'message' => 'status failed update',
                    ], 401);
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Kode validasi yang anda masukan salah',
                ], 401);
            }
        } else {
            $transaksi->update([
                "status" => $status
            ]);

            if ($transaksi) {
                return response()->json([
                    'success' => true,
                    'message' => 'status success update',
                ], 201);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'status failed update',
                ], 401);
            }
        }
    }

    public function fcm($data)
    {
        $curl = curl_init();
        $authKey = "key=AAAAC-0CIus:APA91bGZfiR7Q8hIO4W_gCTegqugpbiPnf8Ygnn72lyNtg1MoGt2Q3OkSNH_aOBefIjiEWcXl1VUbsLlWKAziWPBJiol_RBI1X2IDkfG9MY9YbR_wuHMO8FOTUFuSE-dYY8OjsLq6din";
        $registration_ids = '["' . $data . '"]';
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://fcm.googleapis.com/fcm/send",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => '{
                        "registration_ids": ' . $registration_ids . ',
                        "notification": {
                            "title": "Judul Notifikasi",
                            "body": "Isi Notifikasi"
                        }
                    }',
            CURLOPT_HTTPHEADER => array(
                "Authorization: " . $authKey,
                "Content-Type: application/json",
                "cache-control: no-cache"
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }
    }

    public function update(Request $request, $id)
    {
        $notransaksi = $request->input('notransaksi');
        $id_user = $request->input('id_user');
        $id_driver = $request->input('id_driver');
        $id_toko = $request->file('id_toko');
        $status = $request->input('status');
        $total_price = $request->file('total_price');
        $driver_price = $request->input('driver_price');
        $alamat_user = $request->input('alamat_user');
        $latitude = $request->input('latitude');
        $longitude = $request->input('longitude');
        $kode_validasi = $request->input('kode_validasi');


        $update = Transaction::whereId($id)->update([
            'notransaksi' => $notransaksi,
            'id_user' => $id_user,
            'id_driver' => $id_driver,
            'id_toko' => $id_toko,
            'status' => $status,
            'total_price' => $total_price,
            'driver_price' => $driver_price,
            'alamat_user' => $alamat_user,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'status_delete' => 0,
            'kode_validasi' => $kode_validasi
        ]);
    }

    public function getListTransactionStore(Request $request,$idStore)
    {
        $list = Transaction::whereIdStore($idStore)->get();


        if ($list) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $list
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found'
            ], 400);
        }
    }
}
