<?php

declare(strict_types=1);

return [
    'domain_root' => 'http://localhost:8082',
    'contact' => [
        'email' => 'mail@example.tld',
        'name' => 'Mark Bellingham'
    ],
    'project' => [
        'db_host' => 'localhost:3306',
        'db_name' => 'project',
        'db_char' => 'utf8mb4',
        'db_user' => 'webserver',
        'db_pass' => 'ABC123'
    ],
    'lastfm_api' => [
        'root_url' => 'http://ws.audioscrobbler.com/2.0/?method=user.',
        'username' => 'username',
        'api_key' => 'api-key',
        'shared_secret' => 'shared-secret',
    ],
    'google' => [
        'email' => 'example@gmail.com',
        'apppassword' => 'app-password'
    ],
    'server_secret' => 'server-secret',
    'pictures_path' => '/path/to/my/pictures',
    'flickr_datadump' => '/path/to/flickr/data/dump'
];
