<?php

namespace App\Cron;

use App\Host\HostModel as Hosts;

use App\User\UserService as User;
use App\Host\HostService as Host;

use ZipArchive;
use Lib\Input;

class CronService
{
    public static function minify()
    {
        $js = file_get_contents(BASE_PATH . '/resources/js/main.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/events.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/helpers.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/input.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/commands.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/prompts.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/terminal.js');
        $js .= file_get_contents(BASE_PATH . '/resources/js/music.js');

        $css = file_get_contents(BASE_PATH . '/resources/css/reset.css');
        $css .= file_get_contents(BASE_PATH . '/resources/css/fonts.css');
        $css .= file_get_contents(BASE_PATH . '/resources/css/default.css');

        file_put_contents(BASE_PATH . '/public/js/app.js', $js);
        file_put_contents(BASE_PATH . '/public/js/app.min.js', minify_js($js));

        file_put_contents(BASE_PATH . '/public/css/app.css', $css);
        file_put_contents(BASE_PATH . '/public/css/app.min.css', minify_css($css));

        print_r(file_get_contents(BASE_PATH . '/public/js/app.min.js'));
        print_r(file_get_contents(BASE_PATH . '/public/css/app.min.css'));
    }

    public static function stats($host_id)
    {
        if($host = Hosts::find($host_id)) {
            $date = date('H:i l, F j, Y', time());

            $users = $host->users()->count();
            $hosts = $host->nodes()->count();
    
            $motd = "Local time is {$date}.\nThere are {$users} local users. There are {$hosts} hosts on the network.";
    
            Hosts::where('id', $host_id)->update([
                'motd' => $motd
            ]);
        }

    }

    public static function update()
    {
        
        $secretKey = config('key');
        $providedKey = Input::get('key'); // Eller $_GET['key']

        if ($providedKey !== $secretKey) {
            die('UNAUTHORIZED!');
        }

        // Definer projektets rodmappe (Vigtigt!)
        $basePath = config('path'); // Juster så den peger på din rod
        $tempZip  = $basePath . '/package.zip';
        
        $repoUser = config('repo_user');
        $repoName = config('repo_name');
        $zipUrl   = "https://github.com/$repoUser/$repoName/archive/refs/heads/main.zip";

        if (copy($zipUrl, $tempZip)) {
            $zip = new ZipArchive;
            if ($zip->open($tempZip) === TRUE) {
                $rootInZip = $zip->getNameIndex(0);

                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $zipEntry = $zip->getNameIndex($i);
                    $relativePath = substr($zipEntry, strlen($rootInZip));

                    if (empty($relativePath)) continue;

                    // Beskyt config-mappen
                    if (strpos($relativePath, 'config/') === 0) continue;

                    $fullPath = $basePath . '/' . $relativePath;

                    if (substr($zipEntry, -1) === '/') {
                        if (!is_dir($fullPath)) mkdir($fullPath, 0755, true);
                    } else {
                        copy("zip://".$tempZip."#".$zipEntry, $fullPath);
                    }
                }
                $zip->close();
                unlink($tempZip);
                echo "SYSTEM UPDATED!";
                return;
            }
        }
        echo "UPDATE FAILED!";
    }
}