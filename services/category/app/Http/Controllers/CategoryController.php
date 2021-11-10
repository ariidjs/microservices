<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \Illuminate\Support\Facades\Hash;
use \App\Models\Category;
use \Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth',['except'=>['getStore,changePassword']]);
    }

    public function update(Request $request,$id){
    $category = $request->input('category');

    $update = Category::whereId($id)->update([
        'category'=>$category
    ]);

    if($update){
        return response()->json([
            'success'=>true,
            'message'=>'update Sukses',
            'data'=>[
                "user"=>$update
            ]
        ],201);
    }else{
        return response()->json([
            'success'=>false,
            'message'=>'update failed'
        ],401);
    }
}

    public function insert(Request $request){
        $category = $request->input('category');
        $insert = Category::create([
            'category'=>$category
        ]);
        if($insert){
            return response()->json([
                'success'=>true,
                'message'=>'insert Sukses',
                'data'=>$insert
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'failed '
            ],401);
        }
    }
}
