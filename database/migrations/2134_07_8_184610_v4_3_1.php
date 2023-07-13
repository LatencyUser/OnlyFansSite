<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V431 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('payment_requests')) {
            Schema::table('payment_requests', function (Blueprint $table) {
                $table->index('status');
                $table->index('type');
            });
        }

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->index('status');
            });
        }

        if (Schema::hasTable('streams')) {
            Schema::table('streams', function (Blueprint $table) {
                $table->index('slug');
                $table->index('is_public');
                $table->index('requires_subscription');
            });
        }

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->index('paypal_plan_id');
                $table->index('ccbill_subscription_id');
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->index('stripe_transaction_id');
                $table->index('stripe_session_id');
                $table->index('paypal_payer_id');
                $table->index('paypal_transaction_id');
                $table->index('paypal_transaction_token');
                $table->index('coinbase_charge_id');
                $table->index('coinbase_transaction_token');
                $table->index('nowpayments_payment_id');
                $table->index('nowpayments_order_id');
                $table->index('ccbill_payment_token');
                $table->index('ccbill_transaction_id');
                $table->index('ccbill_subscription_id');
                $table->index('status');
                $table->index('type');
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->index('enable_2fa');
                $table->index('enable_geoblocking');
            });
        }

        if (Schema::hasTable('user_codes')) {
            Schema::table('user_codes', function (Blueprint $table) {
                $table->index('code');
            });
        }

        if (Schema::hasTable('user_devices')) {
            Schema::table('user_devices', function (Blueprint $table) {
                $table->index('signature');
            });
        }

        if (Schema::hasTable('user_verifies')) {
            Schema::table('user_verifies', function (Blueprint $table) {
                $table->index('status');
            });
        }

        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->index('status');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        if (Schema::hasTable('payment_requests')) {
            Schema::table('payment_requests', function (Blueprint $table) {
                $table->dropIndex(['status']);
                $table->dropIndex(['type']);
            });
        }

        if (Schema::hasTable('posts')) {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('streams')) {
            Schema::table('streams', function (Blueprint $table) {
                $table->dropIndex(['slug']);
                $table->dropIndex(['is_public']);
                $table->dropIndex(['requires_subscription']);
            });
        }

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->dropIndex(['paypal_plan_id']);
                $table->dropIndex(['ccbill_subscription_id']);
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex(['stripe_transaction_id']);
                $table->dropIndex(['stripe_session_id']);
                $table->dropIndex(['paypal_payer_id']);
                $table->dropIndex(['paypal_transaction_id']);
                $table->dropIndex(['paypal_transaction_token']);
                $table->dropIndex(['coinbase_charge_id']);
                $table->dropIndex(['coinbase_transaction_token']);
                $table->dropIndex(['nowpayments_payment_id']);
                $table->dropIndex(['nowpayments_order_id']);
                $table->dropIndex(['ccbill_payment_token']);
                $table->dropIndex(['ccbill_transaction_id']);
                $table->dropIndex(['ccbill_subscription_id']);
                $table->dropIndex(['status']);
                $table->dropIndex(['type']);
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropIndex(['enable_2fa']);
                $table->dropIndex(['enable_geoblocking']);
            });
        }

        if (Schema::hasTable('user_codes')) {
            Schema::table('user_codes', function (Blueprint $table) {
                $table->dropIndex(['code']);
            });
        }

        if (Schema::hasTable('user_devices')) {
            Schema::table('user_devices', function (Blueprint $table) {
                $table->dropIndex(['signature']);
            });
        }

        if (Schema::hasTable('user_verifies')) {
            Schema::table('user_verifies', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }

        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->dropIndex(['status']);
            });
        }



    }
}
