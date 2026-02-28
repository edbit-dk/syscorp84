<?php

namespace DB\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

use App\User\UserModel as User;

class UserTable extends User
{
    public static function up()
    {
        DB::schema()->disableForeignKeyConstraints();
        DB::schema()->dropIfExists((new self)->table);

        DB::schema()->create((new self)->table, function (Blueprint $table) {
            $table->increments('id');
            $table->string('username')->unique();
            $table->string('email')->unique()->nullable();
            $table->enum('role', ['USER', 'OPERATOR', 'ADMIN'])->default('USER');
            $table->string('code')->unique();
            $table->string('password')->nullable();
            $table->boolean('is_admin')->default(0);
            $table->boolean('is_active')->default(1);
            $table->integer('credits')->default(0);
            $table->ipAddress('ip')->nullable();
            $table->timestamp('last_login')->nullable();
            $table->datetimes();
        });
    }

    public static function down()
    {
        DB::schema()->drop((new self)->table);
    }
}

