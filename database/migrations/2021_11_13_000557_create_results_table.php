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
            $table->integer('count_above');
            $table->integer('count_below');
            $table->integer('count_middle')->nullable();
            $table->string('sd_above')->nullable();
            $table->string('sd_below')->nullable();
            $table->string('ave')->nullable();
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
