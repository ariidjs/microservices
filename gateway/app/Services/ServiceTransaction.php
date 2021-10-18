<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceTransaction
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
        $this->baseUri = config('service.transaction.base_uri');
        // $this->secret = config('service.store.secret');
    }
    public function orderCustomer($data){
        return $this->performRequest("POST",'/api/v1/transaksi',$data);
    }

    public function confirmStore($idTransaction,$data){
        return $this->performRequest("POST",'/api/v1/transaksi/'.$idTransaction,$data);
    }

    public function confirmDriver($idTransaction,$data){
        return $this->performRequest("POST",'/api/v1/transaksi/driver/'.$idTransaction,$data);
    }

    public function validationCode($idTransaction,$code){
        return $this->performRequest("GET",'/api/v1/transaksi/'.$idTransaction.'/kode/'.$code);
    }

    public function finishTransaction($idTransaction){
        return $this->performRequest("GET",'/api/v1/transaksi/done/'.$idTransaction);
    }

    
    // public function update($data,$id){
    //     return $this->performRequest("POST",'api/v1/store/'.$id,$data);
    // }

    // public function statusOpen($status,$id){
    //     return $this->performRequest("GET",'api/v1/store/'.$id.'/status/'.$status);
    // }
    
}
