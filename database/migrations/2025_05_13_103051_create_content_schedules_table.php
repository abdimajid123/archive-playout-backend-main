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
        Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('content_id')->constrained('contents')->cascadeOnDelete();
            $table->foreignId('slot_id')->constrained('schedule_slots')->cascadeOnDelete();

            $table->string('channel');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_schedules');
    }
};
