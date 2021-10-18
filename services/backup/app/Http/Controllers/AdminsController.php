<?php

namespace App\Http\Controllers;

use App\Models\Admins;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class AdminsController extends BaseController
{
      private $DELETE = -1;
    
    public function insert(Request $request){
        $name= $request->input('names');
        $username=$request->input('username');
        $email=$request->input('email');
        $password=$request->input('password');
        $avatar=$request->file('avatar');

        if($avatar){
            $photoName= time().$avatar->getClientOriginalName();
            $avatar->move('../../gateway/public/images',$photoName);
        }else{
            $photoName = 'default.png';
        }

        $insert = Admins::create([
            'name'=>$name,
            'username'=>$username,
            'email'=>$email,
            'password'=>Hash::make($password),
            'avatar'=>$photoName,
        ]);

        if($insert){
          return response()->json([
                        'success'=>true,
                        'message'=>'success',
                        'data'=> $insert
                    ],201);
        }else{
             return response()->json([
                        'success'=>false,
                        'message'=>'failed'
            ],400);
        }

    }   

     public function updated(Request $request,$id){
        $name=$request->input('name');
        $role=$request->input('role');
        $password=$request->input('password');
        $avatar=$request->file('avatar');

        if($avatar){
            $photoName= time().$avatar->getClientOriginalName();
            $avatar->move('../../gateway/public/images',$photoName);
            $update = Admins::create([
                'name'=>$name,
                'role'=>$role,
                'password'=>$password,
                'avatar'=>$photoName,
            ]);
        }else{
            $update = Admins::create([
                'name'=>$name,
                'role'=>$role,
                'password'=>$password,
            ]);
        }

   

        if($update){
          return response()->json([
                        'success'=>true,
                        'message'=>'success',
                        'data'=> $update
                    ],201);
        }else{
             return response()->json([
                        'success'=>false,
                        'message'=>'failed'
            ],400);
        }
    }   

    public function delete($id){
        $delete = Admins::whereId($id)->update([
            "status_delete"=>$this->DELETE
        ]);
    }
}
