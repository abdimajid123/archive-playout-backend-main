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
        Schema::create('rawfiles', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // Folder label
            $table->text('description')->nullable();
            $table->text('path');          // Path to folder on server
            $table->string('channel');     // Associated channel (AstaanTV, etc.)
            $table->enum('status', ['unedited', 'edited'])->default('unedited'); // Status of raw data
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rawfiles');
    }
};
