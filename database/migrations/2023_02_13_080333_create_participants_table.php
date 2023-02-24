<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('chat_session_id')->constrained();
            $table->string('nickname')->nullable();
            // $table->string('state'); // pending state (you can add contact but it will be still in pending state until other peer accept to reply), active state (other peer reply back), banned state (other peer don't want to receive message from you)
            $table->boolean('is_owner')->default(false);
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
        Schema::dropIfExists('participants');
    }
};
