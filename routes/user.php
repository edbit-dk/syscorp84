<?php

use App\User\UserController;
use App\Email\EmailController;
use App\Host\HostController;

use App\User\UserService as User;
use App\Host\HostService as Host;
use App\AppService as App;

use Lib\Input;

if(User::auth()) {
     // Auth
     $app->get('/password', [UserController::class, 'password']);
     $app->get('/user', [UserController::class, 'user']);

     $app->get('/rlogin', [HostController::class, 'rlogin']);
}

if(!User::auth()) {
     $app->get('/login', [UserController::class, 'login']);
     $app->get('/logon', [UserController::class, 'login']);
     $app->get('/lo', [UserController::class, 'login']);
     $app->get('/enroll', [UserController::class, 'enroll']);
}

if(User::auth() && !Host::auth() && !Host::guest()) {
     $app->get('/mail', [EmailController::class, 'mail']);
     $app->get('/scan', [HostController::class, 'scan']);
     
     $app->get('/netstat', [HostController::class, 'scan']);
     $app->get('/scan', [HostController::class, 'scan']);
     
     $app->get('/connect', [HostController::class, 'connect']);
     $app->get('/telnet', [HostController::class, 'connect']);

     $app->get('/exit', [UserController::class, 'logout']);
     $app->get('/logout', [UserController::class, 'logout']);
     $app->get('/logoff', [UserController::class, 'logout']);
     $app->get('/exit', [UserController::class, 'logout']);
     $app->get('/quit', [UserController::class, 'logout']);
     $app->get('/dc', [UserController::class, 'logout']);
     $app->get('/close', [UserController::class, 'logout']);
}

if(User::uplinked() && !User::auth()) {
     $app->get('/exit', [UserController::class, 'unlink']);
     $app->get('/logout', [UserController::class, 'unlink']);
}