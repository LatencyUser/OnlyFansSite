<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V350 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where('group', 'Withdrawals & Deposit')
                ->update(['group' => 'Payments']);
            
            DB::raw("UPDATE settings SET `key` = REPLACE(`key`, 'withdrawals-deposit.','payments.') WHERE `key` LIKE '%withdrawals-deposit%'");

            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'payments.offline_payments_owner',
                        'display_name' => 'Account owner',
                        'value' => '',
                        'type' => 'text',
                        'order' => 44,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.offline_payments_account_number',
                        'display_name' => 'Account number',
                        'value' => '',
                        'type' => 'text',
                        'order' => 45,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.offline_payments_bank_name',
                        'display_name' => 'Bank name',
                        'value' => '',
                        'type' => 'text',
                        'order' => 46,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.offline_payments_routing_number',
                        'display_name' => 'Routing number',
                        'value' => '',
                        'type' => 'text',
                        'order' => 47,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.offline_payments_iban',
                        'display_name' => 'IBAN',
                        'value' => '',
                        'type' => 'text',
                        'order' => 48,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.offline_payments_swift',
                        'display_name' => 'BIC / SWIFT',
                        'value' => '',
                        'type' => 'text',
                        'order' => 49,
                        'group' => 'Payments'
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
            ->where('key', 'payments.offline_payments_owner')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.offline_payments_account_number')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.offline_payments_bank_name')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.offline_payments_routing_number')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.offline_payments_iban')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.offline_payments_swift')
            ->delete();

    }
}
