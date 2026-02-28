<?php

namespace App\Host;

use App\AppController;

use App\Level\LevelModel as Level;
use App\Host\HostModel as Hosts;

use App\User\UserService as User;
use App\Host\HostService as Host;
use App\AppService as App;

class HostController extends AppController
{
    public static function create() 
    {
        $data = request()->get('data');

        $input = explode(' ', trim($data));

        $name = $input[0];

        $level = Level::inRandomOrder()->first();

        $pass_length = rand($level->min, $level->max);
        
        $admin_pass = wordlist(config('database') . '/wordlist.txt', $pass_length , 1)[0];
        
        $host = HostModel::create([
            'hostname' => $name,
            'password' =>  strtolower($admin_pass),
            'level_id' => $level->id,
            'ip' => random_ip()
        ]);

        dd($host);
    }

    public function connection()
    {

        if(Host::guest()) {
            $hostname = Host::hostname(); 
            echo "[@$hostname]>";
            exit;
        }
        
        if(Host::auth()) {
            $hostname = Host::hostname(); 
            $username = User::username();

            if(Host::data()->user_id == User::id()) {
                echo "[$username@$hostname]#";
            } else {  
                echo "[$username@$hostname]>";
            }
            
            exit;
        }

        if(User::auth()) {
            $hostname = User::username();
            echo "[@$hostname]>";
            exit;
        }

        if(User::uplinked()){
            echo '>'; 
        }else {
             echo '.';
        }

    }

    public function connect() 
    {
        $host = false;
        $host_id = false;

        if($this->data) {
            $data = $this->data;
        } else {
            echo 'ERROR: HOST NOT FOUND';
            exit;
        }

        if($host = Host::try($data)) {
            $host_id = $host->id;
        }

        $user = User::data();

        if($host->credits > $user->credits) {
            echo <<<EOT
            ERROR: ACCESS DENIED. CREDITS TOO LOW!
            EOT;
            exit;
        }

        if(Host::check()) {
            
            if(Host::data()->node($host_id) || Host::data()->host($host_id)) {
                $host = Host::connect($data);
            }
        } else {
            $host = Host::connect($data);
        }

        sleep(1);

        if(!$host) {
            echo 'ERROR: CONNECTION REFUSED';
            exit;
        } else {
            $host = Host::data()->hostname;
            $ip = Host::data()->ip;

            echo <<< EOT
            ACCESSING $ip...
            EOT;
            exit;
        }

    }

    public function hosts()
    {
        $hosts = Hosts::all();

        foreach ($hosts as $host) {
            echo <<<EOT
            $host->hostname: $host->org - $host->location\n
            EOT;
        }
    }

    public function scan() 
    {
        $hosts = false;

        if(Host::check()) {
            $hosts = Host::data()->connections();
        } else {
            $hosts = Host::networks();
        }

        if(!$hosts) {
            echo "ERROR: NO COMLINKS FOUND!\n";
            exit;
        } 

        echo "SEARCHING COMLINKS...\n\nACTIVE STATIONS:\n";

        foreach ($hosts as $host) {

            $access = ' ';

            if($host->user(User::auth())) {
                $access = '*';
            }

            if($host->user_id == User::auth()) {
                $access = '#';
            }
            
            echo <<<EOT

            $access $host->hostname: $host->org - $host->location (CREDITS $host->credits)

            EOT;
        }
        
    }

    public function sysadmin()
    {
        $host = Host::data();
        $user = User::data()->host($host->id);

       if($user) {
            Host::logon(User::username(), User::data()->password);
       } else {

            User::data()->hosts()->attach($host->id);

            Host::logon(User::username(), User::data()->password);
       }

       Host::data()->users()->updateExistingPivot(User::id(),['last_session' => now()]);
       echo bootup();
       exit;
    }

    public function rlogin()
    {
        $data = parse_request('data');

        if(!empty($data)) {
            if(Host::rlogin($data)) {
                echo <<< EOT
                AUTHENTICATION COMPLETE!
                EOT;
            } else {
                echo <<< EOT
                ERROR: INVALID CREDENTIALS!
                EOT;
            }
        }
    }

    public function logon() 
    {
        $input = App::auth($this->data);

        // Initialize login attempts if not set
        Host::attempts();

        // Check if the user is already blocked
        Host::blocked();

        sleep(1);

        if(Host::logon($input['username'],  $input['password'])) {
            echo <<< EOT
            AUTHENTICATION COMPLETE!
            EOT;
        } else {
             // Calculate remaining attempts
             $attempts_left = Host::attempts(true);
 
             // Block the user after 4 failed attempts
             if ($attempts_left == 0) {

                Host::blocked(true);
                exit;

             } else {
                echo <<< EOT
                ERROR: INVALID CREDENTIALS!
                EOT;
                exit;
             }
        }
        
    }

    public function logoff() 
    {
        Host::logoff();
        echo "\nDISCONNECTING...\n";
    }

}