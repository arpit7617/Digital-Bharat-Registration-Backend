<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('motor_insurance_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('applicant_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('aadhaar')->nullable();
            $table->string('pan')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            // Vehicle
            $table->string('vehicle_type')->nullable(); // Two Wheeler / Four Wheeler / Commercial
            $table->string('vehicle_make')->nullable(); // Maruti / Honda etc
            $table->string('vehicle_model')->nullable();
            $table->string('vehicle_year')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('engine_number')->nullable();
            $table->string('chassis_number')->nullable();
            $table->decimal('vehicle_value', 14, 2)->nullable(); // IDV
            // Plan
            $table->string('plan_type')->nullable(); // Comprehensive / Third Party
            $table->string('insurer_name')->nullable();
            $table->decimal('premium_amount', 12, 2)->nullable();
            $table->string('policy_term')->nullable();
            // Previous
            $table->boolean('has_previous_policy')->default(false);
            $table->string('previous_policy_number')->nullable();
            $table->string('previous_insurer')->nullable();
            $table->string('claim_history')->nullable(); // No Claim / 1 Claim etc
            // Nominee
            $table->string('nominee_name')->nullable();
            $table->string('nominee_relation')->nullable();
            // Extra
            $table->string('status')->default('Pending');
            $table->unsignedBigInteger('claimed_by')->nullable();
            $table->json('details')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('registrations')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('motor_insurance_applications');
    }
};
