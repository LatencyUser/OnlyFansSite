<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V240 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add now payments related column for transactions table
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('nowpayments_payment_id')->after('coinbase_transaction_token')->nullable();
                $table->string('nowpayments_order_id')->after('nowpayments_payment_id')->nullable();
            });
        }

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'payments.nowpayments_api_key',
                    'display_name' => 'NowPayments Api Key',
                    'value' => NULL,
                    'details' => NULL,
                    'type' => 'text',
                    'order' => 33,
                    'group' => 'Payments',
                ),
                array (
                    'key' => 'payments.nowpayments_ipn_secret_key',
                    'display_name' => 'NowPayments IPN Secret Key',
                    'value' => NULL,
                    'details' => NULL,
                    'type' => 'text',
                    'order' => 34,
                    'group' => 'Payments',
                )
            ));
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
