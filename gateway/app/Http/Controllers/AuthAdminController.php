<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceAdmin;
use App\Services\ServiceAdmin;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;

class AuthAdminController extends BaseController
{
    use ApiResponser;
    private $serviceAdmin;
    private $authServiceAdmin;
    public function __construct(ServiceAdmin $serviceAdmin,AuthServiceAdmin $authServiceAdmin)
    {
        $this->serviceAdmin = $serviceAdmin;
        $this->authServiceAdmin = $authServiceAdmin;
    } 

    public function register(Request $request){
        $name= $request->input('name');
        $username=$request->input('username');
        $email=$request->input('email');
        $password=$request->input('password');
        $role =$request->input('role');
        $avatar=$request->file('avatar');

        if($avatar){
            $photoName= time().$avatar->getClientOriginalName();
        }else{
            $photoName = 'default.png';
        }

        $body = [
            'name'=>$name,
            'username'=>$username,
            'email'=>$email,
            'password'=>$password,
            'avatar'=>$photoName,
            'role'=>$role
        ];

          $response = json_decode($this->successResponse($this
            ->serviceAdmin
            ->register($body))
            ->original,true);
        
        if($response["success"]){
            if($avatar){
                $avatar->move('images',$photoName);
            } 
            return $response;
        }
    }

    public function login(Request $request){
        $username = $request->input("username");
        $password = $request->input("password");

        $body = [
            "username"=>$username,
            "password"=>$password
        ];

       return  json_decode($this->successResponse($this
            ->authServiceAdmin
            ->login($body))
            ->original,true);
    }
}
