<?php

namespace App\Http\Controllers;

use \Illuminate\Http\Request;
use \App\Models\Management;
use App\Services\ServiceStore;
use App\Traits\ApiResponser;
use Laravel\Lumen\Routing\Controller;

class ManagementController extends Controller
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

    }

    public function insert(Request $request){
        $distance = $request->input('distance');
        $total_order = $request->input('total_order');
        $rating = $request->input('rating');
        $jumlah_transaksi = $request->input('jumlah_transaksi');
        $level_pelanggan = $request->input('level_pelanggan');
        $total_transaksi = $request->input('total_transaksi');
        $taxDriver = $request->input('taxDriver');
        $taxStore = $request->input('taxStore');

        $insert = Management::insert([
            "distance"=>$distance,
            "total_order"=>$total_order,
            "rating"=>$rating,
            "jumlah_transaksi"=>$jumlah_transaksi,
            "level_pelanggan"=>$level_pelanggan,
            "total_transaksi"=>$total_transaksi,
            "taxDriver"=>$taxDriver,
            "taxStore"=>$taxStore,
        ]);

        if($insert){
            return response()->json([
                'success'=>true,
                'message'=>'insert Sukses',
                'data'=>$insert
            ],201);
        }else{
            return response()->json([
                'success'=>true,
                'message'=>'insert data failed',
                'data'=>null
            ],401);
        }

    }

    public function update(Request $request){

        $distance = $request->input('distance');
        $total_order = $request->input('total_order');
        $rating = $request->input('rating');
        $jumlah_transaksi = $request->input('jumlah_transaksi');
        $level_pelanggan = $request->input('level_pelanggan');
        $total_transaksi = $request->input('total_transaksi');

        $update = Management::whereId(1)->update([
            "distance"=>$distance,
            "total_order"=>$total_order,
            "rating"=>$rating,
            "jumlah_transaksi"=>$jumlah_transaksi,
            "level_pelanggan"=>$level_pelanggan,
            "total_transaksi"=>$total_transaksi,
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

    public function getManagement(){
        $management = Management::whereId(1)->first();
        if($management){
            return response()->json([
                'success'=>true,
                'message'=>'data success',
                'data' => $management
            ],201);
        }else{
            return response()->json([
                'success'=>false,
                'message'=>'failed',
                'data' => null
            ],401);
        }
    }
}
