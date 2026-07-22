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
        Schema::create('real_estate_enquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('registrations')->onDelete('cascade');
            $table->string('action'); // Buy, Sell, Rent
            $table->string('property_type'); // Residential, Commercial, Land
            $table->string('location');
            $table->string('budget')->nullable();
            $table->string('status')->default('Pending');
            $table->unsignedBigInteger('claimed_by')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('real_estate_enquiries');
    }
};
