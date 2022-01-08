<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServicePromo
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
        $this->baseUri = config('service.promo.base_uri');
        $this->secret = config('service.store.secret');
    }

    public function savePromo($data){
        return $this->performRequest("POST", '/api/v1/promo', $data);
    }
    public function getListPromo(){
        return $this->performRequest("GET", '/api/v1/promo');
    }
    public function getListPromoCustomer($id){
        return $this->performRequest("GET",'/api/v1/promo/customer/'.$id);
    }

}
