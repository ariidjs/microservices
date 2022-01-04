<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceSaldoDriver
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
        $this->baseUri = config('service.saldo_driver.base_uri');
        $this->secret = config('service.store.secret');
    }
    public function withdraw($data){
        return $this->performRequest("POST",'/api/v1/saldo',$data);
    }

    public function deposit($data){
        return $this->performRequest("POST",'/api/v1/saldo',$data);
    }

    public function getHistorySaldo($id)
    {
        return $this->performRequest("GET",'/api/v1/saldo/'.$id);
    }

    public function getListSaldoDriver()
    {
        return $this->performRequest("GET",'/api/v1/saldo/');
    }

    public function updateStatus($id,$status)
    {
        return $this->performRequest("GET",'/api/v1/saldo/'.$id.'/'.$status);
    }

    public function getDetail($id){
        return $this->performRequest("GET",'/api/v1/saldo/detail/'.$id);
    }

}
