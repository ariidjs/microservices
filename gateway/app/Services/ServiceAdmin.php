<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceAdmin
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
        $this->baseUri = config('service.admin.base_uri');
        $this->secret = config('service.customer.secret');
    }

    public function register($data){
        return $this->performRequest('POST','/api/v1/admins/',$data);
    }
}
