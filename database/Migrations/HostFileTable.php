<?php

namespace DB\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class HostFileTable
{
    protected $table = 'host_file';

    public static function up()
    {
        DB::schema()->disableForeignKeyConstraints();
        DB::schema()->dropIfExists((new self)->table);
        
        DB::schema()->create((new self)->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('host_id');
            $table->unsignedInteger('file_id');
            $table->unsignedInteger('owner_id');
            $table->index('host_id');
            $table->index('file_id');
            $table->index('owner_id');
            $table->unique(['host_id', 'file_id', 'owner_id']);
            $table->datetimes();
            $table->foreign('host_id')->references('id')->on('hosts')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('users');
        });
        
    }

    public static function down()
    {
        DB::table((new self)->table, function (Blueprint $table) {
            $table->dropUnique(['host_id', 'file_id']);
            $table->dropIndex(['host_id']);
            $table->dropIndex(['file_id']);
        });

        DB::schema()->drop((new self)->table);
    }
}

