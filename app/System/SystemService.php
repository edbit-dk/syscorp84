<?php

namespace App\System;

use Lib\Session;

use App\Host\HostModel as Hosts;

use App\User\UserService as User;
use App\Host\HostService as Host;
use App\Email\EmailService as Mail;

class SystemService 
{

    private static $uplink = 'uplink';

    public static function boot()
    {
        echo bootup(10);
        echo text('os_boot.txt');
    }

    public static function mode($mode)
    {
        Session::set('term', strtoupper($mode));
    }

    public static function uplink($input = '')
    {
        $code = 'code';

        if(empty($input) && !Session::has(self::$uplink)) {
            User::blocked(false);
            return self::code();
        }

        // Initialize login attempts if not set
        Host::attempts();

        // Check if the user is already blocked
        User::blocked();

        if(Session::get($code) == $input) {
            sleep(1);
            User::uplink(true);

            $remote_ip = remote_ip();

            echo <<< EOT
            UPLINK WITH CENTRAL NETWORK INITIATED... 

            SUCCESS: SECURITY ACCESS CODE SEQUENCE ACCEPTED

            AUTHORIZING $remote_ip...
            EOT;
            exit;

        } else {

            // Calculate remaining attempts
            $attempts_left = Host::attempts(true);

            // Block the user after 4 failed attempts
            if ($attempts_left == 0) {

                User::blocked(true);
                exit;

            } else {
                echo <<< EOT
                UPLINK WITH CENTRAL NETWORK INITIATED... 

                ERROR: ACCESS DENIED
                EOT;
            }
            
        }
    }

    public static function code()
    {
        $code = 'code';
        $access_code = access_code();

        Session::set($code, $access_code);

        echo <<< EOT
        THIS TERMINAL ALLOWS ACCESS TO CENTRAL NETWORK
        ______________________________________________

        ENTER SECURITY ACCESS CODE SEQUENCE: 
        
        [ {$access_code} ]
        EOT;
    }

    public static function login()
    {
        sleep(1);

        $port = $_SERVER['SERVER_PORT'];
        $date = strtoupper(date('F jS, Y',));

        echo <<< EOT
        WELCOME TO ROBCOM INDUSTRIES (TM) TERMLINK $port
        $date
        __________________________________________

        EOT;
    }

    public static function home() 
    {
        $username = strtoupper(User::username());
        $last_login = User::data()->last_login;
        $server_id = Host::id();

        echo <<< EOT
        ROBCOM INDUSTRIES VIRTUAL OPERATING SYSTEM
        COPYRIGHT 1975-1977 ROBCOM INDUSTRIES

        WELCOME, $username ($last_login)
        __________________________________________
        EOT;
    }

    public static function user()
    {   
        $date = timestamp(User::data()->last_login);
        $username = strtoupper(User::username());
        $last_login = "$date as $username";
        $last_ip = User::data()->ip;

        $host = Hosts::where('id', 1)->first();
        $id = $host->id;
        $os = $host->os;
        $org = $host->org;
        $location = $host->location;

        $motd = $host->motd;
        $notes = $host->notes;
        $mail = Mail::unread();

        $system_info = "Welcome to $org, $location\n";
        $system_info .= isset($motd) ? "\n$motd\n" : null;
        $system_info .= isset($notes) ? "\n$notes\n" : null;
        $system_info .= isset($mail) ? "\n$mail" : null;

        $current_date = datetime($host->created_at, config('unix_timestamp'));

        echo <<< EOT
        ROBCOM INDUSTRIES VIRTUAL OPERATING SYSTEM
        COPYRIGHT 1975-1977 ROBCOM INDUSTRIES
        -Server $id-

        SESSION: {$last_login} FROM $last_ip
        ($os): $current_date

        $system_info
        ___________________________________________ 
        EOT;
    }

    public static function connect()
    {
        $host = Host::data();
        $os = $host->os;
        $welcome = $host->welcome;
        $org = $host->org;
        
        echo <<< EOT
        $org
        $os

        $welcome
        EOT;
    }

    public static function auth()
    {
        $host = Host::data();
        $last_ip = User::data()->ip;
        $os = $host->os;
        $id = $host->id;
        $location = $host->location;
        $motd = $host->motd;
        $notes =  $host->notes;
        $org = $host->org;
        $username = strtoupper(User::username());
        $last_login = '';

        if($host_user = Host::data()->user(User::id())) {

            if(empty($host_user->pivot->last_session)) {
              $host_user->pivot->last_session = now();
              $host_user->pivot->save();
            }
            $date = timestamp($host_user->pivot->last_session);
            $last_login = "$date as $username";
        }

        
        $current_date = datetime($host->created_at, config('unix_timestamp'));

        $emails = Mail::unread();
        $mail = $emails;

        $system_info = "Welcome to $org, $location\n";
        $system_info .= isset($motd) ? "\n$motd\n" : null;
        $system_info .= isset($notes) ? "\n$notes\n" : null;
        $system_info .= isset($mail) ? "\n$mail" : null;

        Host::root();

        echo <<< EOT
        ROBCOM INDUSTRIES VIRTUAL OPERATING SYSTEM
        COPYRIGHT 1975-1977 ROBCOM INDUSTRIES
        -Server $id-

        SESSION: {$last_login} FROM $last_ip
        ($os): $current_date
        
        $system_info
        ___________________________________________ 
        EOT;
    }

}
