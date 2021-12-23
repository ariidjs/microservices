<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BenefitController extends Controller
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

    //

    public function insert(Request $request){
        $data = $request->only([
            'notransaksi', 'totalBenefit','taxStore','taxDriver'
        ]);

        // return $data;
        $data['date'] = date('Y-m-d');

        $insert = Benefit::create($data);

        if($insert){
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $insert
            ], 201);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'failed',
                'data' => null
            ], 401);
        }
    }

    public function getListBenefit(){
        $data = Benefit::all();

        if($data){
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $data
            ], 201);
        }else{
            return response()->json([
                'success' => true,
                'message' => 'failed',
                'data' => null
            ], 401);
        }
    }

    public function getTotalBenefit(){
        $total = Benefit::sum('totalBenefit');

        return response()->json([
            'success' => true,
            'message' => 'success',
            'data' => $total
        ], 201);
    }

}