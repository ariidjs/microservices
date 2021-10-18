<?php

namespace App\Services;
use App\Traits\ConsumeExternalService;

class StoreService
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

    public function getStore($id){
        return $this->performRequest("GET",'api/v1/store/'.$id);
    }


    /**
     * Obtain the full list of author from the author service
     */
    public function getListFilterProduct($data)
    {
        return $this->performRequest('POST', '/products/list',$data);
    }

    /**
     * Create Author
     */
    public function createAuthor($data)
    {
        return $this->performRequest('POST', '/authors', $data);
    }

    /**
     * Get a single author data
     */
    public function obtainAuthor($author)
    {
        return $this->performRequest('GET', "/authors/{$author}");
    }

    /**
     * Edit a single author data
     */
    public function editAuthor($data, $author)
    {
        return $this->performRequest('PUT', "/authors/{$author}", $data);
    }

    /**
     * Delete an Author
     */
    public function deleteAuthor($author)
    {
        return $this->performRequest('DELETE', "/authors/{$author}");
    }
}
