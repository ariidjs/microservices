<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \App\Models\DetailTransactions;
use App\Services\ServiceProduct;
use App\Traits\ApiResponser;
use Laravel\Lumen\Routing\Controller;



class DetailTransactionsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    
    use ApiResponser;
    private $serviceProduct;
   
    public function __construct(ServiceProduct $serviceProduct)
    {
        $this->serviceProduct = $serviceProduct;
    }

    public function insert(Request $request){
        $data = $request->getContent();
        $data = json_decode($data);

        // return $data;
     
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
                'success'=>false,
                'message'=>'insert data failed',
            ],401);
        }

    }


  public function getNotransaction($notrans,$idStore){
     $productStore = json_decode($this->successResponse($this
            ->serviceProduct
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

  public function getDetailTransaction($notrans){
      $notrans=DetailTransactions::whereNotransaksi($notrans)->get();
      return $notrans;
  }
}
