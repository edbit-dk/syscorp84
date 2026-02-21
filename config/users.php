<?php

return [
    [
        'username' => 'ADMIN', 
        'email' => 'ADMIN@FALLHACK', 
        'password' => random_pass(),
        'code' => access_code(),
        'fullname' => 'FALLHACK ADMIN',
        'role' => 'ADMIN',
        'is_admin' => 1,
        'level_id' => 6,
        'credits' => 100
    ],
    [
        'username' => 'OPERATOR', 
        'email' => 'OPERATOR@FALLHACK', 
        'password' => random_pass(),
        'code' => access_code(),
        'fullname' => 'FALLHACK OPERATOR',
        'role' => 'OPERATOR',
        'is_admin' => 1,
        'level_id' => 3,
        'credits' => 50
    ],
    [
        'username' => 'USER', 
        'email' => 'USER@FALLHACK', 
        'password' => random_pass(),
        'code' => access_code(),
        'fullname' => 'FALLHACK USER',
        'role' => 'USER',
        'is_admin' => 0,
        'level_id' => 1,
        'credits' => 10
    ],
    
];