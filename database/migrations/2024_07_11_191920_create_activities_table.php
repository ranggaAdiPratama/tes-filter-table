<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivitiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activities', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unsignedBigInteger('operator_id');
            $table->unsignedBigInteger('site_id');
            $table->unsignedBigInteger('machine_id');
            $table->string('activity');
            $table->string('uom');
            $table->integer('block');
            $table->string('task');
            $table->string('start');
            $table->string('end');
            $table->integer('fuel');
            $table->string('duty');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activities');
    }
}
