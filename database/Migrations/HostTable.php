<?php

namespace DB\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

use App\Host\HostModel as Host;

class HostTable extends Host
{
    public static function up()
    {

        DB::schema()->disableForeignKeyConstraints();
        DB::schema()->dropIfExists((new self)->table);
        
        DB::schema()->create((new self)->table, function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('hostname')->unique();
            $table->string('password')->nullable();
            $table->text('welcome')->nullable();
            $table->string('org')->nullable();
            $table->enum('os', ['SU/DOS V1.10', 'SU/DOS V3.0', 'SU/DOS V4.0', 'MU/DOS V1.4', '4.3 BSD VAX-11/780', '[REDACTED]'])->default('SU/DOS V1.10');
            $table->string('location')->nullable();
            $table->ipAddress('ip')->unique();
            $table->text('motd')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(1);
            $table->boolean('is_network')->default(0);
            $table->integer('credits')->default(0);
            $table->datetimes();
        });

    }

    public static function down()
    {
        DB::schema()->drop((new self)->table);
    }
}

