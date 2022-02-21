<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use App\Models\Customers;
use \Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Exceptions\Handler;
use Exception;
use Illuminate\Database\QueryException;
use Symfony\Component\HttpKernel\Exception\HttpException;

class CustomerController extends BaseController
{
    private  $DELETE = 1;
    private  $ACTIVE = 0;
    public function insert(Request $request)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $image = $request->input('avatar');
        $address = $request->input('address');
        $fcm = $request->input('fcm');

        if (!$address) {
            $address = " ";
        }

        $emailCheck = Customers::whereEmail($email)->first();
        $phoneCheck = Customers::whereEmail($phone)->first();


        if ($emailCheck) {
            return response()->json([
                'success' => false,
                'message' => 'email telah terdaftar',
            ], 401);
        }

        if ($phoneCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Phone telah terdaftar',
            ], 401);
        }


        $insert = Customers::create([
            "name" => $name,
            "email" => $email,
            "phone" => $phone,
            "image" => $image,
            "address" => $address,
        ]);

        if ($insert) {
            $insert->update([
                "fcm" => $fcm
            ]);
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $insert
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Insert data failed',
            ], 401);
        }
    }

    public function phoneNumberAvailable($phone)
    {
        $checkPhone = Customers::wherePhone($phone)->first();

        if ($checkPhone) {
            return response()->json([
                'success' => true,
                'message' => 'phone is register',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'phone not register',
            ], 404);
        }
    }

    public function getCustomer($id)
    {

        $customer = Customers::whereId($id)->first();


        if ($customer) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $customer
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data customer not found',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $image = $request->input('image');
        $address = $request->input('address');
        $level = $request->input('level');


        if ($image) {
            $update = Customers::whereId($id)->update([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "image" => $image,
                "address" => $address,
                "level" => $level,
            ]);
        } else {
            $update = Customers::whereId($id)->update([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "address" => $address,
                "level" => $level,
            ]);
        }
        if ($update) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $update
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Kode validasi yang anda masukan salah',
            ], 401);
        }
    }

    public function updateCustomer(Request $request, $id)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $address = $request->input('address');

        $update = Customers::whereId($id)->update([
            "name" => $name,
            "email" => $email,
            "address" => $address
        ]);
        if ($update) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $update
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'update data gagal',
            ], 401);
        }
    }

    public function delete($id)
    {
        $delete = Customers::whereId($id)->update(["status_delete" => $this->DELETE]);
        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'success delete',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function active($id)
    {
        $delete = Customers::whereId($id)->update(["status_delete" => $this->ACTIVE]);
        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'success active customer',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function login(Request $request, $phone)
    {
        $data = Customers::wherePhone($phone)->first();


        if ($data) {
            $info = $data;
            Customers::wherePhone($phone)->update([
                "fcm" => $request->input('fcm')
            ]);
            return response()->json([
                'success' => true,
                'message' => 'login success',
                'data' => $info
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'login failed',
            ], 404);
        }
    }

    public function auth(Request $request)
    {
        $fcm = $request->input("fcm");
        $id = $request->input("id");
        $auth = Customers::whereFcm($fcm)->whereId($id)->first();


        if ($auth) {
            return response()->json([
                'success' => true,
                'message' => 'authorize success',
                'data' => $auth
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'authorized failed',
            ], 404);
        }
    }

    public function getListCustomer()
    {
        $customer = Customers::all();

        if ($customer) {
            return response()->json([
                'success' => true,
                'message' => 'Success',
                'data' => $customer
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found'
            ], 400);
        }
    }

    public function countCustomer(){
        $count = Customers::count();

        return response()->json([
            'success' => true,
            'message' => 'login success',
            'data' => $count
        ], 201);
    }

    public function updateImageCustomer(Request $request,$id){
        $image = $request->input("image");

        $update = Customers::whereId($id)->update([
            'image'=>$image
        ]);

        if($update){
            return response()->json([
                'success' => true,
                'message' => 'success'
            ], 201);
        }else{
            return response()->json([
                'success' => false,
                'message' => 'failed'
            ], 401);
        }
    }

}
