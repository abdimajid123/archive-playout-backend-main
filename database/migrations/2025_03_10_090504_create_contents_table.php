<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public $withinTransaction = false;

    public function up()
{
    Schema::create('contents', function (Blueprint $table) {
        $table->id(); // ✅ Auto-increment integer ID
        $table->string('title');
        $table->string('description');
        $table->string('channel');
        $table->integer('season')->nullable();
        $table->integer('episode')->nullable();
        $table->string('type');
        $table->json('category'); // ✅ convert to JSON
        $table->integer('year')->nullable();
        $table->time('duration')->nullable(); // ✅ video length
        $table->string('country')->nullable();
        $table->timestamps();
    });
}



    public function down(): void
    {
        Schema::dropIfExists('contents');
    }
};
