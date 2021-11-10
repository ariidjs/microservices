<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceProduct
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
        $this->baseUri = config('service.product.base_uri');
        $this->secret = config('service.store.secret');
    }
   
    public function getListFilterProduct($data)
    {
        return $this->performRequest('POST', '/api/v1/products/list',$data);
    }

    public function getProductStore($id){
        return $this->performRequest('GET','/api/v1/products/store/'.$id);
    }
}
