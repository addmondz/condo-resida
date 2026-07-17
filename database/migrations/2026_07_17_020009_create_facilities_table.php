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
        Schema::create('facilities', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->foreignId('property_id')->constrained('properties')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('rules')->nullable();
            $table->integer('capacity')->nullable();
            $table->string('image')->nullable();
            $table->time('opening_time')->nullable();
            $table->time('closing_time')->nullable();
            $table->integer('slot_duration')->default(60);
            $table->integer('max_bookings_per_resident')->default(2);
            $table->integer('advance_booking_days')->default(14);
            $table->integer('cancellation_hours')->default(24);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_under_maintenance')->default(false);
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facilities');
    }
};
