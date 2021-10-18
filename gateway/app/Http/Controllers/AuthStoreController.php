<?php

namespace App\Http\Controllers;


use App\Services\AuthServiceStore;
use App\Services\ServiceProduct;
use App\Services\ServiceSaldoStore;
use App\Services\ServiceStore;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;


class AuthStoreController extends BaseController
{
    use ApiResponser;
    private $authServiceStore;
    private $serviceStore;
    private $serviceSaldo;
    private $serviceTransaction;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    private $TIME_EXPIRE = 3;
    private $serviceProduct;
    private $JWT_EXPIRED = false;
    private $WITHDRAW = 2;
    private $DEPOSIT = 1;


    public function __construct(AuthServiceStore $authServiceStore,ServiceStore $serviceStore,ServiceProduct $serviceProduct,ServiceSaldoStore $serviceSaldoStore,ServiceTransaction $serviceTransaction)
    {
        $this->authServiceStore =$authServiceStore;
        $this->serviceStore = $serviceStore;
        $this->serviceProduct = $serviceProduct;
        $this->serviceSaldo = $serviceSaldoStore;
        $this->serviceTransaction = $serviceTransaction;
    }

    private function auth($fcm){
         $body = [
            "fcm"=>$fcm
        ];

        return json_decode($this->successResponse($this
            ->authServiceStore
            ->auth($body))
            ->original,true);
    }

    public function authStore(Request $request){
        $data = $this->auth($request->input("fcm"));
        if($data['success']){
            $payload = array(
                "id" => $data['data']['id'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp"=>(round(microtime(true) * 1000) + ($this->TIME_EXPIRE*60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            $data['jwt'] = $jwt;
            return $data;
        }
    }

    public function inserProduct(Request $request){
        $jwt = $request->header("jwt");
        $fcm = $request->header('fcm');
    
        Try{
            JWT::decode($jwt,$this->key,array('HS256'));
            return $this->insert($request,$jwt,$this->JWT_EXPIRED);
        }catch(ExpiredException $ex){
            $data = $this->auth($fcm);
            $payload = array(
                "id" => $data['data']['id'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp"=>(round(microtime(true) * 1000) + ($this->TIME_EXPIRE*60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            return $this->insert($request,$jwt,!$this->JWT_EXPIRED);
        }

    }

    public function insert($request,$jwt,$expired){
        $id_store = $request->input('id_store');
        $name_product = $request->input('name_product');
        $category = $request->input('category');
        $price = $request->input('price');
        $price_promo = $request->input('price_promo');
        $image1 = $request->file('image1');
        $image2 = $request->file('image2');
        $image3 = $request->file('image3');
        $image4 = $request->file('image4');
        $description = $request->input('description');
        $status_delete = 0;

        if($image1){
            $fotoProduct1 =time().$image1->getClientOriginalName();
        }else{
            $fotoProduct1 = '';
        }

        if($image2){
            $fotoProduct2 =time().$image2->getClientOriginalName();
        }else{
            $fotoProduct2 = '';
        }

        if($image3){
            $fotoProduct3 = time().$image3->getClientOriginalName();
        }else{
            $fotoProduct3 = '';
        }

        if($image4){
            $fotoProduct4 = time().$image4->getClientOriginalName();
        }else{
            $fotoProduct4 = '';
        }

        $body = [
            'id_store'=>$id_store,
            'name_product'=>$name_product,
            'category'=>$category,
            'price'=>$price,
            'price_promo'=>$price_promo,
            'image1'=>$fotoProduct1,
            'image2'=>$fotoProduct2,
            'image3'=>$fotoProduct3,
            'image4'=>$fotoProduct4,
            'description'=>$description,
            'status_delete'=>$status_delete,
            'jwt' =>$jwt
        ];


            $response = json_decode($this->successResponse($this
            ->serviceProduct
            ->insertProduct($body))
            ->original,true);

            if($response["success"]){
                if($image1){
                    $image1->move('images',$fotoProduct1);
                }
                if($image2){
                    $image2->move('images',$fotoProduct2);
                }
                if($image3){
                    $image3->move('images',$fotoProduct3);
                }
                if($image4){
                    $image4->move('images',$fotoProduct4);
                }
            
            
     
                if($expired){
                    $response["jwt"]=$jwt;
                }else{
                   $response["jwt"]=null; 
                }
                return $response;
            }
       
    }


    public function register(Request $request){
        $owner_name = $request->input("owner_name");
        $store_name = $request->input("store_name");
        $phone = $request->input("phone");
        $email = $request->input("email");
        $fcm = $request->input("fcm");
        $description_store = $request->input("description_store");
        $nik_ktp = $request->input("nik_ktp");
        $photo_ktp = $request->file("photo_ktp");
        $photo_store = $request->file("photo_store");
        $latitude = $request->input("latitude");
        $longititude = $request->input("longititude");
        $address = $request->input("address");


         if($photo_ktp){
            $ktp = time().$photo_ktp->getClientOriginalName();
        }else{
            $ktp = 'default.png';
        }
        
        if($photo_store){
            $store = time().$photo_store->getClientOriginalName();
        }else{
            $store = 'default.png';
        }

        $body = [
            "owner_name" => $owner_name,
            "store_name" => $store_name,
            "phone" => $phone,
            "email" => $email,
            "fcm" => $fcm,
            "description_store" => $description_store,
            "nik_ktp" => $nik_ktp,
            "photo_ktp" => $ktp,
            "latitude" => $latitude,
            "longititude" => $longititude,
            "address" => $address,
            "photo_store"=>$store
        ];

          $response = json_decode($this->successResponse($this
            ->serviceStore
            ->register($body))
            ->original,true);

            if($response["success"]){
                 $photo_ktp->move('images',$ktp); 
                 $photo_store->move('images',$store);    
                 return $response;
            }
     }
     
        

    
    
     public function checkPhone($phone){
        return json_decode($this->successResponse($this
            ->authServiceStore
            ->checkPhone($phone))
            ->original,true);
    }

    public function getListProduct(Request $request){
        $validation = $this->validationJWT($request);
        return json_decode($this->successResponse($this
            ->serviceProduct
            ->getListProductStore($validation["data"]["id"]))
            ->original,true);
    }

    public function updateProduct(Request $request,$idProduct){
        // return "Hello";
        $jwt = $request->header("jwt");
        $fcm = $request->header('fcm');
    
        Try{
            JWT::decode($jwt,$this->key,array('HS256'));
            return $this->update($request,$jwt,$this->JWT_EXPIRED,$idProduct);
        }catch(ExpiredException $ex){
            $data = $this->auth($fcm);
            $payload = array(
                "id" => $data['data']['id_store'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp"=>(round(microtime(true) * 1000) + ($this->TIME_EXPIRE*60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            return $this->update($request,$jwt,!$this->JWT_EXPIRED,$idProduct);
        }
    }
    public function update($request,$jwt,$expired,$idProduct){
        $name_product = $request->input('name_product');
        $category = $request->input('category');
        $price = $request->input('price');
        $price_promo = $request->input('price_promo');
        $image1 = $request->file('image1');
        $image2 = $request->file('image2');
        $image3 = $request->file('image3');
        $image4 = $request->file('image4');
        $description = $request->input('description');

        $store = JWT::decode($jwt,$this->key,array('HS256'));

        if($image1){
            $fotoProduct1 =time().$image1->getClientOriginalName();
        }else{
            $fotoProduct1 = '';
        }

        if($image2){
            $fotoProduct2 =time().$image2->getClientOriginalName();
        }else{
            $fotoProduct2 = '';
        }

        if($image3){
            $fotoProduct3 = time().$image3->getClientOriginalName();

        }else{
            $fotoProduct3 = '';
        }

        if($image4){
            $fotoProduct4 = time().$image4->getClientOriginalName();
        }else{
            $fotoProduct4 = '';
        }

         $body = [
            'id_store'=>$store->id,
            'name_product'=>$name_product,
            'category'=>$category,
            'price'=>$price,
            'price_promo'=>$price_promo,
            'image1'=>$fotoProduct1,
            'image2'=>$fotoProduct2,
            'image3'=>$fotoProduct3,
            'image4'=>$fotoProduct4,
            'description'=>$description,
            'status_delete'=>0,
        ];


        $response = json_decode($this->successResponse($this
        ->serviceProduct
        ->updatedProduct($body,$idProduct))
        ->original,true);


        if($response["success"]){
             if($image1){
                $image1->move('images',$fotoProduct1);
             }
             if($image2){
                $image2->move('images',$fotoProduct2);
             }
             if($image3){
                $image3->move('images',$fotoProduct3);
             }
             if($image4){
               $image4->move('images',$fotoProduct4);  
             }

             if($expired){
                $response["jwt"]=$jwt;
             }else{
                $response["jwt"]=null;
             }
            
             return $response;
        }
    }

    public function login(Request $request,$phone){
       
        $body = [
            'fcm'=>$request->input('fcm')
        ];
        
        $data = json_decode($this->successResponse($this
            ->authServiceStore
            ->login($phone,$body))
            ->original,true);

        if($data['success']){
            $payload = array(
                "id" => $data['data']['id_store'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp"=>(round(microtime(true) * 1000) + ($this->TIME_EXPIRE*60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            $data['jwt'] = $jwt;
            return $data;
        }
    }

    public function confirmOrder(Request $request,$idTransaction){
        $this->validationJWT($request);
        $body = [
            "status" => $request->input("status")
        ];
        return json_decode($this->successResponse($this
        ->serviceTransaction
        ->confirmStore($idTransaction,$body))
        ->original,true);
    }


    public function validationJWT($request){
        $jwt = $request->header("jwt");
        $fcm = $request->header('fcm');
    
        Try{
            $data = JWT::decode($jwt,$this->key,array('HS256'));
            return [
                "expired" => $this->JWT_EXPIRED,
                "jwt"=> $jwt,
                "data"=>(array)$data
            ];
        }catch(ExpiredException $ex){
            $data = $this->auth($fcm);
            $payload = array(
                "id" => $data['data']['id_store'],
                "owner_name" => $data['data']['owner_name'],
                "store_name" => $data['data']['store_name'],
                "exp"=>(round(microtime(true) * 1000) + ($this->TIME_EXPIRE*60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            return [
                "expired" => !$this->JWT_EXPIRED,
                "data"=>$payload,
                "jwt"=> $jwt
            ];
        }
    }

    public function withdraw(Request $request){
        $validation = $this->validationJWT($request); 
        $id_store = $validation['data']['id'];
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $image= $request->file('image');
        $namabank = $request->input('nama_bank');
        if($image){
            $foto =time().$image->getClientOriginalName();
        }else{
            $foto = '';
        }

        $body =[
            'id_store'=>$id_store,
            'norek'=>$norek,
            'saldo'=>$saldo,
            'type'=>$this->WITHDRAW,
            'nama_bank'=>$namabank,
            'image'=>$foto,
        ];

        $response = json_decode($this->successResponse($this
        ->serviceSaldo
        ->withdraw($body))
        ->original,true);

        if($response["success"]){
            if($image){
                 $image->move('images',$foto);
            }
            if($validation['expired']){
                $response["jwt"] = $validation["jwt"];
            }else{
                $response["jwt"] = null;
            }
            return $response;
        }
    }

    public function deposit(Request $request){
        $validation = $this->validationJWT($request); 
        $id_store = $validation['data']['id'];
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $image= $request->file('image');
        $namabank = $request->input('nama_bank');
        if($image){
            $foto =time().$image->getClientOriginalName();
        }else{
            $foto = '';
        }

        $body =[
            'id_store'=>$id_store,
            'norek'=>$norek,
            'saldo'=>$saldo,
            'type'=>$this->DEPOSIT,
            'nama_bank'=>$namabank,
            'image'=>$foto,
        ];

        $response = json_decode($this->successResponse($this
        ->serviceSaldo
        ->deposit($body))
        ->original,true);

        if($response["success"]){
            if($image){
                 $image->move('images',$foto);
            }
            if($validation['expired']){
                $response["jwt"] = $validation["jwt"];
            }else{
                $response["jwt"] = null;
            }
            return $response;
        }
    }

    public function updateStore(Request $request){
        $validation = $this->validationJWT($request);


        $store_name = $request->input('store_name');
        $phone = $request->input('phone');
        $photo_store = $request->file('photo_store');
        $address = $request->input('address');
        $latitude = $request->input('latitude');
        $longititude = $request->input('longititude');
        $description_store = $request->input('description_store');


        if($photo_store){
            $fotoStore = time().$photo_store->getClientOriginalName();
        }else{
            $fotoStore = null;
        }

        $body = [
                'store_name'=>$store_name,
                'phone'=>$phone,
                'photo_store'=>$fotoStore,
                'address'=>$address,
                'latitude'=>$latitude,
                'longititude'=>$longititude,
                'description_store'=>$description_store
        ];

         $response = json_decode($this->successResponse($this
        ->serviceStore
        ->update($body,$validation["data"]["id"]))
        ->original,true);

        if($response["success"]){
            if($photo_store){
                 $photo_store->move('images',$fotoStore);
            }

            if($validation["expired"]){
                $response["jwt"] = $validation["jwt"];
            }else{
                $response["jwt"] = null;
            }

            return $response;
        }

    }

    public function statusOpen(Request $request,$status){
     
        $validation = $this->validationJWT($request); 
  
        return json_decode($this->successResponse($this
        ->serviceStore
        ->statusOpen($status,$validation["data"]["id"]))
        ->original,true);
    }



}
