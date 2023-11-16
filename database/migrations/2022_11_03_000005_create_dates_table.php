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
        Schema::create('calendar_dates', function (Blueprint $table) {
            $table->id();
            $table->string('long_note')->nullable();
            $table->string('displayed_note', 42)->nullable();
            $table->date('date');
    

            $table->unsignedBigInteger('calendar_id');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');

            $table->unsignedBigInteger('color_association_id');
            $table->foreign('color_association_id')->references('id')->on('color_associations')->onDelete('cascade');

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
        Schema::dropIfExists('calendar_dates');
    }
};
