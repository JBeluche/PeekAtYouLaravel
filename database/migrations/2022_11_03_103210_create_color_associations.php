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
        Schema::create('color_associations', function (Blueprint $table) {
        
            $table->id();
            $table->string('association_text');
            $table->unsignedBigInteger('color_id');
            $table->unsignedBigInteger('calendar_id');

            $table->foreign('color_id')->references('id')->on('colors')->onDelete('cascade');
            $table->foreign('calendar_id')->references('id')->on('calendars')->onDelete('cascade');

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
        Schema::dropIfExists('calendar_color');
    }
};
