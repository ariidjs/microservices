<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceRating
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
        $this->baseUri = config('service.rating.base_uri');
        $this->secret = config('service.store.secret');
    }
    public function updateRating($data){
        return $this->performRequest("POST",'/api/v1/rating',$data);
    }

}
