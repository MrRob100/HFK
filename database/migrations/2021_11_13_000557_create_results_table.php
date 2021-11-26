<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->string('symbol1');
            $table->string('symbol2');
            $table->string('candle_type');
            $table->date('start');
            $table->date('end');
            $table->integer('middles');
            $table->integer('oneup');
            $table->integer('twoup');
            $table->integer('threeup');
            $table->integer('fourup');
            $table->integer('fiveup');
            $table->integer('sixup');
            $table->integer('sevenup');
            $table->integer('eightup');
            $table->integer('nineup');
            $table->integer('tenup');
            $table->integer('onedown');
            $table->integer('twodown');
            $table->integer('threedown');
            $table->integer('fourdown');
            $table->integer('fivedown');
            $table->integer('sixdown');
            $table->integer('sevendown');
            $table->integer('eightdown');
            $table->integer('ninedown');
            $table->integer('tendown');
            $table->integer('upneighbours');
            $table->integer('downneighbours');
            $table->integer('usn');
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
        Schema::dropIfExists('results');
    }
}
