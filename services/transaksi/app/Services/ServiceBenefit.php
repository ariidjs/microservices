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
        $this->secret = config('service.product.secret');
    }



    public function saveBenefit($data)
    {
        return $this->performRequest('POST', '/api/v1/benefit',$data);
    }


}
