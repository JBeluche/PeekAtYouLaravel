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
            // Generated on the device so a retried create returns the same row.
            $table->uuid('client_uuid')->nullable();
            $table->string('color');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            // Colour uniqueness is enforced among live labels in validation only,
            // so a deleted label does not block reusing its colour.
            $table->index(['calendar_id', 'color']);
            $table->unique(['calendar_id', 'client_uuid']);
            $table->index(['calendar_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labels');
    }
};
