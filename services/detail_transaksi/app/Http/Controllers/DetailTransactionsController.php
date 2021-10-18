<?php

namespace App\Http\Controllers;

use App\Services\ServiceProduct;
use \Illuminate\Http\Request;
use \Illuminate\Support\Facades\Hash;
use \App\Models\DetailTransactions;
use \Illuminate\Support\Facades\DB;
use Laravel\Lumen\Routing\Controller;
use \App\Traits\ApiResponser;

class DetailTransactionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    use ApiResponser;

    public $productService;
    public function __construct(ServiceProduct $serviceProduct)
    {
        $this->productService = $serviceProduct;
    }

    public function insert(Request $request){
        $data = $request->getContent();
        $data = json_decode($data);
//        return $data;
        $dataArray = array();
        foreach ($data as $value) {
            $data = (array) $value;
            array_push($dataArray,$data);
        }
        $detailTransaction = DetailTransactions::insert($dataArray);

        if($detailTransaction){
            return response()->json([
                'success'=>true,
                'message'=>'insert Success',
                'data'=>$dataArray
            ],201);
        }else{
            return response()->json([
                'success'=>true,
                'message'=>'insert data failed',
            ],401);
        }

    }

    public function getNotransaction($notrans,$idStore){
        $productStore = json_decode($this->successResponse($this
            ->productService
            ->getProductStore($idStore))
            ->original,true)["data"];
        $data = DetailTransactions::whereNotransaksi($notrans)->get();
        $data = json_decode($data);

        $detailProduct = array();
        foreach ($data as $valueDetailProduct ) {
            foreach ($productStore as $valueProduct) {
                if($valueDetailProduct->id_product == $valueProduct["id"]){
                    $detailProduct[] = array_merge((array)$valueDetailProduct,$valueProduct);
                }
            }
        }

        if($detailProduct){
            return response()->json([
                'success'=>true,
                'message'=>'Success',
                'data'=>$detailProduct
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'get data failed',
            ],401);
        }

    }
}
