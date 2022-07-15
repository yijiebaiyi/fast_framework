<?php

return [
    "Cache" => [
        "default" => "redis",
        "memcache" => [
            "servers" => [
                [
                    "host" => "localhost",
                    "port" => 11211,
                ]
            ],
        ],
        "memcached" => [
            "servers" => [
                [
                    "host" => "localhost",
                    "port" => 11211,
                ]
            ],
        ],
        "redis" => [
            "master" => [
                "pconnect" => false,
                "host" => "localhost",
                "port" => 6379,
                "timeout" => 0,
            ],
        ],
    ],

    "Log" => [
        "default" => "file",
        "file" => [
            "path" => RUNTIME_PATH
        ],
    ]
];