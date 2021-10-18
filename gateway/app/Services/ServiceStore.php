<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceStore
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
        $this->baseUri = config('service.store.base_uri');
        $this->secret = config('service.store.secret');
    }
    public function register($data){
        return $this->performRequest("POST",'/api/v1/store/',$data);
    }
    public function update($data,$id){
        return $this->performRequest("POST",'api/v1/store/'.$id,$data);
    }

    public function statusOpen($status,$id){
        return $this->performRequest("GET",'api/v1/store/'.$id.'/status/'.$status);
    }
    
}
