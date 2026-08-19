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
        Schema::table('registrations', function (Blueprint $table) {
            if (!Schema::hasColumn('registrations', 'payment_acknowledged')) {
                $table->boolean('payment_acknowledged')->default(false)->after('registration_type');
            }
            if (!Schema::hasColumn('registrations', 'payment_status')) {
                $table->string('payment_status')->default('pending')->after('payment_acknowledged');
            }
            if (!Schema::hasColumn('registrations', 'payment_id')) {
                $table->string('payment_id')->nullable()->after('payment_status');
            }
            if (!Schema::hasColumn('registrations', 'transaction_id')) {
                $table->string('transaction_id')->nullable()->after('payment_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            $table->dropColumn(['payment_acknowledged', 'payment_status', 'payment_id', 'transaction_id']);
        });
    }
};
