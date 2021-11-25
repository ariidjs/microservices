<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceDetailTransaction
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
        $this->baseUri = config('service.detailTransaction.base_uri');
        $this->secret = config('service.detailTransaction.secret');
    }

    public function getDetail($notrans,$id_store)
    {
        return $this->performRequest("GET", "api/v1/detailTransaction/".$notrans."/store/".$id_store);
    }

}
