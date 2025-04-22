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
        Schema::create('event_gallery', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id')->nullable(); // Allow null for galleries not tied to events
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('images'); // Store image paths as JSON
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_gallery');
    }
};