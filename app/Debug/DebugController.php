<?php

namespace App\Debug;

use App\AppController;

use Lib\Dump;
use Lib\Crypt;
use Lib\DES;
use Lib\Enigma;
use Lib\Passwd;
use Lib\RSA;

use App\Host\HostService as Host;

class DebugController extends AppController
{

    public function dump()
    {
        $host_password = Host::password();
        $host_admin = Host::admin();
        
        Dump::words(wordlist(strlen( $host_password), Host::level(), 'password_list.txt'));
        Dump::correct([$host_admin,  $host_password]);
    

        if($input = $this->data) {
            $attemptsLeft = 4 - count(Dump::data());
            $header = "ROBCOM INDUSTRIES (TM) TERMLINK PROTOCOL\nENTER PASSWORD NOW";
            $header .= "ATTEMPT(S) LEFT: $attemptsLeft\n\n";
        
        if($input == 'reset') {
                Dump::reset();
                return Dump::memory(16, 12, $header);
            }
            Dump::input($input);
        }

        Dump::memory(16, 12, $header);
    }

}