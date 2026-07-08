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
        // Student Loans Table
        if (!Schema::hasTable('student_loans')) {
            Schema::create('student_loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('registrations')->onDelete('cascade');
                $table->string('college_name');
                $table->string('course_name');
                $table->decimal('amount', 15, 2);
                $table->string('status')->default('Pending');
                $table->timestamps();
            });
        }

        // Business Loans Table
        if (!Schema::hasTable('business_loans')) {
            Schema::create('business_loans', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('registrations')->onDelete('cascade');
                $table->decimal('amount', 15, 2);
                $table->string('purpose');
                $table->integer('tenure');
                $table->string('status')->default('Pending');
                $table->timestamps();
            });
        }

        // Job Postings Table
        if (!Schema::hasTable('job_postings')) {
            Schema::create('job_postings', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('registrations')->onDelete('cascade');
                $table->string('job_title');
                $table->text('description');
                $table->string('salary_range');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_loans');
        Schema::dropIfExists('business_loans');
        Schema::dropIfExists('job_postings');
    }
};
