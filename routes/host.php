<?php

use App\Host\HostController;

use App\Debug\DebugController;
use App\Email\EmailController;

use App\Host\HostService as Host;
use App\User\UserService as User;

$app->get('/host-create', [HostController::class, 'create']);

$app->get('/connection', [HostController::class, 'connection']);

if(Host::guest() && User::auth()) {
    $app->get('/login', [HostController::class, 'logon']);
    $app->get('/logon', [HostController::class, 'logon']);
    $app->get('/set', [DebugController::class, 'set']);
    $app->get('/run', [DebugController::class, 'run']);  
    
    // sysadmin571_bypass /:
    $app->get('/sysadmin_bypass', [HostController::class, 'sysadmin']);
}

if(Host::auth() && !Host::guest()) {
    $app->get('/scan', [HostController::class, 'scan']);
    $app->get('/netstat', [HostController::class, 'scan']);
    $app->get('/connect', [HostController::class, 'connect']);
    $app->get('/telnet', [HostController::class, 'connect']);
    $app->get('/hosts', [HostController::class, 'hosts']);
    
    $app->get('/mail', [EmailController::class, 'mail']);
}

if(Host::auth() OR Host::guest()) {
    $app->get('/logout', [HostController::class, 'logoff']);
    $app->get('/logoff', [HostController::class, 'logoff']);
    $app->get('/exit', [HostController::class, 'logoff']);
    $app->get('/quit', [HostController::class, 'logoff']);
    $app->get('/dc', [HostController::class, 'logoff']);
    $app->get('/close', [HostController::class, 'logoff']);
}
