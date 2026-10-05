<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('booths', function (Blueprint $table) {
            $table->id();
            $table->string('booth_code'); // e.g., A-01, L-19, MAIN ENTRANCE
            $table->string('section')->nullable(); // e.g., Artisans Village, Column A, Facility
            $table->string('start_cell'); // e.g., "A47"
            $table->string('end_cell')->nullable(); // e.g., "AH1" (used for merged blocks)
            $table->boolean('is_merged')->default(false);
            $table->string('type')->default('booth'); // 'booth', 'wall', 'entrance', 'facility'
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booths');
    }
};
