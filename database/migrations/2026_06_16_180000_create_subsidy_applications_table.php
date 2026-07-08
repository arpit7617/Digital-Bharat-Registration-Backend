<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subsidy_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();

            // Personal
            $table->string('applicant_name')->nullable();
            $table->string('father_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('aadhaar')->nullable();

            // Location
            $table->string('village')->nullable();
            $table->string('tehsil')->nullable();
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();

            // Subsidy Details
            $table->string('subsidy_type')->nullable();  // e.g. Fertilizer, Solar Pump, Seed, etc.
            $table->string('scheme_name')->nullable();   // e.g. PM-KUSUM, DBT
            $table->text('purpose')->nullable();

            // Land
            $table->string('land_size')->nullable();
            $table->string('khasra_number')->nullable();

            // Bank
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('ifsc')->nullable();

            $table->string('status')->default('Pending');
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subsidy_applications');
    }
};
