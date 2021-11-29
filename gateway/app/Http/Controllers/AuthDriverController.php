<?php

namespace App\Http\Controllers;


use App\Services\AuthServiceDriver;
use App\Services\ServiceDetailTransaction;
use App\Services\ServiceDriver;
use App\Services\ServiceSaldoDriver;
use App\Services\ServiceStore;
use App\Services\ServiceTransaction;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Mail;

class AuthDriverController extends BaseController
{
    use ApiResponser;
    private $authServiceDriver;
    private $serviceDriver;
    private $serviceSaldo;
    private $serviceTransaction;
    private $serviceDetailTransaction;
    private $serviceStore;

    private $TIME_EXPIRE = 3;
    private $JWT_EXPIRED = false;
    private $WITHDRAW = 2;
    private $DEPOSIT = 1;

    private $DELETE = -1;
    private $ACTIVE = 1;
    private $PENDING = 0;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    public function __construct(AuthServiceDriver $authServiceDriver, ServiceDriver $serviceDriver, ServiceSaldoDriver $saldoDriver, ServiceTransaction $serviceTransaction,ServiceDetailTransaction $serviceDetailTransaction,ServiceStore $serviceStore)
    {
        $this->authServiceDriver = $authServiceDriver;
        $this->serviceDriver = $serviceDriver;
        $this->serviceSaldo = $saldoDriver;
        $this->serviceTransaction = $serviceTransaction;
        $this->serviceDetailTransaction = $serviceDetailTransaction;
        $this->serviceStore = $serviceStore;
    }

    public function authDriver(Request $request)
    {

        $fcm = $request->input("fcm");
        $body = [
            "fcm" => $fcm
        ];

        return json_decode($this->successResponse($this
            ->authServiceDriver
            ->auth($body))
            ->original, true);
    }

    public function register(Request $request)
    {

        $name = $request->input("name_driver");
        $email = $request->input("email");
        $phone = $request->input("phone");
        $platkendaraan = $request->input("plat_kendaraan");
        $photo_profile = $request->file("photo_profile");
        $photo_stnk = $request->file("photo_stnk");
        $photo_ktp = $request->file("photo_ktp");
        $nomorstnk = $request->input("nomor_stnk");
        $nik = $request->input("nik");
        $j_kelamin = $request->input("j_kelamin");


        if ($photo_ktp) {
            $ktp = time() . $photo_ktp->getClientOriginalName();
            $photo_ktp->move('images', $ktp);
        } else {
            $ktp = 'default.png';
        }
        if ($photo_profile) {
            $avatar = time() . $photo_profile->getClientOriginalName();
            $photo_profile->move('images', $avatar);
        } else {
            $avatar = 'default.png';
        }
        if ($photo_stnk) {
            $stnk = time() . $photo_stnk->getClientOriginalName();
            $photo_stnk->move('images', $stnk);
        } else {
            $stnk = 'default.png';
        }



        $body = [
            "name_driver" => $name,
            "email" => $email,
            "phone" => $phone,
            "plat_kendaraan" => $platkendaraan,
            "nik" => $nik,
            "nomor_stnk" => $nomorstnk,
            "photo_profile" => $avatar,
            "photo_stnk" => $stnk,
            "photo_ktp" => $ktp,
            "j_kelamin" => $j_kelamin
        ];

//        return $this->successResponse($this
//            ->serviceDriver
//            ->register($body));
        return json_decode($this->successResponse($this
            ->serviceDriver
            ->register($body))
            ->original, true);
    }


    public function login(Request $request, $phone)
    {
        $response = json_decode($this->successResponse($this
            ->authServiceDriver
            ->checkPhone($phone))
            ->original, true);
            // return $response;
        if ($response["success"]) {
            $body = [
                'fcm' => $request->header('fcm')
            ];
            $data = json_decode($this->successResponse($this
                ->authServiceDriver
                ->login($phone, $body))
                ->original, true);

            if ($data['success']) {
                $payload = array(
                    "id" => $data['data']['id'],
                    "email" => $data['data']['email'],
                    "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
                );
                $jwt = JWT::encode($payload, $this->key);
                $data['jwt'] = $jwt;
                return $data;
            }

        }else {
            return $response;
        }
    }

    public function getDriverById(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_driver = $validation['data']['id'];
        return json_decode($this->successResponse($this
            ->serviceDriver
            ->getDriver($id_driver))
            ->original, true);
    }

    public function getHistorySaldo(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_driver = $validation['data']['id'];
        $driver = json_decode($this->successResponse($this
        ->serviceDriver
        ->getDriver($id_driver))
        ->original, true);

        $response = json_decode($this->successResponse($this
            ->serviceSaldo
            ->getHistorySaldo($id_driver))
            ->original, true);

        if($driver){
            $response['total_saldo'] = $driver['data']['saldo'];
            return $response;
        }




    }

    public function withdrawORDeposit(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_driver = $validation['data']['id'];
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $image = $request->file('image');
        $type = $request->input('type');
        $namabank = $request->input('nama_bank');
        $nama = $request->input('nama');
        if ($image) {
            $foto = time() . $image->getClientOriginalName();
        } else {
            $foto = '';
        }

        if($type == $this->WITHDRAW){
            $driver = json_decode($this->successResponse($this
            ->serviceDriver
            ->getDriver($id_driver))
            ->original, true);

            if($driver["data"]){
                if($driver["data"]["saldo"] < $saldo){
                    return response()->json([
                        'success'=>false,
                        'message'=>'saldo anda tidak mencukupi untuk melakukan withdraw'
                    ],400);
                }
            }else{
                return response()->json([
                    'success'=>false,
                    'message'=>'data driver not found'
                ],400);
            }
        }

        $body = [
            'id_driver' => $id_driver,
            'norek' => $norek,
            'saldo' => $saldo,
            'type' => $type,
            'nama_bank' => $namabank,
            'image' => $foto,
            'nama' => $nama
        ];

        $response = json_decode($this->successResponse($this
            ->serviceSaldo
            ->withdraw($body))
            ->original, true);

        if ($response["success"]) {
            if ($image) {
                $image->move('images', $foto);
            }
            if ($validation['expired']) {
                $response["jwt"] = $validation["jwt"];
            } else {
                $response["jwt"] = null;
            }
            return $response;
        }
    }

    private function auth($fcm)
    {
        $body = [
            "fcm" => $fcm
        ];

        return json_decode($this->successResponse($this
            ->authServiceDriver
            ->auth($body))
            ->original, true);
    }

    public function validationJWT($request)
    {
        $jwt = request()->header('Authorization');
        $jwt = str_replace('Bearer ', '', $jwt);
        $fcm = $request->header('fcm');
        try {
            $data = JWT::decode($jwt, $this->key, array('HS256'));
            return [
                "expired" => $this->JWT_EXPIRED,
                "jwt" => $jwt,
                "data" => (array)$data
            ];
        } catch (ExpiredException $ex) {
            $data = $this->auth($fcm);
            $payload = array(
                "id" => $data['data']['id'],
                "email" => $data['data']['email'],
                "exp" => (round(microtime(true) * 1000) + ($this->TIME_EXPIRE * 60000))
            );
            $jwt = JWT::encode($payload, $this->key);
            return [
                "expired" => !$this->JWT_EXPIRED,
                "data" => $payload,
                "jwt" => $jwt
            ];
        }
    }



    public function statusDriver(Request $request, $status)
    {

        $validation = $this->validationJWT($request);

        return json_decode($this->successResponse($this
            ->serviceDriver
            ->statusWork($status, $validation["data"]["id"]))
            ->original, true);
    }

    public function getDriverHistory(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_driver = $validation['data']['id'];
        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->getDriverHistory($id_driver))
            ->original, true);
    }

    public function confirmOrder(Request $request, $idTransaction)
    {
        $validation = $this->validationJWT($request);
        $body = [
            "status" => $request->input("status"),
            "id_driver" => $validation['data']['id']
        ];

       $response = json_decode($this->successResponse($this
            ->serviceTransaction
            ->confirmDriver($idTransaction, $body))
            ->original, true);

        return $response;
    }

    public function validationCode(Request $request, $idTransaction, $code)
    {
        $this->validationJWT($request);

        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->validationCode($idTransaction, $code))
            ->original, true);
    }

    public function finishTransaction(Request $request, $idTransaction)
    {
        $this->validationJWT($request);
        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->finishTransaction($idTransaction))
            ->original, true);
    }


    public function sendEmail(Request $request)
    {
        // Siapkan Data
        // $email = $request->email;
        $email = "androzone404@gmail.com";
        $data = array(
            'name' => "Razitul ikhlas",
            'email_body' => "ini body email"
        );

        // Kirim Email
        Mail::send('email_template', $data, function ($mail) use ($email) {
            $mail->to($email, 'no-reply')
                ->subject("Pendaftaran akun driver");
            $mail->from('razituli@gmail.com', '');
        });

        // Cek kegagalan
        if (Mail::failures()) {
            return "Gagal mengirim Email";
        }
        return "Email berhasil dikirim!";
    }

    public function getListDriverFromAdmin()
    {
        return json_decode($this->successResponse($this
            ->serviceDriver
            ->getListDriverFromAdmin())
            ->original, true);
    }

    public function changeStatusAktivation($id, $status)
    {
        if ($status == $this->ACTIVE) {
            return json_decode($this->successResponse($this
                ->serviceDriver
                ->changeStatusAktivation($id, $this->ACTIVE))
                ->original, true);
        } else {
            return json_decode($this->successResponse($this
                ->serviceDriver
                ->changeStatusAktivation($id, $this->DELETE))
                ->original, true);
        }
    }

    public function getDriver($id)
    {
        return json_decode($this->successResponse($this
            ->serviceDriver
            ->getDriver($id))
            ->original, true);
    }


    public function getDriverTrans(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_driver = $validation['data']['id'];
        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->getDriverTrans($id_driver))
            ->original, true);
    }

    public function updateDriver(Request $request, $id)
    {
        $name = $request->input("name_driver");
        $email = $request->input("email");
        $phone = $request->input("phone");
        $platkendaraan = $request->input("plat_kendaraan");
        $photo_profile = $request->file("photo_profile");
        $photo_stnk = $request->file("photo_stnk");
        $photo_ktp = $request->file("photo_ktp");
        $saldo = $request->input("saldo");
        $status = $request->input("status");
        $nomorstnk = $request->input("nomor_stnk");
        $nik = $request->input("nik");

        if ($photo_ktp) {
            $ktp = time() . $photo_ktp->getClientOriginalName();
            $photo_ktp->move('images', $ktp);
        } else {
            $ktp = 'default.png';
        }
        if ($photo_profile) {
            $avatar = time() . $photo_profile->getClientOriginalName();
            $photo_profile->move('images', $avatar);
        } else {
            $avatar = 'default.png';
        }
        if ($photo_stnk) {
            $stnk = time() . $photo_stnk->getClientOriginalName();
            $photo_stnk->move('images', $stnk);
        } else {
            $stnk = 'default.png';
        }


        $body = [
            "name_driver" => $name,
            "email" => $email,
            "phone" => $phone,
            "plat_kendaraan" => $platkendaraan,
            "nik" => $nik,
            "nomor_stnk" => $nomorstnk,
            "photo_profile" => $photo_profile,
            "photo_stnk" => $photo_stnk,
            "photo_ktp" => $photo_ktp,
            "saldo" => $saldo,
            "status" => $status
        ];

        return json_decode($this->successResponse($this
            ->serviceDriver
            ->updatedDriver($body, $id))
            ->original, true);
    }

    public function getDetailTransaction(Request $request,$notrans,$id_store){
        $this->validationJWT($request);

        // return $notrans;
        $store = json_decode($this->successResponse($this
            ->serviceStore
            ->getStore($id_store))
            ->original,true);

        $store = collect($store["data"])
        ->contains('id_store', 'owner_name', 'rating', 'store_name','description_store','photo_store', 'latitude', 'longititude', 'address');

        $detailProduct = json_decode($this->successResponse($this
        ->serviceDetailTransaction
        ->getDetail($notrans,$id_store))
        ->original,true);

        return response()->json([
            'success'=>true,
            'message'=>'success',
            'data'=>[
                "store"=> $store,
                "detail_product"=>$detailProduct["data"]
            ]
        ],201);
    }
}
