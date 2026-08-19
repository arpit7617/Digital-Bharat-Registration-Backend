<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations to add performance indexes.
     */
    public function up(): void
    {
        try {
            Schema::table('registrations', function (Blueprint $table) {
                $table->index('city', 'idx_registrations_city');
                $table->index('category', 'idx_registrations_category');
                $table->index('created_at', 'idx_registrations_created_at');
                $table->index('payment_status', 'idx_registrations_payment_status');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('farmer_loans', function (Blueprint $table) {
                $table->index('user_id', 'idx_farmer_loans_user_id');
                $table->index('claimed_by', 'idx_farmer_loans_claimed_by');
                $table->index('status', 'idx_farmer_loans_status');
                $table->index('created_at', 'idx_farmer_loans_created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('student_loans', function (Blueprint $table) {
                $table->index('user_id', 'idx_student_loans_user_id');
                $table->index('claimed_by', 'idx_student_loans_claimed_by');
                $table->index('status', 'idx_student_loans_status');
                $table->index('created_at', 'idx_student_loans_created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('business_loans', function (Blueprint $table) {
                $table->index('user_id', 'idx_business_loans_user_id');
                $table->index('claimed_by', 'idx_business_loans_claimed_by');
                $table->index('status', 'idx_business_loans_status');
                $table->index('created_at', 'idx_business_loans_created_at');
            });
        } catch (\Exception $e) {}

        if (Schema::hasTable('farmer_insurance_applications')) {
            try {
                Schema::table('farmer_insurance_applications', function (Blueprint $table) {
                    $table->index('user_id', 'idx_farmer_ins_user_id');
                    $table->index('claimed_by', 'idx_farmer_ins_claimed_by');
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('health_insurance_applications')) {
            try {
                Schema::table('health_insurance_applications', function (Blueprint $table) {
                    $table->index('user_id', 'idx_health_ins_user_id');
                    $table->index('claimed_by', 'idx_health_ins_claimed_by');
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('motor_insurance_applications')) {
            try {
                Schema::table('motor_insurance_applications', function (Blueprint $table) {
                    $table->index('user_id', 'idx_motor_ins_user_id');
                    $table->index('claimed_by', 'idx_motor_ins_claimed_by');
                });
            } catch (\Exception $e) {}
        }

        if (Schema::hasTable('crop_registrations')) {
            try {
                Schema::table('crop_registrations', function (Blueprint $table) {
                    $table->index('user_id', 'idx_crop_reg_user_id');
                    $table->index('claimed_by', 'idx_crop_reg_claimed_by');
                });
            } catch (\Exception $e) {}
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropIndex('idx_registrations_city');
                $table->dropIndex('idx_registrations_category');
                $table->dropIndex('idx_registrations_created_at');
                $table->dropIndex('idx_registrations_payment_status');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('farmer_loans', function (Blueprint $table) {
                $table->dropIndex('idx_farmer_loans_user_id');
                $table->dropIndex('idx_farmer_loans_claimed_by');
                $table->dropIndex('idx_farmer_loans_status');
                $table->dropIndex('idx_farmer_loans_created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('student_loans', function (Blueprint $table) {
                $table->dropIndex('idx_student_loans_user_id');
                $table->dropIndex('idx_student_loans_claimed_by');
                $table->dropIndex('idx_student_loans_status');
                $table->dropIndex('idx_student_loans_created_at');
            });
        } catch (\Exception $e) {}

        try {
            Schema::table('business_loans', function (Blueprint $table) {
                $table->dropIndex('idx_business_loans_user_id');
                $table->dropIndex('idx_business_loans_claimed_by');
                $table->dropIndex('idx_business_loans_status');
                $table->dropIndex('idx_business_loans_created_at');
            });
        } catch (\Exception $e) {}
    }
};
