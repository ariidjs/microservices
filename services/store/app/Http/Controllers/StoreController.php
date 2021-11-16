<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Stores;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller;

class StoreController extends Controller
{
    public $DELETE = -1;
    public $ACTIVE = 1;
    public $PENDING = 0;

    public function insert(Request $request)
    {
        $owner_name = $request->input("owner_name");
        $store_name = $request->input("store_name");
        $phone = $request->input("phone");
        $email = $request->input("email");
        $fcm = $request->input("fcm");
        $description_store = $request->input("description_store");
        $nik_ktp = $request->input("nik_ktp");
        $photo_ktp = $request->input("photo_ktp");
        $photo_store = $request->input("photo_store");
        $latitude = $request->input("latitude");
        $longititude = $request->input("longititude");
        $address = $request->input("address");

        $phoneCheck=Stores::wherePhone($phone)->first();
        $emailCheck=Stores::whereEmail($email)->first();
        $nikCheck=Stores::where(["nik_ktp"=>$nik_ktp])->first();

        if($phoneCheck){
            return response()->json([
                'success' => false,
                'message' => 'nomor handphone yang anda masukan telah terdaftar'
            ], 401);
        }

        if($emailCheck){
            return response()->json([
                'success' => false,
                'message' => 'email yang anda masukan telah terdaftar'
            ], 401);
        }

        if($nikCheck){
            return response()->json([
                'success' => false,
                'message' => 'nik yang anda masukan telah terdaftar'
            ], 401);
        }


        $insert = Stores::create([
            "owner_name" => $owner_name,
            "store_name" => $store_name,
            "phone" => $phone,
            "email" => $email,
            "fcm" => $fcm,
            "description_store" => $description_store,
            "nik_ktp" => $nik_ktp,
            "photo_ktp" => $photo_ktp,
            "latitude" => $latitude,
            "longititude" => $longititude,
            "address" => $address,
            "photo_store" => $photo_store
        ]);

        if ($insert) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $insert
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Insert data failed'
            ], 401);
        }
    }

    public function updated(Request $request, $id)
    {
        $owner_name = $request->input("owner_name");
        $store_name = $request->input("store_name");
        $phone = $request->input("phone");
        $email = $request->input("email");
        $fcm = $request->input("fcm");
        $description_store = $request->input("description_store");
        $nik_ktp = $request->input("nik_ktp");
        $photo_ktp = $request->input("photo_ktp");
        $photo_store = $request->input("photo_store");
        $latitude = $request->input("latitude");
        $longititude = $request->input("longititude");
        $address = $request->input("address");

        $store = Stores::whereIdStore($id)->first()->toArray();



        if (isset($store)) {
            $store['owner_name'] = (isset($owner_name)) ? $owner_name : $store['owner_name'];
            $store['store_name'] = (isset($store_name)) ? $store_name : $store['store_name'];
            $store['phone'] = (isset($phone)) ? $phone : $store['phone'];
            $store['email'] = (isset($email)) ? $email : $store['email'];
            $store['fcm'] = (isset($fcm)) ? $fcm : $store['fcm'];
            $store['description_store'] = (isset($description_store)) ? $description_store : $store['description_store'];
            $store['nik_ktp'] = (isset($nik_ktp)) ? $nik_ktp : $store['nik_ktp'];
            $store['photo_ktp'] = (isset($photo_ktp)) ? $photo_ktp : $store['photo_ktp'];
            $store['photo_store'] = (isset($photo_store)) ? $photo_store : $store['photo_store'];
            $store['latitude'] = (isset($latitude)) ? $latitude : $store['latitude'];
            $store['longititude'] = (isset($longititude)) ? $longititude : $store['longititude'];
            $store['address'] = (isset($address)) ? $address : $store['address'];

            // remove array key
            unset($store['created_at'], $store['updated_at']);

            // return dd($store);
            $update = Stores::whereIdStore($id)->update($store);

            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                    'data' => $store
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'updated data failed',
                ], 401);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found',
            ], 404);
        }
    }

    public function phoneNumberAvailable($phone)
    {
        $checkPhone = Stores::wherePhone($phone)->first();

        if ($checkPhone) {
            return response()->json([
                'success' => true,
                'message' => 'phone is register',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'phone not register',

            ], 201);
        }
    }

    public function getStore($id)
    {
        $store = Stores::whereIdStore($id)->first();

        if ($store) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $store
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found',
            ], 404);
        }
    }

    public function banedStore($id)
    {
        $delete = Stores::whereId($id)->update(["status_delete" => $this->DELETE]);
        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'success banned store',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function auth(Request $request)
    {
        $fcm = $request->input("fcm");
        $aut = Stores::whereFcm($fcm)->first();

        if ($aut) {
            return response()->json([
                'success' => true,
                'message' => 'authorize success',
                'data' => $aut
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'authorized failed',
            ], 404);
        }
    }

    public function updatedSaldo($id, $saldo)
    {

        $store = Stores::whereId($id)->first();

        $update = Stores::whereId($id)->update([
            "saldo" => $store->$saldo + $saldo
        ]);

        if ($update) {
            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'updated failed',
            ], 404);
        }
    }

    public function login(Request $request, $phone)
    {

        $login = Stores::wherePhone($phone)->update([
            "fcm" => $request->input('fcm')
        ]);

        $data = Stores::wherePhone($phone)->first();
        if ($login) {
            return response()->json([
                'success' => true,
                'message' => 'login success',
                'data' => $data
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'login failed',
            ], 404);
        }
    }

    public function getListStore()
    {
        $store = Stores::all()->reject(function ($data) {
            return $data->status_delete === $this->DELETE || $data->status_delete === $this->PENDING;
        });
        if ($store) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $store
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not found',
            ], 404);
        }
    }

    public function getListStoreFromAdmin()
    {
        $store = Stores::all();
        if ($store) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $store
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not found',
            ], 404);
        }
    }

    public function active($id)
    {
        $active = Stores::whereId($id)->update(["status_delete" => $this->ACTIVE]);
        if ($active) {
            return response()->json([
                'success' => true,
                'message' => 'success active store',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function logOut($id)
    {
        $logout = Stores::whereId($id)->update(["fcm" => ""]);

        if ($logout) {
            return response()->json([
                'success' => true,
                'message' => 'logout success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'logout failed',
            ], 400);
        }
    }

    public function onOffStore($id, $status)
    {

        $result = Stores::whereIdStore($id)->update(["status_store" => $status]);
        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 401);
        }
    }

    public function ChangeStatusStore($id_store, $status)
    {
        if ($status == $this->ACTIVE) {
            $status = Stores::whereIdStore($id_store)->update(["status_delete" => $this->ACTIVE]);
        } else {
            $status = Stores::whereIdStore($id_store)->update(["status_delete" => $this->DELETE]);
        }

        if ($status) {
            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 401);
        }
    }
}
