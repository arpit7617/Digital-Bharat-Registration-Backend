<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farmer_insurance_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            // Personal
            $table->string('farmer_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('aadhaar')->nullable();

            // Location
            $table->string('village')->nullable();
            $table->string('tehsil')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();

            // Land
            $table->string('land_size')->nullable();        // acres
            $table->string('khasra_number')->nullable();
            $table->string('survey_number')->nullable();

            // Crop
            $table->string('crop_name')->nullable();
            $table->string('season')->nullable();           // Kharif / Rabi
            $table->string('sowing_date')->nullable();
            $table->string('expected_harvest')->nullable();

            // Insurance
            $table->decimal('sum_insured', 12, 2)->nullable();
            $table->decimal('premium_amount', 12, 2)->nullable();

            // Bank
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc')->nullable();

            $table->string('status')->default('Pending');
            $table->json('details')->nullable();            // full form payload
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farmer_insurance_applications');
    }
};
