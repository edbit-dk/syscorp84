<?php

namespace DB\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

use App\File\FileModel as File;

class FileTable extends File
{
    public static function up()
    {
        DB::schema()->disableForeignKeyConstraints();
        DB::schema()->dropIfExists((new self)->table);

        DB::schema()->create((new self)->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('filename');
            $table->string('type')->default('txt'); // txt, exe, sys, log
            $table->integer('size')->unsigned()->default(1);
            $table->string('encrypt_key')->nullable();
            $table->longText('content')->nullable();
            $table->datetimes();
        });
    }

    public static function down()
    {
        DB::schema()->drop((new self)->table);
    }
}

