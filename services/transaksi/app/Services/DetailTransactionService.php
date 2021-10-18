<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class DetailTransactionService
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

    public function insert($data)
    {
        return $this->performRequest('POST', 'api/v1/detailTransaction',$data);
    }

    public function getNotransaksi($notrans,$idStore){
        return $this->performRequest('GET', '/api/v1/detailTransaction/'.$notrans.'/store/'.$idStore);
    }
}
