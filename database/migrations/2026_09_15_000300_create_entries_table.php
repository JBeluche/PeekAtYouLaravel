<?php

use App\Enums\EntrySymbol;
use App\Models\Calendar;
use App\Models\Label;
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
            // The day's mark. Null means unmarked; deleting a label unmarks the
            // days that used it rather than deleting the note itself.
            $table->foreignIdFor(Label::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index(['calendar_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('entries');
    }
};
