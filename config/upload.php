<?php

return [
    //'webp' => false, // делать копию в webp
    'route' => [
        'index' => [
            'prefix' => 'system',
            'middleware' => [
                'web',
                'auth',
                'can:manage-files',
                'verified'
            ],
        ],
        'download' => [
            'middleware' => [
                'web',
                'auth'
            ],
        ],
        'upload' => [
            'middleware' => [
                'web',
                'auth',
                'only.ajax'
            ],
        ],
        'destroy' => [
            'middleware' => [
                'only.ajax',
                'web',
                'auth',
                'can:delete-files'
            ],
        ],
        'rotate' => [
            'middleware' => [
                'only.ajax',
                'web',
                'auth'
            ],
        ]
    ]
];
