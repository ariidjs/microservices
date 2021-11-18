<?php

namespace App\Http\Controllers;

use App\Models\Saldo;
use \Illuminate\Http\Request;
use \Illuminate\Support\Facades\Hash;

use App\Models\SaldoStore;
use \Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;

use function PHPSTORM_META\type;

class SaldoController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    private $WITHDRAW = "withdraw";
    private $DEPOSIT = "deposit";

    // 1 deposit 2 withdraw

    public function insert(Request $request){
        $id_driver = $request->input('id_driver');
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $type = $request->input('type');
        $image= $request->input('image');
        $namabank = $request->input('nama_bank');
        $nama = $request->input('nama');


        if($type == 1){
            $type = $this->DEPOSIT;
        }else{
            $type = $this->WITHDRAW;
        }

        $insert = Saldo::create([
            'id_driver'=>$id_driver,
            'norek'=>$norek,
            'saldo'=>$saldo,
            'type'=>$type,
            'namabank'=>$namabank,
            'image'=>$image,
            'nama'=>$nama
        ]);

        if($insert){
            return response()->json([
                'success'=>true,
                'message'=>'success',
                'data'=>$insert
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'gagal menambahkan data'
            ],400);
        }
    }

//     public function update(Request $request,$id){
//         $id_store = $request->input('id_store');
//         $name_product = $request->input('name_product');
//         $category = $request->input('category');
//         $price = $request->input('price');
//         $price_promo = $request->input('price_promo');
//         $image1 = $request->file('image1');
//         $image2 = $request->file('image2');
//         $image3 = $request->file('image3');
//         $image4 = $request->file('image4');
//         $description = $request->input('description');
//         $status_delete = $request->input('status_delete');

//         if($image1){
//             $fotoProduct1 =time().$image1->getClientOriginalName();
//             $image1->move('images',$fotoProduct1);
//         }else{
//             $fotoProduct1 = '';
//         }

//         if($image2){
//             $fotoProduct2 =time().$image2->getClientOriginalName();
//             $image2->move('images',$fotoProduct2);
//         }else{
//             $fotoProduct2 = '';
//         }

//         if($image3){
//             $fotoProduct3 = time().$image3->getClientOriginalName();
//             $image3->move('images',$fotoProduct3);
//         }else{
//             $fotoProduct3 = '';
//         }

//         if($image4){
//             $fotoProduct4 = time().$image4->getClientOriginalName();
//             $image4->move('images',$fotoProduct4);
//         }else{
//             $fotoProduct4 = '';
//         }

//         $update = Product::whereId($id)->update([
//             'id_store'=>$id_store,
//             'name_product'=>$name_product,
//             'category'=>$category,
//             'price'=>$price,
//             'price_promo'=>$price_promo,
//             'image1'=>$fotoProduct1,
//             'image2'=>$fotoProduct2,
//             'image3'=>$fotoProduct3,
//             'image4'=>$fotoProduct4,
//             'description'=>$description,
//             'status_delete'=>$status_delete,
//         ]);

//         if($update){
//             return response()->json([
//                 'success'=>true,
//                 'message'=>'update Sukses',
//             ],201);
//         }else{
//             return response()->json([
//                 'success'=>false,
//                 'message'=>'update failed'
//             ],401);
//         }

//     }

//     public function delete($id){
//         $product = Product::find($id);
//         if($product){
//             $delete = Product::whereId($id)->update([
//                 'status_delete'     => 1,
//             ]);

//             if($delete){
//                 return response()->json([
//                     'success'=>true,
//                     'message'=>'data sukses di delete',
//                 ],201);
//             }else{
//                 return response()->json([
//                     'success'=>false,
//                     'message'=>'data gagal di delete',
//                 ],401);
//             }
//         }else{
//             return response()->json([
//                 'success'=>false,
//                 'message'=>'data yang ingin anda delete tidak tersedia',
//             ],401);
//         }
//     }

//     public  function getListProduct(Request $request){
//         $product = Product::all();
//         $product = json_decode($product);

// //        return $request->getContent();
//         $filterIdProduct = array_column(json_decode($request->getContent()),"id_product");
//         $arrayData = array();
//         foreach ($product as $value){
//             $value = (array)$value;
//             array_push($arrayData,$value);
//         }

//         $resultFilterProduct = array_filter($arrayData,function ($value)use($filterIdProduct){
//            foreach ($filterIdProduct as $filter){
//                if($filter == $value['id']){
//                    return $value;
//                }
//            }
//         });


//         if ($resultFilterProduct){
//             return response()->json([
//                 'success'=>true,
//                 'message'=>'data success',
//                 'data'=>$resultFilterProduct
//             ],201);
//         }else{
//             return response()->json([
//                 'success'=>false,
//                 'message'=>'data list product not found',
//             ],404);
//         }
//     }

    public function getRiwayatSaldo($id){
        $saldo =  Saldo::where('id_driver', $id)
            ->get();

        if($saldo){
            return response()->json([
                'success'=>true,
                'message'=>'success',
                'data'=>$saldo
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'data saldo not found',
            ],404);
        }

    }

//     public function getProductStore($id){
//         $product =  Product::where('id_store', $id)
//         ->where('status_delete',0)
//         ->get();

//         if($product){
//             return response()->json([
//                 'success'=>true,
//                 'message'=>'success',
//                 'data'=>$product
//             ],201);
//         }else{
//             return response()->json([
//                 'success'=>true,
//                 'message'=>'data product store not found'
//             ],404);
//         }
//     }

}
