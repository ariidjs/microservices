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
    ]
];
