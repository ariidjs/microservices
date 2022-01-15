<?php

namespace App\Http\Controllers;

use App\Models\Admins;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use Firebase\JWT\JWT;

class AdminsController extends BaseController
{
    private $DELETE = -1;
    private  $ACTIVE = 0;
    private $TIME_EXPIRE = 3;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    public function insert(Request $request)
    {
        $name = $request->input('name');
        $username = $request->input('username');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        $avatar = $request->input('avatar');



        $insert = Admins::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => Hash::make($password),
            'avatar' => $avatar,
            'role' => $role
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
                'message' => 'failed'
            ], 400);
        }
    }

    public function updatePassword(Request $request, $id){
        $user = Admins::whereId($id)->first();
        $oldPassword = $request->input('oldPassword');
        $newPassword = $request->input('newPassword');

        if($user){
            if(Hash::check($oldPassword,$user->password)){
                $update = Admins::whereId($id)->update([
                    "password"=>Hash::make($newPassword)
                ]);
                if($update){
                    return response()->json([
                        "success" => true,
                        "message" => "Success update password"
                    ],201);
                }else{
                    return response()->json([
                        "success" => false,
                        "message" => "Failed update password"
                    ],201);
                }
            }else{
                return response()->json([
                    "success" => false,
                    "message" => "password lama yang anda masukan salah"
                ],201);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'user not found',
            ], 401);
        }
    }


    public function update(Request $request, $id)
    {
        $name = $request->input('names');
        $role = $request->input('role');
        $password = $request->input('password');
        $avatar = $request->file('avatar');

        if ($avatar) {
            $photoName = time() . $avatar->getClientOriginalName();
            $avatar->move('../../gateway/public/images', $photoName);
            $update = Admins::whereId($id)->update([
                'name' => $name,
                'role' => $role,
                'password' => Hash::make($password),
                'avatar' => $photoName,
            ]);
        } else {
            $update = Admins::whereId($id)->update([
                'name' => $name,
                'role' => $role,
                'password' => Hash::make($password),
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
                'message' => 'failed'
            ], 400);
        }
    }

    public function banedAdmin($id)
    {
        $delete = Admins::whereId($id)->update([
            "status_delete" => $this->DELETE
        ]);

        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'banned success',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 400);
        }
    }

    public function active($id)
    {
        $delete = Admins::whereId($id)->update(["status_delete" => $this->ACTIVE]);
        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'success active admin',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        $data = Admins::whereUsername($username)->first();

        if ($data) {
            if (Hash::check($password, $data->password)) {
                return response()->json([
                    'success' => true,
                    'message' => 'login success',
                    'data' => $data,
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'password yang anda masukan salah',
                ], 400);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Email atau username yang anda masukan tidak tersedia',
            ], 400);
        }
    }
}
