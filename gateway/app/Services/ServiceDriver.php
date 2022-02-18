<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceDriver
{
    use ConsumeExternalService;

    /**
     * The base uri to consume authors service
     * @var string
     */
    public $baseUri;

    /**
     * Authorization secret to pass to author api
     * @var string
     */
    public $secret;

    public function __construct()
    {
        $this->baseUri = config('service.driver.base_uri');
        $this->secret = config('service.driver.secret');
    }

    public function register($data)
    {
        return $this->performRequest("POST", '/api/v1/driver/', $data);
    }

    public function statusWork($status, $id)
    {
        return $this->performRequest("GET", 'api/v1/driver/' . $id . '/status/' . $status);
    }

    public function checkPhone($phone){
        return $this->performRequest("GET",'/api/v1/driver/phone/'.$phone);
    }

    public function getListDriverFromAdmin()
    {
        return $this->performRequest("GET", 'api/v1/driver/admin');
    }

    public function changeStatusAktivation($id, $status)
    {
        return $this->performRequest("GET", 'api/v1/driver/' . $id . '/activation/' . $status);
    }

    public function getDriver($id)
    {
        return $this->performRequest("GET", 'api/v1/driver/' . $id);
    }
    public function updatedDriver($data, $id)
    {
        return $this->performRequest("POST", 'api/v1/driver/' . $id, $data);
    }
    public function count(){
        return $this->performRequest("GET",'/api/v1/driver/count');
    }

    public function updateSaldoDriver($id,$saldo,$type){
        return $this->performRequest("GET",'/api/v1/driver/'.$id.'/saldo/'.$saldo.'/'.$type);
    }
}
