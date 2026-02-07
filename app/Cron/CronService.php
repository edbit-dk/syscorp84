<?php

namespace App\Cron;

use App\Host\HostModel as Hosts;

use App\User\UserService as User;
use App\Host\HostService as Host;

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

        // $css = file_get_contents(BASE_PATH . '/resources/css/reset.css');
        // $css .= file_get_contents(BASE_PATH . '/resources/css/main.css');
        // $css .= file_get_contents(BASE_PATH . '/resources/css/terminal.css');
        $css = file_get_contents(BASE_PATH . '/resources/css/default.css');

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
}