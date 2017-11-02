<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BotStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_state', function (Blueprint $table) {
            $table->increments('id');
            $table->bigInteger('chat_id');
            $table->bigInteger('user_id');
            $table->string("user_name");
            $table->string("username");
            $table->bigInteger("message_id");
            $table->integer('current_state');
            $table->text('last_message');
            $table->integer('page_index');
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
        Schema::dropIfExists('bot_state');
    }
}
