<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGpsDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gps_data', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uid');
            $table->decimal('lat');
            $table->decimal('lon');
            $table->decimal('altitude');
            $table->decimal('speed');
            $table->decimal('bearing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gps_data');
    }
}
