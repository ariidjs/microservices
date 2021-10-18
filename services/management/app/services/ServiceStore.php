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
    public function getListStore(){
        return $this->performRequest("GET",'/api/v1/store/');
    }
}
