<?php

return [
    'settings' => [
        'locale' => 'en_US',
    ],
    'odkcentral' => [
        'spirrt' => [
            // [
            //     'url' => 'https://bw-odk.labsinformatics.com',
            //     'projectId' => '1',
            //     'formId' => 'SPI-RRT-Apr-2023',
            //     'email' => 'admin@botswana.org',
            //     'password' => 'bnmko)(*&^',
            // ],
            [
                'url' => 'https://odk-central.malawi.rt-qi.com',
                'projectId' => '3',
                'formId' => 'SPI_RRT%20Checklist',
                'email' => 'KLipenga@mgic.umaryland.edu',
                'password' => 'KLipenga@12345',
            ]
        ],
        'spirt' => [],
    ],
    'email' => [
        'host' => 'smtp.gmail.com',
        'config' => [
            'port' => 587,
            'username' => '',
            'password' => '',
            'ssl' => 'tls',
            'auth' => 'login',
        ],
    ],
    'admin' => [
        'name' => '',
        'emailAddress' => '',
    ],
    'password' => [
        'salt' => '',
    ],
];