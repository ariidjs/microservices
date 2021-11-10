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
        $image = $request->file('name');
        $address = $request->input('address');



        if ($image) {
            $avatar = time() . $image->getClientOriginalName();
            $image->move('images', $avatar);
        } else {
            $avatar = 'default.png';
        }

        if ($image) {
            $update = Customers::whereId($id)->update([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "image" => $avatar,
                "address" => $address,
            ]);
        } else {
            $update = Customers::whereId($id)->update([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "address" => $address,
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
        $login = Customers::wherePhone($phone)->update([
            "fcm" => $request->input('fcm')
        ]);

        $data = Customers::wherePhone($phone)->first();
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

    public function auth(Request $request)
    {
        $fcm = $request->input("fcm");
        $aut = Customers::whereFcm($fcm)->first();

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
}
