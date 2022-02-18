<?php

namespace App\Http\Controllers;

use App\Models\Drivers;
use \Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Kreait\Firebase\Factory;
use Laravel\Lumen\Routing\Controller as BaseController;


class DriverController extends BaseController
{
    private $DELETE = -1;
    private $ACTIVE = 1;
    private $PENDING = 0;
    private $databaseFirebase;

    public function __construct(

    ) {

        $factory = (new Factory)
            ->withServiceAccount(__DIR__ . '/firebaseKey.json')
            ->withDatabaseUri('https://proyek-akhir-1b6f2-default-rtdb.asia-southeast1.firebasedatabase.app/');

        $this->auth = $factory->createAuth();
        $this->databaseFirebase = $factory->createDatabase();
        // $this->databaseFirebase = $databaseFirebase;
    }


    public function insert(Request $request)
    {

        $name = $request->input("name_driver");
        $email = $request->input("email");
        $phone = $request->input("phone");
        $platkendaraan = $request->input("plat_kendaraan");
        $photo_profile = $request->input("photo_profile");
        $photo_stnk = $request->input("photo_stnk");
        $photo_ktp = $request->input("photo_ktp");
        $jkelamin = $request->input("j_kelamin");
        $stnk = $request->input("nomor_stnk");
        $nik = $request->input("nik");



        $emailCheck = Drivers::whereEmail($email)->first();
        $PhoneCheck = Drivers::wherePhone($phone)->first();
        $nikCheck = Drivers::whereNik($nik)->first();
        $stnkCheck = Drivers::whereNomorStnk($stnk)->first();
        $nomorKendaraanCheck = Drivers::wherePlatKendaraan($platkendaraan)->first();

        if ($emailCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Email yang anda masukan telah terdaftar',
            ], 401);
        }

        if ($nikCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Nik yang anda masukan telah terdaftar',
            ], 401);
        }

        if ($stnkCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor STNK yang anda masukan telah terdaftar',
            ], 401);
        }

        if ($PhoneCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Telephone yang anda masukan telah terdaftar',
            ], 401);
        }

        if ($nomorKendaraanCheck) {
            return response()->json([
                'success' => false,
                'message' => 'Nomor Kendaraan yang anda masukan telah terdaftar',
            ], 401);
        }


        $insert = Drivers::create([
            "name_driver" => $name,
            "email" => $email,
            "phone" => $phone,
            "plat_kendaraan" => $platkendaraan,
            "nik" => $nik,
            "nomor_stnk" => $stnk,
            "photo_profile" => $photo_profile,
            "photo_stnk" => $photo_stnk,
            "photo_ktp" => $photo_ktp,
            "j_kelamin" => $jkelamin
        ]);

        if ($insert) {
            // $this->sendEmail($request);

            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $insert
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Insert data failed',
            ], 401);
        }
    }


    public function updated(Request $request, $id)
    {
        $name = $request->input("name_driver");
        $email = $request->input("email");
        $phone = $request->input("phone");
        $nomor_stnk = $request->input("nomor_stnk");
        $nik = $request->input("nik");
        $plat_kendaraan = $request->input("plat_kendaraan");

        $updated = Drivers::whereId($id)->update([
            "name_driver" => $name,
            "email" => $email,
            "phone" => $phone,
            "nomor_stnk" => $nomor_stnk,
            "plat_kendaraan" => $plat_kendaraan,
            "nik" => $nik,
        ]);

        if ($updated) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $updated
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'updated data failed',
            ], 401);
        }
    }

    public function phoneNumberAvailable($phone)
    {
        return "hello";
        // return response()->json([
        //     'success' => true,
        //     'message' => 'phone is sad register',
        // ], 200);
        // $checkPhone = Drivers::wherePhone($phone)->first();
        // if ($checkPhone) {
        //     return response()->json([
        //         'success' => true,
        //         'message' => 'phone is register',
        //     ], 200);
        // } else {
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'phone not register',
        //     ], 200);
        // }
    }

    public function getDrivers($id)
    {
        $drivers = Drivers::whereId($id)->first();

        if ($drivers) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $drivers
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'data driver not found',
            ], 404);
        }
    }

    public function delete($id)
    {
        $delete = Drivers::whereId($id)->update(["status_delete" => $this->DELETE]);
        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'success delete',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function ChangeStatusUser($id_driver, $status)
    {
        if ($status == $this->ACTIVE) {
            $status = Drivers::whereId($id_driver)->update(["status_delete" => $this->ACTIVE]);
        } else {
            $status = Drivers::whereId($id_driver)->update(["status_delete" => $this->DELETE]);
        }

        if ($status) {
            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 401);
        }
    }

    public function login(Request $request, $phone)
    {
        $login = Drivers::wherePhone($phone)->update([
            "fcm" => $request->input('fcm')
        ]);

        $drivers = Drivers::wherePhone($phone)->first();

        if ($login) {
            if ($drivers->status_delete == $this->PENDING) {
                return response()->json([
                    'success' => false,
                    'message' => 'akun anda masih status pending silahkan tunggu aktivasi dari admin',
                ], 401);
            } else if ($drivers->status_delete == $this->DELETE) {
                return response()->json([
                    'success' => false,
                    'message' => 'akun anda telah di banned silahkan hubungi admin',
                ], 403);
            } else {
                return response()->json([
                    'success' => true,
                    'message' => 'login success',
                    'data' => $drivers
                ], 201);
            }
        } else {
            return response()->json([
                'success' => false,
                'message' => 'login failed',
            ], 404);
        }
    }

    public function sendEmail(Request $request)
    {
        // Siapkan Data
        $email = $request->email;
        $data = array(
            'name' => $request->name,
            'email_body' => $request->email_body
        );

        // Kirim Email
        Mail::send('email_template', $data, function ($mail) use ($email) {
            $mail->to($email, 'no-reply')
                ->subject("Sample Email Laravel");
            $mail->from('razituli@gmail.com', 'Testing');
        });

        // Cek kegagalan
        if (Mail::failures()) {
            return "Gagal mengirim Email";
        }
        return "Email berhasil dikirim!";
    }

    public function auth(Request $request)
    {
        $fcm = $request->input("fcm");
        $id = $request->input("id");
        $aut = Drivers::whereFcm($fcm)->whereId($id)->first();

        if ($aut) {
            return response()->json([
                'success' => true,
                'message' => 'authorize success',
                'data' => $aut
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'authorized failed',
            ], 404);
        }
    }

    public function banedDriver($id)
    {
        $delete = Drivers::whereId($id)->update(["status_delete" => $this->DELETE]);
        if ($delete) {
            return response()->json([
                'success' => true,
                'message' => 'success banned store',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'delete failed',
            ], 401);
        }
    }

    public function updatedSaldo($id, $saldo,$type)
    {

        $drivers = Drivers::whereId($id)->first();

        if($drivers){
            if($type == 'withdraw'){
                $update = Drivers::whereId($id)->update([
                    "saldo" => $drivers->saldo - $saldo
                ]);
            }elseif($type == 'deposit'){
                $update = Drivers::whereId($id)->update([
                    "saldo" => $drivers->saldo + $saldo
                ]);
            }

            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'updated failed',
                ], 404);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'id not found',
            ], 404);
        }


    }

    public function getListDriver()
    {
        $store = Drivers::all()->reject(function ($data) {
            return $data->status_delete === $this->DELETE || $data->status_delete === $this->PENDING;
        });
        if ($store) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $store
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not found',
            ], 404);
        }
    }

    public function getListDriversFromAdmin()
    {
        $store = Drivers::all();
        if ($store) {
            return response()->json([
                'success' => true,
                'message' => 'success',
                'data' => $store
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'not found',
            ], 404);
        }
    }

    public function logOut($id)
    {
        $logout = Drivers::whereId($id)->update(["fcm" => ""]);

        if ($logout) {
            return response()->json([
                'success' => true,
                'message' => 'logout success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'logout failed',
            ], 400);
        }
    }

    public function driverWork($id, $status)
    {
        // return "hello";
        $result = Drivers::whereId($id)->update(["status" => $status]);
        if ($result) {

            $reference = $this->databaseFirebase->getReference('DriversLocation');

            return response()->json([
                'success' => true,
                'message' => 'success',
            ], 201);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'failed',
            ], 401);
        }
    }


    public function taxSaldo($id, $saldo)
    {
        $driver = Drivers::whereId($id)->first();

        if($driver){
            $update = Drivers::whereId($id)->update([
                "saldo" => $driver->saldo - $saldo,
                "total_order" => $driver->total_order + 1
            ]);

            if ($update) {
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'updated failed',
                ], 404);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'driver not found',
            ], 404);
        }

    }

    public function countDriver(){
        $count = Drivers::count();

        return response()->json([
            'success' => true,
            'message' => 'login success',
            'data' => $count
        ], 201);
    }

    public function updateRatingDriver(Request $request){


        $id = $request->input("id_driver");
        $rating = $request->input("rating");
        $drivers = Drivers::whereId($id)->first();

        if($drivers){

            $ratingNew = ($drivers->rating+$rating) / 2;

            $update = Drivers::whereId($id)->update([
                "rating" => $ratingNew,
                "total_rating" => $drivers->total_rating + 1
            ]);

            if ($update) {
                $this->databaseFirebase->getReference('DriversLocation/'.$id)->update(["rating"=>$ratingNew]);
                return response()->json([
                    'success' => true,
                    'message' => 'success',
                ], 201);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'updated failed',
                ], 404);
            }
        }else{
            return response()->json([
                'success' => false,
                'message' => 'id not found',
            ], 404);
        }

    }


}
