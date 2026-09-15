<?php

use App\Models\Calendar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labels', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Calendar::class)->constrained()->cascadeOnDelete();
            $table->string('color');
            $table->string('name');
            $table->timestamps();

            $table->unique(['calendar_id', 'color']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
