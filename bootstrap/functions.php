<?php

function closure_to_str($func)
{
    $refl = new \ReflectionFunction($func); // get reflection object
    $path = $refl->getFileName();  // absolute path of php file
    $begn = $refl->getStartLine(); // have to `-1` for array index
    $endn = $refl->getEndLine();
    $dlim = PHP_EOL;
    $list = explode($dlim, file_get_contents($path));         // lines of php-file source
    $list = array_slice($list, ($begn-1), ($endn-($begn-1))); // lines of closure definition
    $last = (count($list)-1); // last line number

    if((substr_count($list[0],'function')>1)|| (substr_count($list[0],'{')>1) || (substr_count($list[$last],'}')>1))
    { throw new \Exception("Too complex context definition in: `$path`. Check lines: $begn & $endn."); }

    $list[0] = ('function'.explode('function',$list[0])[1]);
    $list[$last] = (explode('}',$list[$last])[0].'}');


    return implode($dlim,$list);
}

// format "root:5d41402abc4b2a76b9719d911017c592:0:0:Superuser:/root:/bin/sh\n"
function passwd($file, $action, $username, $newLine = null) {
    // Split the content into lines
    $lines = explode(PHP_EOL, $file);
    
    // Loop through the lines and process based on the action
    foreach ($lines as $index => $line) {
        // Split each line into parts (assuming ':' as separator)
        $parts = explode(':', $line);
        
        // Check if the username matches
        if ($parts[0] === $username) {
            if ($action === 'edit' && $newLine !== null) {
                // Edit the line by replacing it with the new line
                $lines[$index] = $newLine;
            } elseif ($action === 'delete') {
                // Remove the line
                unset($lines[$index]);
            }
            return implode(PHP_EOL, $lines);  // Return updated content after modification
        }
    }

    // If action is 'add', add a new line
    if ($action === 'add' && $newLine !== null) {
        $lines[] = $newLine;
        return implode(PHP_EOL, $lines);  // Return updated content after adding
    }

    return $file;  // Return original content if no action was performed
}

// Get the password (or other fields) for a specific username
function passwd_info($file, $username, $field = 1) {
    // Split the content into lines
    $lines = explode(PHP_EOL, $file);
    
    foreach ($lines as $line) {
        // Split each line into parts (assuming ':' as separator)
        $parts = explode(':', $line);
        
        // Check if the username matches
        if ($parts[0] === $username) {
            return $parts[$field];  // Return the password (second field in the line)
        }
    }

    return null;  // Return null if the username wasn't found
}


function array_has($array, $key, $val) {
    if (array_search($val, array_column($array, $key)) !== FALSE) {
        return true;
      } else {
        return false;
      }
}

function access_code($length = 6, $chars = 'AXYZ01234679', $spaces = '-') {
    $code_1 = random_str($length, $chars) . $spaces;
    $code_2 = random_str($length, $chars) . $spaces;
    $code_3 = random_str($length, $chars) . $spaces;
    $code_4 = random_str($length, $chars);
    
    return "{$code_1}{$code_2}{$code_3}{$code_4}"; 
}

function paginate($page, $count = 0, $perPage = 5) {
    $page = isset($page) && is_numeric($page) ? intval($page) : 1;

    // Pagination settings
    $page = max(1, $page); // Ensure page is at least 1
    $offset = ($page - 1) * $perPage; // Calculate offset for SQL query

     // Total number
     $total = ceil($count / $perPage);

    return ['page' => $page, 'limit' => $perPage, 'offset' => $offset, 'total' => $total];
}

function isEmail($email) {
    //return filter_var($email, FILTER_VALIDATE_EMAIL);
    return str_contains($email, '@');
}

function request_url($url, $custom_query = 'query') {

    if(!empty($url)) {
        $url_components = parse_url($url);
        // Use parse_str() function to parse the
        // string passed via URL
        parse_str($url_components[$custom_query], $params);

        return $params;
    }

    return false;

}


/**
 * Function to count the number of matched characters in two strings.
 *
 * @param string $str1 The first string to compare.
 * @param string $str2 The second string to compare.
 * @return int The count of matched characters.
 */
function count_match_chars($str1, $str2) {
    $count = 0;
    $charCount = [];

    // Populate the charCount array with the frequency of each character in str2
    for ($i = 0; $i < strlen($str2); $i++) {
        $char = $str2[$i];
        if (isset($charCount[$char])) {
            $charCount[$char]++;
        } else {
            $charCount[$char] = 1;
        }
    }

    // Loop through each character in str1 and count matches
    for ($i = 0; $i < strlen($str1); $i++) {
        $char = $str1[$i];
        if (isset($charCount[$char]) && $charCount[$char] > 0) {
            $count++;
            $charCount[$char]--;
        }
    }

    return $count;
}


/**
 * Function to echo "#" character a specified number of times.
 *
 * @param $count The number of times to echo "#".
 */
function str_char_repeat($count, $char = '# ') {
    return str_repeat($char, $count);
}

/**
 * Generate a random string, using a cryptographically secure 
 * pseudorandom number generator (random_int)
 *
 * This function uses type hints now (PHP 7+ only), but it was originally
 * written for PHP 5 as well.
 * 
 * For PHP 7, random_int is a PHP core function
 * For PHP 5.x, depends on https://github.com/paragonie/random_compat
 * 
 * @param int $length      How many characters do we want?
 * @param string $keyspace A string of all possible characters
 *                         to select from
 * @return string
 */
function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}

function word_pass($length = false) {

    if(!$length) {
        $length = rand(4,15);
    }

    return wordlist($length, 1)[0];
}

function random_pass($length = false) {
    if(!$length) {
        $length = rand(1,15);
    }

    if($length) {
        $length = rand($length,15);
    }

    return wordlist($length, 1, 'password_list.txt')[0];
}

function random_os($length = false) {
    if(!$length) {
        $length = rand(3,16);
    }

    if($length) {
        $length = rand($length,16);
    }

    return wordlist($length, 1, 'os_list.txt')[0];
}

function merge_txt_files($files = [], $outputFile = 'merged.txt') {
    $allLines = [];

    foreach ($files as $file) {
        if (!file_exists($file)) {
            echo "File not found: $file\n";
            continue;
        }
    
        // Get lines, undgå tomme
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $allLines = array_merge($allLines, $lines);
    }
    
    // Fjern dubletter og sortér (valgfrit)
    $uniqueLines = array_unique($allLines);
    sort($uniqueLines); // Kan fjernes, hvis sortering ikke ønskes
    
    // Gem til ny fil
    file_put_contents($outputFile, implode(PHP_EOL, $uniqueLines));
}

function random_welcome() {
    // Read file
    $lines = file(config('public') . "/text/welcome_list.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // Check lines
    if ($lines) {
        return $lines[array_rand($lines)];
    } else {
        return 0;
    }
}

function random_date($start = false, $end = false) {
    // Set the start and end timestamps
    if(!$start) {
        $start = strtotime('1969-01-01 00:00:00');
    }
    if(!$end) {
        $end = strtotime('1983-12-31 23:59:59');
    }    
    // Generate a random timestamp within the range
    $randomTimestamp = mt_rand($start, $end);
    
    // Format the timestamp into the desired date format
    return date('Y-m-d H:i:s', $randomTimestamp);
}


function rand_username($string = '', $integer = '') {

    if(empty($string)) {
        $string = wordlist(rand(4,15), 1)[0];
    }

    if(empty($integer)) {
       $integer = random_int(1, 99);
    }

    return vsprintf('%s%s%d', [...sscanf(strtolower("$string-"), '%s %2s'), $integer]);
}

function wordlist($word_length = 4, $max_count = 12, $list = 'word_list.txt') {
    $file_path = config('public') . "/text/$list";
    $retwords = [];
    $total_attempts = 0;
    $max_attempts = $max_count * 10;

    if (!file_exists($file_path) || filesize($file_path) === 0) {
        return []; // File must exist and have content
    }

    $file_size = filesize($file_path);
    $handle = fopen($file_path, 'r');
    if (!$handle) {
        return []; // Return empty if file cannot be opened
    }

    // Preload all words, matching the desired length first, then fallback to any word
    $valid_words = [];
    $all_words = [];
    while (($line = fgets($handle)) !== false) {
        $words = preg_split('/\s+/', trim($line));
        foreach ($words as $word) {
            if (strlen($word) === $word_length) {
                $valid_words[] = $word; // Match the word length
            }
            $all_words[] = $word; // Collect all words
        }
    }
    fclose($handle);

    // If no valid words of the desired length are found, fallback to all words
    if (empty($valid_words)) {
        $valid_words = $all_words;
    }

    // Fill the result with random valid words, allowing repeats if necessary
    while (count($retwords) < $max_count) {
        $retwords[] = $valid_words[array_rand($valid_words)];
    }

    return $retwords;
}


function dot_replacer($input) {
    // Get the length of the input string
    $length = strlen($input);
    
    // Create a string of dots with the same length as the input string
    $dots = str_repeat('.', $length);
    
    return $dots;
}


// Function to generate a random string of characters
function rand_str($length = 7) {
    $special_chars = "!?,;.'[]={}@#$%^*()-_\/|";
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $special_chars[rand(0, strlen($special_chars) - 1)];
    }
    return $randomString;
}


// Function to generate a memory dump
function mem_dump($rows, $columns, $specialWords = [], $length = 7) {
    $memoryDump = array();

    // Insert special words into the specialPositions array
    $specialPositions = [];
    for ($i = 0; $i < count($specialWords); $i++) {
        $row = rand(0, $rows - 1);
        $col = rand(0, $columns - 1);
        $specialPositions[] = [$row, $col, $specialWords[$i]];
    }

    // Generate random strings for each cell
    for ($i = 0; $i < $rows; $i++) {
        $row = array();
        for ($j = 0; $j < $columns; $j++) {
            $cell = rand_str($length);
            // Check if this cell is a special position
            foreach ($specialPositions as $index => $pos) {
                if ($pos[0] === $i && $pos[1] === $j) {
                    // Insert special word and remove it from specialPositions array
                    $cell = $pos[2];
                    unset($specialPositions[$index]);
                    break;
                }
            }
            $row[] = $cell;
        }
        $memoryDump[] = $row;
    }

    return $memoryDump;
}

// Function to format the memory dump with memory paths
function format_dump($memoryDump) {
    $formattedDump = "";
    $rowNumber = 0;

    foreach ($memoryDump as $row) {
        // Generate a random starting memory address for each line
        $memoryAddress = "0x" . dechex(rand(4096, 6553));
        $formattedDump .= $memoryAddress . " ";
        foreach ($row as $cell) {
            $formattedDump .= " " . $cell;
        }
        $formattedDump .= "\n";
    }

    return $formattedDump;
}

function ftp_transfer($filename, $string, $mode = "get", $time = null) {
    // Convert mode to lowercase
    $mode = strtolower($mode);

    // Validate mode
    if ($mode !== "get" && $mode !== "put") {
        return "500 Invalid mode.";
    }

    // Get string size in bytes
    $byteSize = mb_strlen($string, '8bit');

    // Generate random transfer time if not provided
    if ($time === null) {
        $time = rand(1, 10);
    }

    // Calculate speed in KB/s
    $speed = $byteSize / $time / 1024; // Convert bytes to KB

    // Construct FTP-style output
    if ($mode === "get") {
        // Download (GET)
        return "Using BIN mode to transfer files.\n" .
               "200 PORT command successful\n" .
               "150 Opening ASCII mode data connection for ({$filename}) ({$byteSize} bytes)\n" .
               "|===================================================>|\n" .
               "226 Transfer complete\n" .
               "{$byteSize} bytes received in {$time} secs (" . number_format($speed, 2) . " kB/s)";
    } else {
        // Upload (PUT)
        return "Using BIN mode to transfer files.\n" .
               "200 PORT command successful\n" .
               "150 Ok to send data ({$filename}) ({$byteSize} bytes)\n" .
               "|===================================================>|\n" .
               "226 Transfer complete\n" .
               "{$byteSize} bytes sent in {$time} secs (" . number_format($speed, 2) . " kB/s)";
    }
}

function str_bytes($string) {
    return mb_strlen($string, '8bit');
}


function random_ip() {
    return long2ip(rand(0, 4294967295));
}

function remote_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]; // Get the first IP if there are multiple
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

function ipToNum(string $ip): string {
    $packed = inet_pton($ip);

    if ($packed === false) {
        throw new InvalidArgumentException("Invalid IP address: $ip");
    }

    $unpacked = unpack('H*', $packed)[1]; // hex string
    return gmp_strval(gmp_init($unpacked, 16), 10); // convert hex to decimal
}

function bootup($loops = 70, $keyphrases = []) {
    if(empty($keyphrases)) {
        $keyphrases = [" START MEMORY DISCOVERY \n", " CPUO STARTING CELL RELOCATION \n", 
        " CPUO LAUNCH EFIO \n", " CPUO STARTING EFIO \n"];
    }

    $middle_pieces = [" 1", " 0", " 0x0000A4 \n", " 0x00000000000000000 \n", 
                      " 0x000014 \n", " 0x000009 \n", " 0x000000000000E003D \n"];
    
    // Start the huge string with '*'
    $huge_string = " * \n";

    // Loop 70 times, similar to the Python code
    for ($i = 0; $i < $loops; $i++) {
        // Randomly choose between 3 to 7 middle pieces
        $num_middle_pieces = rand(3, 7);
        
        // Build the middle piece string
        $middle_piece = '';
        for ($j = 0; $j < $num_middle_pieces; $j++) {
            $middle_piece .= $middle_pieces[array_rand($middle_pieces)];
        }
        
        // Append the middle piece and a random keyphrase to the huge string
        $huge_string .= $middle_piece;
        $huge_string .= $keyphrases[array_rand($keyphrases)];
    }

    return $huge_string;
}

/**
 * -----------------------------------------------------------------------------------------
 * Based on `https://github.com/mecha-cms/mecha-cms/blob/master/system/kernel/converter.php`
 * -----------------------------------------------------------------------------------------
 */

// HTML Minifier
function minify_html($input) {
    if(trim($input) === "") return $input;
    // Remove extra white-space(s) between HTML attribute(s)
    $input = preg_replace_callback('#<([^\/\s<>!]+)(?:\s+([^<>]*?)\s*|\s*)(\/?)>#s', function($matches) {
        return '<' . $matches[1] . preg_replace('#([^\s=]+)(\=([\'"]?)(.*?)\3)?(\s+|$)#s', ' $1$2', $matches[2]) . $matches[3] . '>';
    }, str_replace("\r", "", $input));
    // Minify inline CSS declaration(s)
    if(strpos($input, ' style=') !== false) {
        $input = preg_replace_callback('#<([^<]+?)\s+style=([\'"])(.*?)\2(?=[\/\s>])#s', function($matches) {
            return '<' . $matches[1] . ' style=' . $matches[2] . minify_css($matches[3]) . $matches[2];
        }, $input);
    }
    if(strpos($input, '</style>') !== false) {
      $input = preg_replace_callback('#<style(.*?)>(.*?)</style>#is', function($matches) {
        return '<style' . $matches[1] .'>'. minify_css($matches[2]) . '</style>';
      }, $input);
    }
    if(strpos($input, '</script>') !== false) {
      $input = preg_replace_callback('#<script(.*?)>(.*?)</script>#is', function($matches) {
        return '<script' . $matches[1] .'>'. minify_js($matches[2]) . '</script>';
      }, $input);
    }

    return preg_replace(
        array(
            // t = text
            // o = tag open
            // c = tag close
            // Keep important white-space(s) after self-closing HTML tag(s)
            '#<(img|input)(>| .*?>)#s',
            // Remove a line break and two or more white-space(s) between tag(s)
            '#(<!--.*?-->)|(>)(?:\n*|\s{2,})(<)|^\s*|\s*$#s',
            '#(<!--.*?-->)|(?<!\>)\s+(<\/.*?>)|(<[^\/]*?>)\s+(?!\<)#s', // t+c || o+t
            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<[^\/]*?>)|(<\/.*?>)\s+(<\/.*?>)#s', // o+o || c+c
            '#(<!--.*?-->)|(<\/.*?>)\s+(\s)(?!\<)|(?<!\>)\s+(\s)(<[^\/]*?\/?>)|(<[^\/]*?\/?>)\s+(\s)(?!\<)#s', // c+t || t+o || o+t -- separated by long white-space(s)
            '#(<!--.*?-->)|(<[^\/]*?>)\s+(<\/.*?>)#s', // empty tag
            '#<(img|input)(>| .*?>)<\/\1>#s', // reset previous fix
            '#(&nbsp;)&nbsp;(?![<\s])#', // clean up ...
            '#(?<=\>)(&nbsp;)(?=\<)#', // --ibid
            // Remove HTML comment(s) except IE comment(s)
            '#\s*<!--(?!\[if\s).*?-->\s*|(?<!\>)\n+(?=\<[^!])#s'
        ),
        array(
            '<$1$2</$1>',
            '$1$2$3',
            '$1$2$3',
            '$1$2$3$4$5',
            '$1$2$3$4$5$6$7',
            '$1$2$3',
            '<$1$2',
            '$1 ',
            '$1',
            ""
        ),
    $input);
}

// CSS Minifier => http://ideone.com/Q5USEF + improvement(s)
function minify_css($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')|\/\*(?!\!)(?>.*?\*\/)|^\s*|\s*$#s',
            // Remove unused white-space(s)
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/))|\s*+;\s*+(})\s*+|\s*+([*$~^|]?+=|[{};,>~]|\s(?![0-9\.])|!important\b)\s*+|([[(:])\s++|\s++([])])|\s++(:)\s*+(?!(?>[^{}"\']++|"(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')*+{)|^\s++|\s++\z|(\s)\s+#si',
            // Replace `0(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)` with `0`
            '#(?<=[\s:])(0)(cm|em|ex|in|mm|pc|pt|px|vh|vw|%)#si',
            // Replace `:0 0 0 0` with `:0`
            '#:(0\s+0|0\s+0\s+0\s+0)(?=[;\}]|\!important)#i',
            // Replace `background-position:0` with `background-position:0 0`
            '#(background-position):0(?=[;\}])#si',
            // Replace `0.6` with `.6`, but only when preceded by `:`, `,`, `-` or a white-space
            '#(?<=[\s:,\-])0+\.(\d+)#s',
            // Minify string value
            '#(\/\*(?>.*?\*\/))|(?<!content\:)([\'"])([a-z_][a-z0-9\-_]*?)\2(?=[\s\{\}\];,])#si',
            '#(\/\*(?>.*?\*\/))|(\burl\()([\'"])([^\s]+?)\3(\))#si',
            // Minify HEX color code
            '#(?<=[\s:,\-]\#)([a-f0-6]+)\1([a-f0-6]+)\2([a-f0-6]+)\3#i',
            // Replace `(border|outline):none` with `(border|outline):0`
            '#(?<=[\{;])(border|outline):none(?=[;\}\!])#',
            // Remove empty selector(s)
            '#(\/\*(?>.*?\*\/))|(^|[\{\}])(?:[^\s\{\}]+)\{\}#s'
        ),
        array(
            '$1',
            '$1$2$3$4$5$6$7',
            '$1',
            ':0',
            '$1:0 0',
            '.$1',
            '$1$3',
            '$1$2$4$5',
            '$1$2$3',
            '$1:0',
            '$1$2'
        ),
    $input);
}

// JavaScript Minifier
function minify_js($input) {
    if(trim($input) === "") return $input;
    return preg_replace(
        array(
            // Remove comment(s)
            '#\s*("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\')\s*|\s*\/\*(?!\!|@cc_on)(?>[\s\S]*?\*\/)\s*|\s*(?<![\:\=])\/\/.*(?=[\n\r]|$)|^\s*|\s*$#',
            // Remove white-space(s) outside the string and regex
            '#("(?:[^"\\\]++|\\\.)*+"|\'(?:[^\'\\\\]++|\\\.)*+\'|\/\*(?>.*?\*\/)|\/(?!\/)[^\n\r]*?\/(?=[\s.,;]|[gimuy]|$))|\s*([!%&*\(\)\-=+\[\]\{\}|;:,.<>?\/])\s*#s',
            // Remove the last semicolon
            '#;+\}#',
            // Minify object attribute(s) except JSON attribute(s). From `{'foo':'bar'}` to `{foo:'bar'}`
            '#([\{,])([\'])(\d+|[a-z_][a-z0-9_]*)\2(?=\:)#i',
            // --ibid. From `foo['bar']` to `foo.bar`
            '#([a-z0-9_\)\]])\[([\'"])([a-z_][a-z0-9_]*)\2\]#i'
        ),
        array(
            '$1',
            '$1$2',
            '}',
            '$1$3',
            '$1.$3'
        ),
    $input);
}