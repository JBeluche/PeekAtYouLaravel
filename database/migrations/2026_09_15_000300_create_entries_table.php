<?php

use App\Enums\EntrySymbol;
use App\Models\Calendar;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Calendar::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->text('text')->nullable();
            $table->string('pen_color')->nullable();
            $table->string('symbol')->default(EntrySymbol::None->value);
            $table->string('mark_color')->nullable();
            $table->timestamps();

            $table->index(['calendar_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
