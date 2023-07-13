<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V440 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'payments.ccbill_datalink_username',
                        'display_name' => 'CCBill DataLink Username',
                        'value' => NULL,
                        'details' => '{
                            "description": "Used for cancelling CCBill subscriptions programmatically. Enables users cancelling their CCBill subscriptions from their profile"
                        }',
                        'type' => 'text',
                        'order' => 33,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.ccbill_datalink_password',
                        'display_name' => 'CCBill DataLink Password',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 34,
                        'group' => 'Payments',
                    )
                )
            );
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign('transactions_invoice_id_foreign');
                $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'payments.ccbill_checkout_disabled',
                        'display_name' => 'Disable for checkout',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be shown on checkout, but it`s still available for deposits."
                        }',
                        'type' => 'checkbox',
                        'order' => 36,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.ccbill_recurring_disabled',
                        'display_name' => 'Disable for recurring payments',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be available for subscription payments, but it`s still available for deposits and one time payments."
                        }',
                        'type' => 'checkbox',
                        'order' => 36,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.stripe_checkout_disabled',
                        'display_name' => 'Disable for checkout',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be shown on checkout, but it`s still available for deposits."
                        }',
                        'type' => 'checkbox',
                        'order' => 40,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.stripe_recurring_disabled',
                        'display_name' => 'Disable for recurring payments',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be available for subscription payments, but it`s still available for deposits and one time payments."
                        }',
                        'type' => 'checkbox',
                        'order' => 42,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.paypal_checkout_disabled',
                        'display_name' => 'Disable for checkout',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be shown on checkout, but it`s still available for deposits."
                        }',
                        'type' => 'checkbox',
                        'order' => 44,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.paypal_recurring_disabled',
                        'display_name' => 'Disable for recurring payments',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be available for subscription payments, but it`s still available for deposits and one time payments."
                        }',
                        'type' => 'checkbox',
                        'order' => 46,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.nowpayments_checkout_disabled',
                        'display_name' => 'Disable for checkout',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be shown on checkout, but it`s still available for deposits."
                        }',
                        'type' => 'checkbox',
                        'order' => 36,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.coinbase_checkout_disabled',
                        'display_name' => 'Disable for checkout',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Won`t be shown on checkout, but it`s still available for deposits."
                        }',
                        'type' => 'checkbox',
                        'order' => 38,
                        'group' => 'Payments',
                    ),
                )
            );
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')
            ->whereIn('key', [
                'payments.ccbill_datalink_username',
                'payments.ccbill_datalink_password',
                'payments.ccbill_checkout_disabled',
                'payments.ccbill_recurring_disabled',
                'payments.stripe_checkout_disabled',
                'payments.stripe_recurring_disabled',
                'payments.paypal_checkout_disabled',
                'payments.paypal_recurring_disabled',
                'payments.nowpayments_checkout_disabled',
                'payments.coinbase_checkout_disabled'
            ])
            ->delete();
    }
}
