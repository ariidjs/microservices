<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \Illuminate\Support\Facades\Hash;
use \App\Models\Product;
use App\Services\ServiceStore;
use App\Traits\ApiResponser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use \Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;
use PhpParser\Node\Stmt\Foreach_;

class ProductController extends Controller
{
    use ApiResponser;

      private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
      private $serviceStore;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ServiceStore $serviceStore)
    {
        $this->serviceStore = $serviceStore;
    }

    public function insert(Request $request){
        $name_product = $request->input('name_product');
        $category = $request->input('category');
        $price = $request->input('price');
        $price_promo = $request->input('price_promo');
        $image1 = $request->input('image1');
        $image2 = $request->input('image2');
        $image3 = $request->input('image3');
        $image4 = $request->input('image4');
        $description = $request->input('description');
        $jwt = $request->input('jwt');
        $status_delete = 0;


        Try{
            $store =JWT::decode($jwt,$this->key,array('HS256'));
            $insert = Product::create([
            'id_store'=>$store->id,
            'name_product'=>$name_product,
            'category'=>$category,
            'price'=>$price,
            'price_promo'=>$price_promo,
            'image1'=>$image1,
            'image2'=>$image2,
            'image3'=>$image3,
            'image4'=>$image4,
            'description'=>$description,
            'status_delete'=>$status_delete,
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
        }catch(ExpiredException $ex){
            return response()->json([
                'success'=>false,
                'message'=>'jwt failed'
            ],400);
        }

        


        
    }

    public function update(Request $request,$id){

        $id_store = $request->input('id_store');
        $name_product = $request->input('name_product');
        $category = $request->input('category');
        $price = $request->input('price');
        $price_promo = $request->input('price_promo');
        $image1 = $request->input('image1');
        $image2 = $request->input('image2');
        $image3 = $request->input('image3');
        $image4 = $request->input('image4');
        $description = $request->input('description');
        $status_delete = $request->input('status_delete');


        $update = Product::whereId($id)->update([
            'id_store'=>$id_store,
            'name_product'=>$name_product,
            'category'=>$category,
            'price'=>$price,
            'price_promo'=>$price_promo,
            'image1'=>$image1,
            'image2'=>$image2,
            'image3'=>$image3,
            'image4'=>$image4,
            'description'=>$description,
            'status_delete'=>$status_delete,
        ]);

        if($update){
            return response()->json([
                'success'=>true,
                'message'=>'update Sukses',
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'update failed'
            ],401);
        }

    }

    public function delete($id){
        $product = Product::find($id);
        if($product){
            $delete = Product::whereId($id)->update([
                'status_delete'     => 1,
            ]);

            if($delete){
                return response()->json([
                    'success'=>true,
                    'message'=>'data sukses di delete',
                ],201);
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'data gagal di delete',
                ],401);
            }
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'data yang ingin anda delete tidak tersedia',
            ],401);
        }
    }

     function left_join_array($left, $right, $left_join_on, $right_join_on = NULL){
            $final= array();

            if(empty($right_join_on))
                $right_join_on = $left_join_on;

            foreach($left AS $k => $v){
                $final[$k] = $v;
                foreach($right AS $kk => $vv){
                    if($v[$left_join_on] == $vv[$right_join_on]){
                        foreach($vv AS $key => $val)
                            $final[$k][$key] = $val; 
                    } else {
                        foreach($vv AS $key => $val)
                            $final[$k][$key] = NULL;            
                    }
                }
            }
        return $final;
    }

    function inner_join(array $left, array $right, $on) {
        $out = array();
        foreach ($left as $left_record) {
            foreach ($right as $right_record) {
            if ($left_record[$on] == $right_record[$on]) {
                $out[] = array_merge($left_record, $right_record);
            }
            }
        }
        return $out;
    }

    public  function getListProduct(){
        $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getListStore())
            ->original,true);
        $product = Product::all();
        $product = json_decode($product);
        $productUniq = array_intersect_key($product, array_unique(array_map(function ($el) {
            return $el->id_store;
        }, $product)));
        $productUniq = json_decode(json_encode($productUniq),true);

       $storeProduct = $this->inner_join($productUniq,$store["data"],"id_store");

       $storeProduct =  array_map(function($storeProduct){
            return [
                "id_store"=> $storeProduct["id_store"],
                "owner_name"=> $storeProduct["owner_name"],
                "store_name"=> $storeProduct["store_name"],
                "phone"=> $storeProduct["phone"],
                "name_product"=> $storeProduct["name_product"],
                "category"=> $storeProduct["category"],
                "price"=> $storeProduct["price"],
                "price_promo"=> $storeProduct["price_promo"],
                "image1"=> $storeProduct["image1"],
                "image2"=> $storeProduct["image2"],
                "image3"=> $storeProduct["image3"],
                "image4"=> $storeProduct["image4"],
                "description"=> $storeProduct["description"],
                "status_store"=> $storeProduct["status_store"],
                "rating"=> $storeProduct["rating"],
                "photo_store"=> $storeProduct["photo_store"],
                "latitude"=> $storeProduct["latitude"],
                "longititude"=> $storeProduct["longititude"],
                "address"=> $storeProduct["address"]
        ];
       },$storeProduct);

        if ($storeProduct){
            return response()->json([
                'success'=>true,
                'message'=>'data success',
                'data'=>$storeProduct
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'data list product not found',
            ],404);
        }
    }

    public function getProduct($id){
        $product =  Product::where('id', $id)
            ->where('status_delete',0)
            ->first();

        if($product){
            return response()->json([
                'success'=>true,
                'message'=>'success',
                'data'=>$product
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'data product not found',
            ],404);
        }

    }

    public function getProductStore($id){

     $response = json_decode($this->successResponse($this
            ->serviceStore
            ->getListStore())
            ->original,true);

        $store=null;
        foreach ($response["data"] as $key => $value) {
            if($value["id_store"]==$id){
                $store = $value;
            }
        };

        if($store){
            $product =  Product::where('id_store', $id)
            ->where('status_delete',0)
            ->get();      
            if($product){
                return response()->json([
                    'success'=>true,
                    'message'=>'success',
                    'store'=>[
                        "id_store"=>$store["id_store"],
                        "store_name"=>$store["store_name"],
                        "image"=>$store["photo_store"],
                        "description"=>$store["description_store"],
                        "latitude"=>$store["latitude"],
                        "longititude"=>$store["longititude"],
                        "address"=>$store["address"],
                    ],
                    'data'=>$product
                ],201);
            }else{
                return response()->json([
                    'success'=>true,
                    'message'=>'data product store not found'
                ],404);
            }
        }else{
                return response()->json([
                    'success'=>true,
                    'message'=>'data store not found'
                ],404);
        }
     
    }

}
