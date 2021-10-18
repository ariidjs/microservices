<?php

return [
    'product'   =>  [
        'base_uri'  =>  env('PRODUCT_BASE_URL'),
        'secret'  =>  env('PRODUCT_SERVICE_SECRET'),
    ],
    'store'   =>  [
        'base_uri' =>  env('STORE_BASE_URL'),
        'secret'   =>  env('STORE_SERVICE_SECRET'),
    ],
    'detailTransaction'   =>  [
        'base_uri' =>  env('DETAIL_TRANSACTION_BASE_URL'),
        'secret'   =>  env('DETAIL_TRANSACTION_SERVICE_SECRET'),
    ],
    'fcm'   =>  [
        'base_uri' =>  env('FCM_BASE_URL'),
        'secret'   =>  env('DETAIL_TRANSACTION_SERVICE_SECRET'),
    ],
    'customer'   =>  [
        'base_uri' =>  env('CUSTOMER_BASE_URL'),
        'secret'   =>  env('DETAIL_TRANSACTION_SERVICE_SECRET'),
    ],
    'driver'   =>  [
        'base_uri' =>  env('DRIVER_BASE_URL'),
        'secret'   =>  env('DETAIL_TRANSACTION_SERVICE_SECRET'),
    ],
    

    
];
