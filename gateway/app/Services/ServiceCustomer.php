<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceCustomer
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

    public function register($data)
    {
        return $this->performRequest("POST", '/api/v1/customer/', $data);
    }

    public function getLisCustomer()
    {
        return $this->performRequest("GET", '/api/v1/customer/');
    }

    public function getCustomer($id){
        return $this->performRequest("GET","/api/v1/customer/".$id);
    }

    public function count(){
        return $this->performRequest("GET",'/api/v1/customer/count');
    }
}
