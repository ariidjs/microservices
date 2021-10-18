<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class CustomerServices
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
        $this->baseUri = config('service.customer.base_uri');
        $this->secret = config('service.customer.secret');
    }

    // public function getStore($id){
    //     return $this->performRequest("GET",'/stores/'.$id);
    // }


    /**
     * Obtain the full list of author from the author service
     */
    public function auth($fcm){
        return $this->performRequest("POST",'/api/v1/customer/auth',$fcm);
    }
    public function checkPhone($phone){
        return $this->performRequest("GET",'/api/v1/customer/phone/'.$phone);
    }
    public function login($phone,$data){
        return $this->performRequest("POST",'/api/v1/customer/login/'.$phone,$data);
    }
    public function register($data){
        return $this->performRequest("POST",'/api/v1/customer',$data);
    }
}
