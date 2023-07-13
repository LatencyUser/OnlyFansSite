<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V470 extends Migration
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
                        'key' => 'payments.paystack_secret_key',
                        'display_name' => 'Paystack Secret Key',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 28,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.paystack_checkout_disabled',
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
                    )
                )
            );
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('paystack_payment_token')->after('ccbill_subscription_id')->nullable();
                $table->index('paystack_payment_token');
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
        DB::table('settings')
            ->whereIn('key', [
                'payments.paystack_secret_key',
                'payments.paystack_checkout_disabled',
            ])
            ->delete();

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('paystack_payment_token');
        });

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex(['paystack_payment_token']);
            });
        }
    }
}
