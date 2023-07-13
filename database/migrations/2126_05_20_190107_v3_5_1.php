<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V351 extends Migration
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
                ->where('key', 'withdrawals-deposit.withdrawal_payment_methods')
                ->update(['key' => 'payments.withdrawal_payment_methods']);

            DB::table('settings')
                ->where('key', 'withdrawals-deposit.withdrawal_min_amount')
                ->update(['key' => 'payments.withdrawal_min_amount']);

            DB::table('settings')
                ->where('key', 'withdrawals-deposit.withdrawal_max_amount')
                ->update(['key' => 'payments.withdrawal_max_amount']);

            DB::table('settings')
                ->where('key', 'withdrawals-deposit.deposit_min_amount')
                ->update(['key' => 'payments.deposit_min_amount']);

            DB::table('settings')
                ->where('key', 'withdrawals-deposit.deposit_max_amount')
                ->update(['key' => 'payments.deposit_max_amount']);

        }



    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
