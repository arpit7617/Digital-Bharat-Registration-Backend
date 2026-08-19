<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('registrations') && !Schema::hasColumn('registrations', 'registration_source')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->string('registration_source')->default('portal')->after('registration_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('registrations') && Schema::hasColumn('registrations', 'registration_source')) {
            Schema::table('registrations', function (Blueprint $table) {
                $table->dropColumn('registration_source');
            });
        }
    }
};
