<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_stations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('stations', function (Blueprint $table) {
            $table->id(); // Primary Key: id
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('room')->nullable();
            $table->integer('capacity_limit')->default(0);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('stations');
    }
};