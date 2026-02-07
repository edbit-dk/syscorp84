<?php

namespace Lib;

use \Lib\Session;

class Dump 
{

    public static $reset = false;
    public static $default = [];
    public static $words = [];
    public static $correct = [];
    public static $dump = 'memory_dump';
    public static $input = 'memory_input';

    public static function reset()
    {
        self::$reset = true;
        Session::remove(self::$input);
        Session::remove(self::$dump);
    }

    public static function words($words = [])
    {
        self::$words = $words;
    }

    public static function correct($words = [])
    {
        self::$correct = $words;
    }

    public static function memory($rows = 16, $cols = 12, $header = "") 
{
        if(empty(self::$words)) {
            // Default word list
            self::$words = ["HACK", "PASSWORD", "SECURITY", "VAULT", "ACCESS", "DENIED", "TERMINAL", "ADMIN", "PASS"];
        }
        
        $words = array_merge(self::$words, self::$correct);
        $randomize = self::$reset;
        $hexBase = 0xF964; 
        
        if ($randomize && Session::has(self::$dump)) {
            Session::remove(self::$input);
            Session::remove(self::$dump);
        }
        
        $totalChars = $rows * $cols * 2;

        if (!Session::has(self::$dump)) {
            $symbols = ['<', '>', '[', ']', '{', '}', '(', ')', '/', '\\', '|', '?', '!', '@', '#', '$', '%', '^', '&', '*', '-', '_', '+', '=', '.', ',', ':', ';'];
            $data = [];
            for ($i = 0; $i < $totalChars; $i++) {
                $data[$i] = $symbols[array_rand($symbols)];
            }

            $usedPositions = [];
            foreach ($words as $word) {
                $wordLen = strlen($word);
                
                do {
                    $pos = rand(0, $totalChars - $wordLen);
                    
                    // Ensure word stays on one line to make copying easier
                    $startRow = floor($pos / $cols);
                    $endRow = floor(($pos + $wordLen - 1) / $cols);
                    
                    $collision = ($startRow !== $endRow); 
                    if (!$collision) {
                        for ($j = $pos; $j < $pos + $wordLen; $j++) {
                            if (isset($usedPositions[$j])) {
                                $collision = true;
                                break;
                            }
                        }
                    }
                } while ($collision);

                for ($j = 0; $j < $wordLen; $j++) {
                    $data[$pos + $j] = $word[$j];
                    $usedPositions[$pos + $j] = true;
                }
            }
            Session::set(self::$dump, $data);
        } else {
            $data = Session::get(self::$dump);
        }

        // --- INCORRECT GUESS LOGIC ---
        if (!Session::has(self::$input)) {
            Session::set(self::$input, []);
        }
        
        $wrongGuesses = Session::get(self::$input);
        
        $dataString = implode('', $data);
        foreach ($wrongGuesses as $wrongWord) {
            $replacement = str_repeat('.', strlen($wrongWord));
            $dataString = str_ireplace($wrongWord, $replacement, $dataString);
        }
        
        $displayData = str_split($dataString);

        // --- OUTPUT GENERATION ---
        // Start with the externally provided header
        $output = $header;

        for ($i = 0; $i < $rows; $i++) {
            $addrL = sprintf("0x%04X", $hexBase + ($i * $cols));
            $addrR = sprintf("0x%04X", $hexBase + (($rows + $i) * $cols));

            $charsLeft = implode('', array_slice($displayData, $i * $cols, $cols));
            $charsRight = implode('', array_slice($displayData, ($rows + $i) * $cols, $cols));

            $output .= "$addrL $charsLeft  $addrR $charsRight\n";
        }
        
        echo $output;
    }

    public static function data()
    {
        return Session::get(self::$input);
    }

    public static function input($word) 
    {
        $correct = self::$correct;

        $input = strtolower($word);
        
        if (in_array($input, $correct)) {
            return true;
        } else {
            // Store incorrect guesses in session
            if (!in_array($input, Session::get(self::$input))) {
                Session::set(self::$input, array_merge(Session::get(self::$input), [$input]));
            }
            return false;
        }
    }
}
