<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Services\FcmService;
use App\Services\ServiceCustomer;
use App\Traits\ApiResponser;
use \Illuminate\Http\Request;
class PromoController extends Controller
{
    use ApiResponser;
    private $ServiceCustomer;
    private $fcmService;
    private $AUTHKEYFCM = "key=AAAAC-0CIus:APA91bGZfiR7Q8hIO4W_gCTegqugpbiPnf8Ygnn72lyNtg1MoGt2Q3OkSNH_aOBefIjiEWcXl1VUbsLlWKAziWPBJiol_RBI1X2IDkfG9MY9YbR_wuHMO8FOTUFuSE-dYY8OjsLq6din";
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ServiceCustomer $serviceCustomer,FcmService $fcmService)
    {
        //
        $this->serviceCustomer = $serviceCustomer;
        $this->fcmService = $fcmService;
    }

    //

    public function insert(Request $request)
    {
        $data = $request->only([
            'idCustomer', 'promoName','promoDescription','promoPrice','date','expired'
        ]);

        $fcm = $request->input("fcm");



        // return $data;
        $data['date'] = date('Y-m-d');

        $insert = Promo::create($data);

        if ($insert) {
            $dataFcmCustomer = [
                "title" => "Ada promo menarik buat kamu",
                "content"=>[
                    "title" => "Promo ".$data["promoName"],
                ],
            ];
           $this->pushFcm($dataFcmCustomer, $fcm);
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $insert
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed'
            ], 400);
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

    public function getData(){

        $customer = json_decode($this->successResponse($this
        ->serviceCustomer
        ->getLisCustomer())
        ->original,true);

        // return dd($customer);

        $data = Promo::all();

        $infoCustomer = $this->inner_join($customer['data'],$data->toArray());

        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $infoCustomer
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed'
            ], 400);
        }

    }



    public function getPromo($id){
        $promo = Promo::whereId($id)->first();
        if ($promo) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $promo
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'promo tidak ditemukan',
            ], 400);
        }


    }

    public function getPromoCustomer($id){

        $promo = Promo::whereIdcustomer($id)
        ->whereStatus('unused')
        ->get();

        if($promo){
            $filter = [];
            foreach($promo as $value ){
                $date = explode("/",$value["expired"]);
                $date =$date[2].$date[1].$date[0];
                $date2 = explode("/",date("m/d/Y"));
                $date2 = $date2[2].$date2[1].$date2[0];
                if($date  > $date2 ){
                    array_push($filter,$value);
                }
            }
            return response()->json([
                "status"=>true,
                "message"=>"success",
                "data"=>$filter
            ],201);
        }else{
            return response()->json([
                "status"=>false,
                "message"=>"not found",
            ],401);
        }
    }


    public function update(Request $request, $id)
    {
        $data = $request->only([
            'idCustomer', 'promoName','promoDescription','promoPrice','date','expired','status'
        ]);
        $update = Promo::where($id)->update($data);

        if ($update) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $update
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed'
            ], 400);
        }
    }

    public function updateStatus($id){
        $update = Promo::where($id)->update([
            "status" => "Used"
        ]);

        if($update){
            return response()->json([
                "success"=>true,
                "message"=>"success updated data"
            ],201);
        }else{
            return response()->json([
                "success"=>false,
                "message"=>"failed updated data"
            ],201);
        }
    }

    function inner_join(array $left, array $right)
    {
        $out = array();
        foreach ($left as $left_record) {
            foreach ($right as $right_record) {
                if ($left_record['id'] == $right_record['idCustomer']) {
                    $out[] = array_merge($left_record, $right_record);
                }
            }
        }
        return $out;
    }
}
