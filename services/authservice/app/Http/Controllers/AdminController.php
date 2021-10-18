<?php

namespace App\Http\Controllers;

use App\Services\AdminServices;


use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;


class AdminController extends BaseController
{
    use ApiResponser;
    public $adminServices;
    public function __construct(AdminServices $adminServices)
    {
        $this->adminServices =$adminServices;
    } 

   
    public function login(Request $request){
        $email = $request->input("email");
        $password = $request->input("password");


        $body = [
            "email"=>$email,
            "password"=>$password,
        ];

         return json_decode($this->successResponse($this
            ->adminServices
            ->login($body))
            ->original,true);
    }


    // public function checkPhone($phone){
    //     return json_decode($this->successResponse($this
    //         ->customerService
    //         ->checkPhone($phone))
    //         ->original,true);
    // }




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
