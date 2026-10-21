<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendars', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'owner_id')->constrained()->cascadeOnDelete();
            // Generated on the device so a retried create returns the same row.
            $table->uuid('client_uuid')->nullable();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['owner_id', 'client_uuid']);
            $table->index(['owner_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendars');
    }
};
