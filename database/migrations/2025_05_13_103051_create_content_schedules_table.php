<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
                Schema::create('content_schedules', function (Blueprint $table) {
            $table->id();
                
            $table->unsignedBigInteger('content_id');
            $table->unsignedBigInteger('slot_id');
                
            $table->string('channel'); // For redundancy and filtering
            $table->date('date'); // Same as schedule_slots.date
            $table->time('start_time'); // Actual schedule start time
            $table->time('end_time');   // Calculated based on content duration
                
            $table->timestamps();
                
            $table->foreign('content_id')->references('id')->on('contents')->onDelete('cascade');
            $table->foreign('slot_id')->references('id')->on('schedule_slots')->onDelete('cascade');
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
