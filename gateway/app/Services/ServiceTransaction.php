<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;

class ServiceTransaction
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
        $this->baseUri = config('service.transaction.base_uri');
        // $this->secret = config('service.store.secret');
    }
    public function orderCustomer($data)
    {
        return $this->performRequest("POST", '/api/v1/transaksi', $data);
    }

    public function confirmStore($idTransaction, $data)
    {
        return $this->performRequest("POST", '/api/v1/transaksi/' . $idTransaction, $data);
    }

    public function confirmDriver($idTransaction, $data)
    {
        return $this->performRequest("POST", '/api/v1/transaksi/driver/' . $idTransaction, $data);
    }

    public function validationCode($idTransaction, $code)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/' . $idTransaction . '/kode/' . $code);
    }

    public function finishTransaction($idTransaction)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/done/' . $idTransaction);
    }

    public function getListTransaction()
    {
        return $this->performRequest("GET", '/api/v1/transaksi/');
    }

    public function getDriverTrans($id) {
        return $this->performRequest("GET",'/api/v1/transaksi/driver/current/'.$id);
    }

    public function getListTransactionStore($idStore)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/store/'.$idStore);
    }
    public function getListTransactionDriver($idDriver)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/driver/'.$idDriver);
    }
    public function getListTransactionCustomer($idCustomer)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/customer/'.$idCustomer);
    }
    public function getListTransactionAdmin()
    {
        return $this->performRequest("GET", '/api/v1/transaksi/admin/');
    }

    public function getDriverHistory($id)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/driver/history/'.$id);
    }

    public function getDetailTransaction($notrans)
    {
        return $this->performRequest("GET", '/api/v1/transaksi/detail/'.$notrans);
    }

    // Sebuh function yang mengembalikan informasi total transaksi dari id yang telah dibuat uniq untuk SAW customer
    public function getInfoDetailTransaction(){
        return $this->performRequest("GET", '/api/v1/transaksi/listTransactionDone');
    }
    public function getDetailCusotmerTransaksi($id){
        return $this->performRequest("GET", '/api/v1/transaksi/customer/detail/'.$id);
    }

    // public function update($data,$id){
    //     return $this->performRequest("POST",'api/v1/store/'.$id,$data);
    // }

    // public function statusOpen($status,$id){
    //     return $this->performRequest("GET",'api/v1/store/'.$id.'/status/'.$status);
    // }

}
