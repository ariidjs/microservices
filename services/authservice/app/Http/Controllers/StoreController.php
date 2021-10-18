<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;
use App\Services\CustomerServices;
use App\Services\StoreServices;
use App\Services\DriverServices;

use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use GrahamCampbell\ResultType\Success;

class StoreController extends BaseController
{
    use ApiResponser;
    public $storeService;
    public $driverServices;
    public $customerService;
    public $adminServices;
    public function __construct(StoreServices $storeService,DriverServices $driverServices,CustomerServices $customerService,AdminServices $adminServices )
    {
        $this->storeService = $storeService;
        $this->driverServices = $driverServices;
        $this->adminServices = $adminServices;
        $this->customerService =$customerService;
    } 

    public function authStore(Request $request){
        $fcm = $request->input("fcm");

        $body = [
            "fcm"=>$fcm
        ];

         return json_decode($this->successResponse($this
            ->storeService
            ->auth($body))
            ->original,true);
    }

    public function authAdmin(Request $request){
        $fcm = $request->input("fcm");

        $body = [
            "fcm"=>$fcm
        ];

         return json_decode($this->successResponse($this
            ->adminServices
            ->auth($body))
            ->original,true);
    }

    public function authCustomer(Request $request){
        $fcm = $request->input("fcm");

        $body = [
            "fcm"=>$fcm
        ];

         return json_decode($this->successResponse($this
            ->customerService
            ->auth($body))
            ->original,true);
    }

    public function authDriver(Request $request){
        $fcm = $request->input("fcm");
        $body = [
            "fcm"=>$fcm
        ];
         return json_decode($this->successResponse($this
            ->driverServices
            ->auth($body))
            ->original,true);
    }

    public function checkPhone($phone){
        return json_decode($this->successResponse($this
            ->storeService
            ->checkPhone($phone))
            ->original,true);
    }

    public function login(Request $request,$phone){
        $body = [
            'fcm'=>$request->input('fcm')
        ];
          return json_decode($this->successResponse($this
            ->storeService
            ->login($phone,$body))
            ->original,true);
    }



    // public function register(Request $request){
    //     $owner_name = $request->input("owner_name");
    //     $store_name = $request->input("store_name");
    //     $phone = $request->input("phone");
    //     $email = $request->input("email");
    //     $fcm = $request->input("fcm");
    //     $description_store = $request->input("description_store");
    //     $nik_ktp = $request->input("nik_ktp");
    //     $photo_ktp = $request->file("photo_ktp");
    //     $photo_store = $request->file("photo_store");
    //     $latitude = $request->input("latitude");
    //     $longititude = $request->input("longititude");
    //     $address = $request->input("address");
       

    //     $body = [
    //          "owner_name" => $owner_name,
    //         "store_name" => $store_name,
    //         "phone" => $phone,
    //         "email" => $email,
    //         "fcm" => $fcm,
    //         "description_store" => $description_store,
    //         "nik_ktp" => $nik_ktp,
    //         "photo_ktp" => $ktp,
    //         "latitude" => $latitude,
    //         "longititude" => $longititude,
    //         "address" => $address,
    //         "photo_store"=>$store
    //     ];
    //      return json_decode($this->successResponse($this
    //         ->driverServices
    //         ->auth($body))
    //         ->original,true);
    // }
}
