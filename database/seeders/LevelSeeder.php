<?php

namespace DB\Seeders;

use Illuminate\Database\Capsule\Manager as DB;
use DB\Migrations\LevelTable;

class LevelSeeder extends LevelTable
{
    /**
     * Seed the application's database.
     */
    public static function run(): void
    {
        DB::table((new self)->table)->insert([
            ['id' => 1, 'status' => 'BEGINNER', 'credits' => 0,'min' => 2,'max' => 3],
            ['id' => 2, 'status' => 'NOVICE', 'credits' => 15,'min' => 4,'max' => 5],
            ['id' => 3, 'status' => 'SKILLED', 'credits' => 25,'min' => 6,'max' => 8],
            ['id' => 4, 'status' => 'ADVANCED', 'credits' => 50,'min' => 8,'max' => 10],
            ['id' => 5, 'status' => 'EXPERT', 'credits' => 75,'min' => 10,'max' => 12],
            ['id' => 6, 'status' => 'MASTER', 'credits' => 100,'min' => 12,'max' => 15]
        ]);
    }

}