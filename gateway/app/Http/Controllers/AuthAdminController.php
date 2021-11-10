<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceAdmin;
use App\Services\ServiceAdmin;
use App\Services\ServiceCustomer;
use App\Services\ServiceDetailTransaction;
use App\Services\ServiceProduct;
use App\Services\ServiceStore;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;

class AuthAdminController extends BaseController
{
    use ApiResponser;
    private $serviceAdmin;
    private $authServiceAdmin;
    private $serviceTransaction;
    private $serviceCustomer;
    private $serviceProduct;
    private $serviceStore;
    private $serviceDetailTransaction;
    private $DELETE = 1;
    private $AKTIF = 0;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    public function __construct(ServiceAdmin $serviceAdmin, AuthServiceAdmin $authServiceAdmin, ServiceTransaction $serviceTransaction, ServiceCustomer $serviceCustomer, ServiceProduct $serviceProduct,ServiceStore $serviceStore,ServiceDetailTransaction $serviceDetailTransaction)
    {
        $this->serviceAdmin = $serviceAdmin;
        $this->authServiceAdmin = $authServiceAdmin;
        $this->serviceTransaction = $serviceTransaction;
        $this->serviceCustomer = $serviceCustomer;
        $this->serviceProduct = $serviceProduct;
        $this->serviceStore = $serviceStore;
        $this->serviceDetailTransaction = $serviceDetailTransaction;
    }

    public function validationJWT($request)
    {
        $jwt = $request->header("jwt");
        try {
            $data = JWT::decode($jwt, $this->key, array('HS256'));
            return [
                "jwt" => $jwt,
                "data" => (array)$data
            ];
        } catch (ExpiredException $ex) {
            return response()->json([
                'success' => false,
                'message' => "unathorized failed"
            ], 400);
        }
    }

    public function register(Request $request)
    {
        // return "Hello";
        $name = $request->input('name');
        $username = $request->input('username');
        $email = $request->input('email');
        $password = $request->input('password');
        $role = $request->input('role');
        $avatar = $request->file('avatar');

        if ($avatar) {
            $photoName = time() . $avatar->getClientOriginalName();
        } else {
            $photoName = 'default.png';
        }

        $body = [
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password,
            'avatar' => $photoName,
            'role' => $role
        ];

        $response = json_decode($this->successResponse($this
            ->serviceAdmin
            ->register($body))
            ->original, true);

        if ($response["success"]) {
            if ($avatar) {
                $avatar->move('images', $photoName);
            }
            return $response;
        }
    }

    public function login(Request $request)
    {
        $username = $request->input("username");
        $password = $request->input("password");

        $body = [
            "username" => $username,
            "password" => $password
        ];

        $response =  json_decode($this->successResponse($this
            ->authServiceAdmin
            ->login($body))
            ->original, true);

        // return dd($response);


        if ($response["success"]) {
            $payload = array(
                "id" => $response['data']['id'],
                "name" => $response['data']['name'],
                "avatar" => $response['data']['avatar'],
                "role" => $response['data']['role']
            );
            $jwt = JWT::encode($payload, env('APP_KEY'));
            $response['data']['jwt'] = $jwt;
            return $response;
        }
    }

    public function getPromo()
    {
        $response =  json_decode($this->successResponse($this
            ->serviceTransaction
            ->getListTransaction())
            ->original, true);

        $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getLisCustomer())
            ->original, true);

        if (isset($response["data"]) && isset($customer["data"])) {
            $listTransaction = $response["data"];
            $listCustomer = $customer["data"];
            $transactionWithUserId = $this->inner_join($listTransaction, $listCustomer);

            $groups = array();
            foreach ($transactionWithUserId as $item) {
                $key = $item['id'];
                $total = 1;
                $total++;
                if (!array_key_exists($key, $groups)) {
                    $groups[$key] = array(
                        'id' => $item['id'],
                        'total_price' => $item['total_price'],
                        'level' => $item["level"],
                        'fcm' => $item["fcm"],
                        'transaction' => 1,
                        'name' => $item["name"],
                        'phone' => $item["phone"],
                        'level' => $item["level"],

                    );
                } else {
                    $groups[$key]['total_price'] = $groups[$key]['total_price'] + $item['total_price'];
                    $groups[$key]['transaction'] = $groups[$key]['transaction'] + 1;
                }
            }

            $data = array();
            foreach ($groups as $key => $value) {
                array_push($data, $value);
            }

            foreach ($data as $key => $value) {
                $total_price = $value["total_price"];
                if ($total_price <= 2000) {
                    $data[$key]["total_price"] = 1;
                } else if ($total_price > 2000 && $total_price <= 5000) {
                    $data[$key]["total_price"] = 0.8;
                } else if ($total_price > 5000 && $total_price <= 7000) {
                    $data[$key]["total_price"] = 0.6;
                } else if ($total_price > 7000 && $total_price <= 10000) {
                    $data[$key]["total_price"] = 0.4;
                } else if ($total_price > 10000) {
                    $data[$key]["total_price"] = 0.2;
                }

                //convert total__order
                $transaction = $value["transaction"];
                // echo $transaction.PHP_EOL;
                if ($transaction < 3) {
                    $data[$key]["transaction"] = 0.2;
                } else if ($transaction >= 3 && $transaction <= 5) {
                    $data[$key]["transaction"] = 0.4;
                } else if ($transaction >= 6 && $transaction <= 7) {
                    $data[$key]["transaction"] = 0.6;
                } else if ($transaction >= 8 && $transaction <= 10) {
                    $data[$key]["transaction"] = 0.8;
                } else if ($transaction > 10) {
                    $data[$key]["transaction"] = 1;
                }

                // convert rating
                $level = $value["level"];
                // echo $level.PHP_EOL;
                if ($level == "Silver") {
                    $data[$key]["level"] = 0.7;
                } else if ($level == "Gold") {
                    $data[$key]["level"] = 0.8;
                } else if ($level == "Platinum") {
                    $data[$key]["level"] = 0.9;
                }
            }

            $columnTotalPrice = array_column($data, "total_price");
            $columnTotalTransaction = array_column($data, "transaction");
            $columnLevel = array_column($data, "level");
            $maxTotalPrice = max($columnTotalPrice);
            $maxTotalTransaction = max($columnTotalTransaction);
            $maxLevel = max($columnLevel);

            foreach ($data as $key => $value) {
                $level = $value["level"] / $maxLevel;
                $totalPrice = $value["total_price"] / $maxTotalPrice;
                $totalTransaction = $value["transaction"] / $maxTotalTransaction;
                $data[$key]["level"] = $level;
                $data[$key]["total_price"] = $totalPrice;
                $data[$key]["transaction"] = $totalTransaction;
            }

            foreach ($data as $key => $value) {
                $totalSAW = ($value["level"] * 0.25) + ($value["total_price"] * 0.25) + ($value["transaction"] * 0.5);
                $data[$key]["saw"] = $totalSAW;
            }

            usort($data, function ($a, $b) {
                if ($a['saw'] == $b['saw']) {
                    return 0;
                }
                return ($a['saw'] > $b['saw']) ? -1 : 1;
            });
            return $data;
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data not found',
            ], 404);
        }
    }

    public function statusDeleteProduct($idProduct, $status)
    {
        if ($status == $this->AKTIF) {
            return json_decode($this->successResponse($this
                ->serviceProduct
                ->changeStatusDeleteProduct($idProduct, $this->AKTIF))
                ->original, true);
        } else if ($status == $this->DELETE) {
            return json_decode($this->successResponse($this
                ->serviceProduct
                ->changeStatusDeleteProduct($idProduct, $this->DELETE))
                ->original, true);
        }
    }


    function inner_join(array $left, array $right)
    {
        $out = array();
        foreach ($left as $left_record) {
            foreach ($right as $right_record) {
                if ($left_record["id_customer"] == $right_record["id"]) {
                    $out[] = array_merge($left_record, $right_record);
                }
            }
        }
        return $out;
    }

    public function updateCustomerAdmin(Request $request, $id)
    {
        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $image = $request->file('name');
        $address = $request->input('address');


        if ($image) {
            $avatar = time() . $image->getClientOriginalName();
            $image->move('images', $avatar);
        } else {
            $avatar = 'default.png';
        }

        if ($image) {
            $update = Customers::whereId($id)->update([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "image" => $avatar,
                "address" => $address,
            ]);
        } else {
            $update = Customers::whereId($id)->update([
                "name" => $name,
                "email" => $email,
                "phone" => $phone,
                "address" => $address,
            ]);
        }
        if ($update) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $update
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Kode validasi yang anda masukan salah',
            ], 401);
        }
    }

    public function getListTransaction(Request $request){
        $validatiop= $this->validationJWT($request);

        $response = json_decode($this->successResponse($this
        ->serviceTransaction
        ->getListTransaction())
        ->original, true);

       $data = collect($response["data"])->map(function($item,$key){
            $customer = json_decode($this->successResponse($this
            ->serviceCustomer
            ->getCustomer($item["id_customer"]))
            ->original, true);

            $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getStore($item["id_store"]))
            ->original, true);
            $item["customer_name"] =$customer["data"]["name"];
            $item["store_name"]= $store["data"]["store_name"];
            return $item;
        });

        // $response["data"]["customer_name"]=$customer["data"]["name"];

        return response()->json([
            'success' => true,
            'message' => 'Success',
            'data' => $data
        ], 200);
    }

    public function getDetailTransaction(Request $request,$notrans,$id_store){
        $this->validationJWT($request);

        $response = json_decode($this->successResponse($this
        ->serviceDetailTransaction
        ->getDetail($notrans,$id_store))
        ->original,true);

        return $response;


    }
}
