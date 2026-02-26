<?php

namespace DB\Seeders;

use Illuminate\Database\Capsule\Manager as DB;
use DB\Migrations\UserFileTable;


class UserFileSeeder extends UserFileTable
{
    /**
     * Seed the application's database.
     */
    public static function run(): void
    {
        $nodes = require BASE_PATH . '/config/user_files.php';
        $chunkSize = 500; // Adjust based on server capabilities

        DB::beginTransaction();
        try {
            foreach (array_chunk($nodes, $chunkSize) as $chunk) {
                DB::table((new self)->table)->insert($chunk);
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }

        /* Batch insert many nodes
        $chunkSize = 500;
        $relations = Host::relations();

        DB::connection()->beginTransaction();
            try {
                foreach (array_chunk($relations, $chunkSize) as $chunk) {
                    DB::table((new self)->table)->insert($chunk);
                }
                    DB::connection()->commit();
                    echo "Relations created: " . count($relations) . "\n";
            } catch (\Exception $e) {
                    DB::connection()->rollBack();
                    echo "EROOR: " . $e->getMessage();
            }
        */        

    }
    
}