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
        
        // Initialiser ord
        Dump::words(wordlist(strlen($host_password), Host::level(), 'word_list.txt'));
        Dump::correct(['ADMIN', $host_password]);

        // Håndter input FØR vi genererer headeren
        if ($input = $this->data) {
            if ($input == 'reset') {
                Dump::reset();
            } else {
                Dump::input($input);
            }
        }

        // Nu kan vi sikkert tælle forsøg, da Dump::data() altid er et array
        $attemptsLeft = 4 - count(Dump::data());
        
        $header = "ROBCOM INDUSTRIES (TM) TERMLINK PROTOCOL\n";
        $header .= "ENTER PASSWORD NOW\n";
        $header .= "ATTEMPT(s) LEFT: " . ($attemptsLeft < 0 ? 0 : $attemptsLeft) . "\n\n";

        // Vis terminalen
        Dump::memory(16, 12, $header);
    }
}