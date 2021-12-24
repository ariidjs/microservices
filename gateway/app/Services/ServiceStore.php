<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceStore
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
        $this->baseUri = config('service.store.base_uri');
        $this->secret = config('service.store.secret');
    }
    public function register($data)
    {
        return $this->performRequest("POST", '/api/v1/store/', $data);
    }
    public function update($data, $id)
    {
        return $this->performRequest("POST", 'api/v1/store/' . $id, $data);
    }

    public function statusOpen($status, $id)
    {
        return $this->performRequest("GET", 'api/v1/store/' . $id . '/status/' . $status);
    }

    public function getListStoreFromAdmin()
    {
        return $this->performRequest("GET", 'api/v1/store/admin');
    }

    public function getStore($id)
    {
        return $this->performRequest("GET", 'api/v1/store/' . $id);
    }

    public function activation($id, $status)
    {
        return $this->performRequest("GET", 'api/v1/store/' . $id . '/activation/' . $status);
    }

    public function changeStatusAktivation($id, $status)
    {
        return $this->performRequest("GET", 'api/v1/store/' . $id . '/activation/' . $status);
    }

    public function count(){
        return $this->performRequest("GET",'/api/v1/store/count');
    }
    public function getListStore()
    {
        return $this->performRequest("GET", '/api/v1/store');
    }
}
