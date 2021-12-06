<?php

namespace App\Http\Controllers;

use App\Models\Benefit;
use Illuminate\Http\Request;

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
}
