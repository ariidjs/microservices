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
    public function insertProduct($data)
    {
        return $this->performRequest("POST", '/api/v1/products', $data);
    }

    public function updatedProduct($data, $id)
    {
        return $this->performRequest("POST", '/api/v1/products/update/' . $id, $data);
    }

    public function getListProduct()
    {
        return $this->performRequest("GET", "/api/v1/products/list/");
    }

    public function getListProductStore($id)
    {
        return $this->performRequest("GET", "/api/v1/products/store/" . $id);
    }
    public function getListProductStoreFromAdmin($id)
    {
        return $this->performRequest("GET", "/api/v1/products/stores/" . $id);
    }

    public function changeStatusDeleteProduct($id, $status)
    {
        return $this->performRequest("GET", "/api/v1/products/" . $id . "/" . $status);
    }
}
