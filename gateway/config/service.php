<?php

return [
    'auth'   =>  [
        'base_uri'  =>  env('AUTH_SERVICES_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
      'store'   =>  [
        'base_uri'  =>  env('STORE_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
      ],
      'driver'   =>  [
        'base_uri'  =>  env('DRIVER_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
      'customer'   =>  [
        'base_uri'  =>  env('CUSTOMER_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
      'product'   =>  [
        'base_uri'  =>  env('PRODUCT_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
      'saldo_store'   =>  [
        'base_uri'  =>  env('SALDO_STORE_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
       'saldo_driver'   =>  [
        'base_uri'  =>  env('SALDO_DRIVER_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
     'transaction'   =>  [
        'base_uri'  =>  env('TRANSACTION_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
    'admin'   =>  [
        'base_uri'  =>  env('ADMIN_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
    'detailTransaction'   =>  [
        'base_uri' =>  env('DETAIL_TRANSACTION_BASE_URL'),
        'secret'   =>  env('DETAIL_TRANSACTION_SERVICE_SECRET'),
    ],
    'management'   =>  [
        'base_uri' =>  env('MANAGEMENT_BASE_URL'),
        'secret'   =>  env('DETAIL_TRANSACTION_SERVICE_SECRET'),
    ],



];
