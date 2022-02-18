<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceDriver
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
        $this->baseUri = config('service.driver.base_uri');
        $this->secret = config('service.store.secret');
    }

    public function getDriver($id){
        return $this->performRequest("GET",'api/v1/driver/'.$id);
    }

    public function statusWork($status,$id){
        return $this->performRequest("GET",'api/v1/driver/'.$id.'/status/'.$status);
    }

    public function taxDriver($id,$saldo){
        return $this->performRequest("GET",'/api/v1/driver/'.$id.'/tax/'.$saldo);
    }

    public function updateRatingDriver($data){
        return $this->performRequest("POST",'/api/v1/driver/updaterating',$data);
    }


}
