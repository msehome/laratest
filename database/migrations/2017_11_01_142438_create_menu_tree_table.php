<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenuTreeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menu_tree', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('parent_id');
            $table->integer('order')->default(0);
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('details')->nullable();
            $table->string('image')->nullable();
            $table->integer('contents')->nullable();
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
        Schema::dropIfExists('menu_tree');
    }
}
