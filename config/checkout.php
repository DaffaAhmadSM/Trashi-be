<?php

return [
    /*
    | Price per kilometer in IDR.
    */
    'price_per_km' => env('CHECKOUT_PRICE_PER_KM', 5000),

    /*
    | Minimum service fee in IDR.
    */
    'min_fee' => env('CHECKOUT_MIN_FEE', 20000),

    /*
    | Maximum service distance in kilometers.
    */
    'max_distance_km' => env('CHECKOUT_MAX_DISTANCE_KM', 30),

    /*
    | Currency for all fees.
    */
    'currency' => 'IDR',
];
