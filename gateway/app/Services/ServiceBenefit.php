<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceBenefit
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
        $this->baseUri = config('service.benefit.base_uri');
        $this->secret = config('service.customer.secret');
    }

    public function listBenefit()
    {
        return $this->performRequest("GET", '/api/v1/benefit/');
    }

    public function getTotalBenefit()
    {
        return $this->performRequest("GET", '/api/v1/benefit/totalBenefit');
    }

}
