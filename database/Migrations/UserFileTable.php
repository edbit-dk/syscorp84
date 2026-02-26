<?php

namespace DB\Migrations;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

use App\User\UserModel as User;

class UserFileTable extends User
{
    protected $table = 'user_file';

    public static function up()
    {
        DB::schema()->disableForeignKeyConstraints();
        DB::schema()->dropIfExists((new self)->table);

        DB::schema()->create((new self)->table, function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('file_id');
            
            // Specifikke metadata for spillerens kopi
            $table->integer('version')->default(1); // Til opgradering af værktøjer
            $table->boolean('is_active')->default(false); // Er værktøjet "loaded" i terminalen?
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('file_id')->references('id')->on('files')->onDelete('cascade');
            $table->datetimes();
            
            // Sikrer at spilleren ikke har 10 kopier af det præcis samme værktøj-ID
            $table->unique(['user_id', 'file_id']);
        });
    }

    public static function down()
    {
        DB::schema()->drop((new self)->table);
    }
}

