<?php

namespace App\Setup;

use DB\Migrations\LevelTable;
use DB\Migrations\UserTable;
use DB\Migrations\HostTable;
use DB\Migrations\HostUserTable;
use DB\Migrations\HostNodeTable;
use DB\Migrations\HostFileTable;
use DB\Migrations\HelpTable;
use DB\Migrations\EmailTable;
use DB\Migrations\FileTable;
use DB\Migrations\UserFileTable;
use DB\Seeders\LevelSeeder;
use DB\Seeders\UserSeeder;
use DB\Seeders\HostSeeder;
use DB\Seeders\HostUserSeeder;
use DB\Seeders\HostUnixUserSeeder;
use DB\Seeders\HostNodeSeeder;
use DB\Seeders\HelpSeeder;
use DB\Seeders\FileSeeder;
use DB\Seeders\HostFileSeeder;
use DB\Seeders\UserFileSeeder;

class SetupService
{
    public static function install()
    {
        self::system();
        self::users();
        self::hosts();
        self::nodes();
        self::accounts();
        self::files();
        self::help();
    }

    public static function system()
    {
        LevelTable::up();
        LevelSeeder::run();

        EmailTable::up();

        HelpTable::up();
        HelpSeeder::run();
    }

    public static function nodes()
    {
        HostNodeTable::up();
        HostNodeSeeder::run();
    }

    public static function accounts()
    {
        HostUserTable::up();
        //HostUnixUserSeeder::run();
        //HostUserSeeder::run();
    }

    public static function users()
    {
        UserTable::up();
        UserSeeder::run();
    }

    public static function hosts()
    {
        HostTable::up();
        HostSeeder::run();
    }

    public static function files()
    {
        FileTable::up();
        HostFileTable::up();
        UserFileTable::up();
        FileSeeder::run();
        HostFileSeeder::run();
        UserFileSeeder::run();  
    }

    public static function help()
    {
        HelpTable::up();
        HelpSeeder::run();
    }

    
}