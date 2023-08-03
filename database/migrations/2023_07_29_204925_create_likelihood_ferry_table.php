<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLikelihoodFerryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('likelihood_ferry', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('criteria_id');

            $table->foreign('criteria_id')
            ->references('id')
            ->on('ferry_criterias')
            ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('likelihood_ferry');
    }
}
