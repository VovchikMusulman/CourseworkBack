<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // Добавляем только city, так как address уже существует
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->after('phone')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('city');
        });
    }
};
