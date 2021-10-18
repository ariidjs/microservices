<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class FcmService
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
        $this->baseUri = config('service.fcm.base_uri');
        $this->secret = config('service.product.secret');
    }


 
    public function pushNotification($data,$header)
    {
        return $this->performRequest('POST', '/fcm/send',$data,$header);
    }

   
}
