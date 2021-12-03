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
        $this->adminServices = $adminServices;
    }


    public function login(Request $request)
    {
        $username = $request->input("username");
        $password = $request->input("password");
        $body = [
            "username" => $username,
            "password" => $password,
        ];

        // return response()->json([
        //     'success' => false,
        //     'message' => 'Email yang anda masukan telah terdaftar',
        //     'data'=>$password
        // ], 201);



        return json_decode($this->successResponse($this
            ->adminServices
            ->login($body))
            ->original, true);
    }
}
