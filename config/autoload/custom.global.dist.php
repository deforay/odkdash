<?php

return [
    'settings' => [
        'locale' => 'en_US',
    ],
    // Timezone used by scheduled tasks (tasks/GeneralTasks.php). Pick the
    // IANA zone for this deployment (e.g. Africa/Blantyre, America/Jamaica,
    // Africa/Harare). Falls back to PHP's date_default_timezone_get() if
    // unset. Examples: 'UTC', 'Asia/Kolkata'.
    'timezone' => 'UTC',
    'odkcentral' => [
        'spirrt' => [
            [
                'url' => '',
                'projectId' => '',
                'formId' => '',
                'email' => '',
                'password' => '',
            ]
        ],
        'spirt' => [
            [
                'url' => '',
                'projectId' => '',
                'formId' => '',
                'email' => '',
                'password' => '',
            ]
        ],
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
