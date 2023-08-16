<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinishedCriteriaSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('finished_criteria_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("schedule_id");
            $table->unsignedBigInteger("criteria_id");
            $table->date('start_date');
            $table->date("estimated_completion_date");
            $table->date("completed_date");
            $table->integer("completion_delay");

            $table->foreign("schedule_id")
            ->references("id")
            ->on("schedules")
            ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('finished_criteria_schedules');
    }
}
