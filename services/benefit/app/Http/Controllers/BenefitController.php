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
        $total = Benefit::where('created_at', '>=', date('Y').'-01-01')->where('created_at', '<=', date('Y')."-12-31")->sum('totalBenefit');

        return response()->json([
            'success' => true,
            'message' => 'success',
            'data' => $total
        ], 201);
    }

    public function chartBenefit(){
        $data = DB::select("SELECT  count(created_at) as total,MONTH(created_at) as bulan,SUM(totalBenefit) as price
        FROM benefit
        WHERE created_at >='".date('Y')."-01-01' AND created_at   <= ' ".date('Y')."-12-31'
        GROUP BY  month(created_at)");

        if(isset($data)){
            foreach($data as $key => $value){
                $data[$key]->bulan = $this->convertMonth($value->bulan);
            }
            return response()->json([
                "status"=>true,
                "message"=>"Success",
                "data"=>$data
            ],201);
        }else{
            return response()->json([
                "status"=>false,
                "message"=>"data not found",
                "data"=>null
            ],201);
        }
    }

    public function convertMonth($month){
        if($month == 1){
            return 'January';
        }
        if($month == 2){
            return 'February';
        }
        if($month == 3){
            return 'March';
        }
        if($month == 4){
            return 'April';
        }
        if($month == 5){
            return 'May';
        }
        if($month == 6){
            return 'June';
        }
        if($month == 7){
            return 'July';
        }
        if($month == 8){
            return 'August';
        }
        if($month == 9){
            return 'September';
        }
        if($month == 10){
            return 'October';
        }
        if($month == 11){
            return 'November';
        }
        if($month == 12){
            return 'December';
        }

    }

}
