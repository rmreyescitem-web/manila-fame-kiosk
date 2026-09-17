<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->string('booth_code'); // e.g., L-19, I-25, or "BUYERS LOUNGE"
            $table->string('section')->nullable(); // e.g., Artisans Village 1, Comfort Room
            $table->string('start_cell'); // e.g., "D4"
            $table->string('end_cell')->nullable(); // e.g., "I4" (used if merged)
            $table->boolean('is_merged')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booths');
    }
};