<?php

return [
    [
        'username' => 'ADMIN', 
        'email' => 'ADMIN@FALLHACK', 
        'password' => word_pass(),
        'code' => access_code(),
        'role' => 'ADMIN',
        'is_admin' => 1,
        'credits' => 100
    ],
    [
        'username' => 'OPERATOR', 
        'email' => 'OPERATOR@FALLHACK', 
        'password' => word_pass(),
        'code' => access_code(),
        'role' => 'OPERATOR',
        'is_admin' => 1,
        'credits' => 50
    ],
    [
        'username' => 'USER', 
        'email' => 'USER@FALLHACK', 
        'password' => word_pass(),
        'code' => access_code(),
        'fullname' => 'FALLHACK USER',
        'role' => 'USER',
        'is_admin' => 0,
        'credits' => 10
    ],
    
];