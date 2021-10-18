<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class ServiceProduct
{
    use ConsumeExternalService;

    public $baseUri;


    public $secret;

    public function __construct()
    {
        $this->baseUri = config('service.product.base_uri');
        $this->secret = config('service.product.secret');
    }



    public function getListFilterProduct($data)
    {
        return $this->performRequest('POST', '/products/list',$data);
    }

    public function getProductStore($id){
        return $this->performRequest('GET','/api/v1/products/store/'.$id);
    }

}
