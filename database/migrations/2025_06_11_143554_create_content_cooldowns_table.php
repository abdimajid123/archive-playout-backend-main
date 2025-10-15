<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public $withinTransaction = false;

    public function up(): void
    {
        Schema::create('content_cooldowns', function (Blueprint $table) {
            $table->id();

            $table->foreignId('content_id')->constrained('contents')->onDelete('cascade');
            $table->string('channel'); // For channel-specific cooldown
            $table->timestamp('last_used_at');
            $table->integer('cooldown_days')->default(30); // Default: 30 days cooldown

            $table->timestamps();

            $table->unique(['content_id', 'channel']); // Prevent duplicate per content/channel
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_cooldowns');
    }
};