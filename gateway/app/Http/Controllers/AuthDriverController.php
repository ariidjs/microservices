<?php

namespace App\Http\Controllers;

use App\Services\AuthServiceDriver;
use App\Services\ServiceDriver;
use App\Services\ServiceSaldoDriver;
use App\Services\ServiceTransaction;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use Laravel\Lumen\Routing\Controller as BaseController;
use \App\Traits\ApiResponser;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Mail;
use function PHPUnit\Framework\isInstanceOf;

class AuthDriverController extends BaseController
{
    use ApiResponser;
    private $authServiceDriver;
    private $serviceDriver;
    private $serviceSaldo;
    private $serviceTransaction;

    private $TIME_EXPIRE = 3;
    private $JWT_EXPIRED = false;
    private $WITHDRAW = 2;
    private $DEPOSIT = 1;
    private $key = "asjlkdnaskjndjkawqnbdjkwbqdjknasljkmmndasjkjdnijkwqbduiqwbdojkawqnd";
    public function __construct(AuthServiceDriver $authServiceDriver, ServiceDriver $serviceDriver, ServiceSaldoDriver $saldoDriver, ServiceTransaction $serviceTransaction)
    {
        $this->authServiceDriver = $authServiceDriver;
        $this->serviceDriver = $serviceDriver;
        $this->serviceSaldo = $saldoDriver;
        $this->serviceTransaction = $serviceTransaction;
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


    public function checkPhone(Request $request,$phone)
    {

        // return $this->successResponse($this
        // ->authServiceDriver
        // ->checkPhone($phone));
        $response = json_decode($this->successResponse($this
            ->authServiceDriver
            ->checkPhone($phone))
            ->original, true);

        return $response->available;
    
        if ($response['success']) {
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
        }
    }


    public function login(Request $request, $phone)
    {

        $body = [
            'fcm' => $request->input('fcm')
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
    }

    public function withdraw(Request $request)
    {
        $validation = $this->validationJWT($request);
        $id_driver = $validation['data']['id'];
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $image = $request->file('image');
        $namabank = $request->input('nama_bank');
        if ($image) {
            $foto = time() . $image->getClientOriginalName();
        } else {
            $foto = '';
        }

        $body = [
            'id_driver' => $id_driver,
            'norek' => $norek,
            'saldo' => $saldo,
            'type' => $this->WITHDRAW,
            'nama_bank' => $namabank,
            'image' => $foto,
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
        $jwt = $request->header("jwt");
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

    public function deposit(Request $request)
    {
        $validation = $this->validationJWT($request);

        // return $validation;
        $id_driver = $validation['data']['id'];
        $norek = $request->input('norek');
        $saldo = $request->input('saldo');
        $image = $request->file('image');
        $namabank = $request->input('nama_bank');
        if ($image) {
            $foto = time() . $image->getClientOriginalName();
        } else {
            $foto = '';
        }

        $body = [
            'id_driver' => $id_driver,
            'norek' => $norek,
            'saldo' => $saldo,
            'type' => $this->DEPOSIT,
            'nama_bank' => $namabank,
            'image' => $foto,
        ];

        $response = json_decode($this->successResponse($this
            ->serviceSaldo
            ->deposit($body))
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

    public function statusDriver(Request $request, $status)
    {

        $validation = $this->validationJWT($request);

        return json_decode($this->successResponse($this
            ->serviceDriver
            ->statusWork($status, $validation["data"]["id"]))
            ->original, true);
    }

    public function confirmOrder(Request $request, $idTransaction)
    {
        $validation = $this->validationJWT($request);
        $body = [
            "status" => $request->input("status"),
            "id_driver" => $validation['data']['id']
        ];

        return json_decode($this->successResponse($this
            ->serviceTransaction
            ->confirmDriver($idTransaction, $body))
            ->original, true);
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
}
