<?php

namespace App\Http\Controllers;


use App\Services\ProductService;
use App\Services\DetailTransactionService;
use App\Services\StoreService;
use \Illuminate\Http\Request;
use \App\Models\Rating;
use App\Services\FcmService;
use App\Services\ServiceBenefit;
use App\Services\ServiceCustomer;
use App\Services\ServiceDriver;
use App\Services\ServiceManagement;
use App\Services\ServicePromo;
use \App\Traits\ApiResponser;
use DateTime;
use Illuminate\Support\Facades\DB;
use Kreait\Firebase\Database;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Factory;
use Laravel\Lumen\Routing\Controller;
use PhpParser\Node\Stmt\TryCatch;
use Throwable;

class RatingController extends Controller
{
    use ApiResponser;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $productService;
    public $servicePromo;
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
    private $serviceBenefits;
    private $configFirebase;
    private $databaseFirebase;
    private $serviceCustomer;
    private $serviceDriver;
    private $serviceManagement;
    private $AUTHKEYFCM = "key=AAAAC-0CIus:APA91bGZfiR7Q8hIO4W_gCTegqugpbiPnf8Ygnn72lyNtg1MoGt2Q3OkSNH_aOBefIjiEWcXl1VUbsLlWKAziWPBJiol_RBI1X2IDkfG9MY9YbR_wuHMO8FOTUFuSE-dYY8OjsLq6din";


    public function __construct(
        ProductService $productService,
        StoreService $storeService,
        DetailTransactionService $detailTransactionService,
        FcmService $fcmService,
        ServiceCustomer $serviceCustomer,
        ServiceDriver $serviceDriver,
        ServiceManagement $serviceManagement,
        ServiceBenefit $serviceBenefit,
        ServicePromo $servicePromo
    ) {
        // $factory = (new Factory)->withServiceAccount('../../../config/firebaseConfig.json');
        // $this->configFirebase = $factory;
        $this->productService = $productService;
        $this->storeService = $storeService;
        $this->detailTransactionService = $detailTransactionService;
        $this->fcmService = $fcmService;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceDriver = $serviceDriver;
        $this->serviceManagement = $serviceManagement;
        $this->serviceBenefits = $serviceBenefit;
        $this->servicePromo = $servicePromo;
        $factory = (new Factory)
            ->withServiceAccount(__DIR__ . '/firebaseKey.json')
            ->withDatabaseUri('https://proyek-akhir-1b6f2-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->auth = $factory->createAuth();
        $this->databaseFirebase = $factory->createDatabase();
        // $this->databaseFirebase = $databaseFirebase;
    }

    public function insert(Request $request){
        // return "hello";
        $id_driver = $request->input("id_driver");
        $id_customer = $request->input("id_customer");
        $rating = $request->input("rating");

        $data = [
            "id_driver" => $id_driver,
            "rating" => $rating,
            "id_customer" => $id_customer
        ];

        $driver = json_decode($this->successResponse($this
                ->serviceDriver
                ->updateRatingDriver($data))
                ->original, true);
        if($driver["success"]){
            $response = Rating::create($data);

            if($response){
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                ], 201);
            }else{
                return response()->json([
                    'success' => false,
                    'message' => 'Failed',
                ], 401);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 401);
        }

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




}
