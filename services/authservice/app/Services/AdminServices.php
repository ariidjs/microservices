<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class AdminServices
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
        $this->baseUri = config('service.admin.base_uri');
        $this->secret = config('service.admin.secret');
    }

    // public function getStore($id){
    //     return $this->performRequest("GET",'/stores/'.$id);
    // }


    /**
     * Obtain the full list of author from the author service
     */
    public function login($data)
    {
        return $this->performRequest("POST", '/api/v1/admins/login', $data);
    }
}
