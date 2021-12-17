<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use App\Services\ServiceCustomer;
use App\Traits\ApiResponser;
use \Illuminate\Http\Request;
class PromoController extends Controller
{
    use ApiResponser;
    private $ServiceCustomer;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(ServiceCustomer $serviceCustomer)
    {
        //
        $this->serviceCustomer = $serviceCustomer;
    }

    //

    public function insert(Request $request)
    {
        $data = $request->only([
            'idCustomer', 'promoName','promoDescription','promoPrice','date','expired'
        ]);

        // return $data;
        $data['date'] = date('Y-m-d');

        $insert = Promo::create($data);

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

    public function getData(){

        $customer = json_decode($this->successResponse($this
        ->serviceCustomer
        ->getLisCustomer())
        ->original,true);

        // return dd($customer);

        $data = Promo::all();

        $infoCustomer = $this->inner_join($customer['data'],$data->toArray());

        if ($data) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $infoCustomer
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed'
            ], 400);
        }

    }


    public function update(Request $request, $id)
    {
        $data = $request->only([
            'idCustomer', 'promoName','promoDescription','promoPrice','date','expired','status'
        ]);
        $update = Promo::where($id)->update($data);

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

    function inner_join(array $left, array $right)
    {
        $out = array();
        foreach ($left as $left_record) {
            foreach ($right as $right_record) {
                if ($left_record['id'] == $right_record['idCustomer']) {
                    $out[] = array_merge($left_record, $right_record);
                }
            }
        }
        return $out;
    }
}
