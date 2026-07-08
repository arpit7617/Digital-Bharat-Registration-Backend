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
        Schema::table('business_loans', function (Blueprint $table) {
            $table->json('details')->nullable();
        });

        Schema::table('student_loans', function (Blueprint $table) {
            $table->json('details')->nullable();
        });

        Schema::table('farmer_loans', function (Blueprint $table) {
            $table->json('details')->nullable();
        });

        Schema::table('crop_registrations', function (Blueprint $table) {
            $table->json('details')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_loans', function (Blueprint $table) {
            $table->dropColumn('details');
        });

        Schema::table('student_loans', function (Blueprint $table) {
            $table->dropColumn('details');
        });

        Schema::table('farmer_loans', function (Blueprint $table) {
            $table->dropColumn('details');
        });

        Schema::table('crop_registrations', function (Blueprint $table) {
            $table->dropColumn('details');
        });
    }
};
