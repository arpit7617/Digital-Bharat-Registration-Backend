<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('health_insurance_applications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('applicant_name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('email')->nullable();
            $table->string('aadhaar')->nullable();
            $table->string('pan')->nullable();
            $table->date('dob')->nullable();
            $table->string('gender')->nullable();
            $table->integer('age')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('pincode')->nullable();
            // Plan
            $table->string('plan_type')->nullable(); // Individual / Family / Senior
            $table->decimal('sum_insured', 12, 2)->nullable();
            $table->decimal('premium_amount', 12, 2)->nullable();
            $table->integer('members_covered')->nullable();
            $table->string('insurer_name')->nullable(); // Insurance company
            $table->string('policy_term')->nullable(); // 1 year / 2 years
            // Medical
            $table->boolean('pre_existing_disease')->default(false);
            $table->text('disease_details')->nullable();
            // Nominee
            $table->string('nominee_name')->nullable();
            $table->string('nominee_relation')->nullable();
            $table->string('nominee_dob')->nullable();
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
        Schema::dropIfExists('health_insurance_applications');
    }
};
