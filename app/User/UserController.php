<?php

namespace App\User;

use Lib\Session;
use Lib\Input;

use App\User\UserModel as User;

use App\User\UserService as Auth;
use App\Host\HostService as Host;
use App\AppService as App;

use App\AppController;

class UserController extends AppController
{
    public function login() 
    {
        // Check if the user is already blocked
        Auth::blocked();

        if(!Auth::check() && $this->data) {

            $input = App::auth($this->data);

            if(Auth::login($input['username'], $input['password'])) {
                Host::attempt(0, Auth::id());
                sleep(1);
                echo 'SUCCESS: LOGON ACCEPTED';
                exit;  

            } else {
                echo <<< EOT
                ERROR: ACCESS DENIED. INVALID CREDENTIALS!
                EOT;
                exit;
            }    
        }
    }

    public function user() 
    {
        $user = Auth::data();
        $password = isset($user->password) ? base64_encode($user->password) : null;

        echo "ACCESS CODE: {$user->code} \n";
        echo "SIGNUP: {$user->created_at} \n";
        echo "USERNAME: {$user->username} \n";
        echo "PASSWORD: {$password} \n";
        echo "LEVEL: {$user->level_id} \n";
        echo "XP: {$user->xp} \n";
        echo "REP: {$user->rep} \n";
    }

    public function password()
    {
        if(!Auth::check() && $this->data) {
            Auth::data()->update([
                'password' => $this->data
            ]);
    
            echo 'SUCCESS: PASSWORD CHANGED';
        }
    }

    public function enroll() 
    {
        // Check if the user is already blocked
        Auth::blocked();

        $input = App::auth($this->data);

        if(!Auth::check() && $this->data) {

            $code = Session::get('code');
            $username = $input['username'];
            $password = $input['password'];

            if (User::where('username', '=', $username)->exists()) {
                echo 'ERROR: USERNAME TAKEN!';
                exit;
             }

             User::create([
                'username' => $username,
                'email' => "@$username",
                'fullname' => ucfirst($username),
                'password' => $password,
                'code' => $code
            ]);
        }
        
        if(Auth::login($input['username'], $input['password'])) {
            Host::attempt(0, Auth::id());
            sleep(1);
            echo 'SUCCESS: LOGON ACCEPTED';
            exit;  

        } else {
            echo 'ERROR: ACCESS DENIED!';
            exit;
        }   
    }

    public function logout() 
    {
        Auth::logout();
    }

    public function unlink()
    {
        Auth::uplink(false);
    }
}