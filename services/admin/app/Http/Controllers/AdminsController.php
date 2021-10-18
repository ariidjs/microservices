<?php

namespace App\Http\Controllers;

use App\Models\Admins;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;

class AdminsController extends BaseController
{
      private $DELETE = -1;
      private  $ACTIVE = 0;
    
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
     
    
    public function update(Request $request,$id){
        $name=$request->input('names');
        $role=$request->input('role');
        $password=$request->input('password');
        $avatar=$request->file('avatar');

        if($avatar){
            $photoName= time().$avatar->getClientOriginalName();
            $avatar->move('../../gateway/public/images',$photoName);
            $update = Admins::whereId($id)->update([
                'name'=>$name,
                'role'=>$role,
                'password'=>Hash::make($password),
                'avatar'=>$photoName,
            ]);
        }else{
            $update = Admins::whereId($id)->update([
                'name'=>$name,
                'role'=>$role,
                'password'=>Hash::make($password),
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

    public function banedAdmin($id){
        $delete = Admins::whereId($id)->update([
            "status_delete"=>$this->DELETE
        ]);

        if($delete){
         return response()->json([
                    'success'=>true,
                    'message'=>'banned success',
                ],200);
        }else{
             return response()->json([
                    'success'=>false,
                    'message'=>'failed',
                ],400);
        }
    }

    public function active($id){
        $delete = Admins::whereId($id)->update(["status_delete"=>$this->ACTIVE]);
        if($delete){
                return response()->json([
                    'success'=>true,
                    'message'=>'success active admin',
                ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'delete failed',
            ],401);
        }
    }

    public function login(Request $request){

        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);



        // $emailOrPassword = $request->input('email');
        // $password = $request->input('password');

        // $data = Admins::whereEmail($emailOrPassword)->first();
        // if(!$data){
        //       $data = Admins::whereUsername($emailOrPassword)->first();
        // }
    
        
        // if($data){
        //     if(Hash::check($password, $data->password)){
        //     $token = auth()->attempt(["email"=>"admin","password"=>Hash::check($password, $data->password)]);
        //     return $this->respondWithToken($token);

        //         //   return response()->json([
        //         //     'success'=>true,
        //         //     'message'=>'login success',
        //         //     'data'=>$data
        //         // ],200);
        //     }else{
        //           return response()->json([
        //             'success'=>false,
        //             'message'=>'password yang anda masukan salah',
        //         ],400);
        //     }
        // }else{
        //         return response()->json([
        //             'success'=>false,
        //             'message'=>'Email atau username yang anda masukan tidak tersedia',
        //         ],400);
        // }

    }

      /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(auth()->user());
    }

        /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60
        ]);
    }
}
