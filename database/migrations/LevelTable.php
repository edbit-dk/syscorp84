<?php

namespace DB\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

use App\Level\LevelModel as Level;

class LevelTable extends Level
{
    public static function up()
    {
        DB::schema()->disableForeignKeyConstraints();
        DB::schema()->dropIfExists((new self)->table);

        DB::schema()->create((new self)->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('status');
            $table->integer('credits')->default(0);
            $table->unsignedTinyInteger('min');
            $table->unsignedTinyInteger('max');
        });
    }

    public static function down()
    {
        DB::schema()->drop((new self)->table);
    }
}

