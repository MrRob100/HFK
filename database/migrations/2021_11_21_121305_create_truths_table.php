<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTruthsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('truths', function (Blueprint $table) {
            $table->id();
            $table->string('pair');
            $table->string('unix');
            $table->string('o');
            $table->string('h');
            $table->string('l');
            $table->string('c');
            $table->string('ema');
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
        Schema::dropIfExists('truths');
    }
}
