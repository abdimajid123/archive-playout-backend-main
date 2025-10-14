<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('channel_rules', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->unique(); // one rule per channel
            $table->integer('min_content_per_day')->default(1);
            $table->integer('max_content_per_day');
            $table->integer('slot_duration_minutes')->default(30);
            $table->json('preferred_content_types')->nullable(); // e.g. ["documentary","series"]
            $table->string('scheduling_algorithm')->default('fifo'); // or "priority", "random"
            $table->integer('cooldown_days')->default(30); // instead of repeat_cooldown_minutes
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('channel_rules');
    }
};

