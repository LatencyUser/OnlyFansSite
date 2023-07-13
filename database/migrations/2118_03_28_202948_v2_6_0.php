<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V260 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('withdrawals')) {
            Schema::table('withdrawals', function (Blueprint $table) {
                $table->string('payment_method')->nullable();
                $table->string('payment_identifier')->nullable();
            });
        }
        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'withdrawals-deposit.withdrawal_payment_methods',
                        'display_name' => 'Withdrawal allowed payment methods',
                        'value' => 'Bank transfer, Other',
                        'details' => '{
                            "description": "Comma separated values (Bank transfer, Stripe, PayPal, Crypto, Other)"
                        }',
                        'type' => 'text',
                        'order' => 90,
                        'group' => 'Withdrawals & Deposit',
                    )
                )
            );
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'site.enforce_app_ssl',
                        'display_name' => 'Enforce platform SSL usage',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Usually not required, rarely, some hosting providers needs it."
                        }',
                        'type' => 'checkbox',
                        'order' => 130,
                        'group' => 'Site',
                    )
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
            ->where('key', 'withdrawals-deposit.withdrawal_payment_methods')
            ->delete();

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('payment_method');
            $table->dropColumn('payment_identifier');
        });

        DB::table('settings')
            ->where('key', 'site.enforce_app_ssl')
            ->delete();
    }
}
