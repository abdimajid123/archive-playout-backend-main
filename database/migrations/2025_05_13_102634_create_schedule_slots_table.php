<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('schedule_slots', function (Blueprint $table) {
            $table->id();
            $table->string('channel'); // Channel name directly from contents table
            $table->date('date'); // Specific date for the slot
            $table->time('start_time'); // When the slot starts
            $table->time('end_time');   // When the slot ends
            $table->timestamps(); // ✅ Fixed here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_slots');
    }
};
