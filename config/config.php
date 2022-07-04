<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Operators
    |--------------------------------------------------------------------------
    |
    | Possible operators for specific types
    |
    */
    'operators' => [
        'number' => ['<','<=','>','>=','=','!='],
        'string' => ['=','!=', '~'],
        'boolean' => ['=','!='],
        'date' => ['<','<=','>','>=','=','!='],
        'enum' => ['=','!='],
        'source' => ['=','!='],
    ],

    /*
    |--------------------------------------------------------------------------
    | Route prefix
    |--------------------------------------------------------------------------
    |
    |
    */
    'prefix' => 'filters',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    |
    */
    'middleware' => ['api'],
];
