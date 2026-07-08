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
            $table->unsignedBigInteger('claimed_by')->nullable();
        });

        Schema::table('student_loans', function (Blueprint $table) {
            $table->unsignedBigInteger('claimed_by')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_loans', function (Blueprint $table) {
            $table->dropColumn('claimed_by');
        });

        Schema::table('student_loans', function (Blueprint $table) {
            $table->dropColumn('claimed_by');
        });
    }
};
