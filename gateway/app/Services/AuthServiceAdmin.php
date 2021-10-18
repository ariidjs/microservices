<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class AuthServiceAdmin
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
        $this->baseUri = config('service.auth.base_uri');
        $this->secret = config('service.auth.secret');
    }

    public function login($data){
        return $this->performRequest("POST",'api/v1/auth/admin',$data);
    }
    
}
