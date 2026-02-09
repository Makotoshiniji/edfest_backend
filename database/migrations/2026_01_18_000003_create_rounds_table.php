<?php
// database/migrations/xxxx_xx_xx_xxxxxx_create_rounds_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id(); // Primary Key: id
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rounds');
    }
};