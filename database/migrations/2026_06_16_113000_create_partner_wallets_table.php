<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table) {
            if (! Schema::hasColumn('registrations', 'registration_type')) {
                $table->string('registration_type')->default('normal')->index();
            }
            if (! Schema::hasColumn('registrations', 'is_partner')) {
                $table->boolean('is_partner')->default(false)->index();
            }
            if (! Schema::hasColumn('registrations', 'registration_fee')) {
                $table->integer('registration_fee')->nullable();
            }
            if (! Schema::hasColumn('registrations', 'partner_code')) {
                $table->string('partner_code')->nullable()->unique();
            }
            if (! Schema::hasColumn('registrations', 'referred_partner_code')) {
                $table->string('referred_partner_code')->nullable()->index();
            }
            if (! Schema::hasColumn('registrations', 'wallet_balance')) {
                $table->decimal('wallet_balance', 12, 2)->default(0);
            }
        });

        if (! Schema::hasTable('partner_wallet_transactions')) {
            Schema::create('partner_wallet_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('partner_user_id')->index();
                $table->string('partner_code')->index();
                $table->unsignedBigInteger('referee_user_id')->nullable()->index();
                $table->decimal('amount', 12, 2);
                $table->string('source')->default('registration_referral');
                $table->timestamps();

                $table->index(['partner_user_id', 'source']);
                $table->unique(['partner_user_id', 'referee_user_id', 'source'], 'partner_referee_source_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('partner_wallet_transactions')) {
            Schema::dropIfExists('partner_wallet_transactions');
        }

        Schema::table('registrations', function (Blueprint $table) {
            foreach ([
                'registration_type',
                'is_partner',
                'registration_fee',
                'partner_code',
                'referred_partner_code',
                'wallet_balance',
            ] as $column) {
                if (Schema::hasColumn('registrations', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
