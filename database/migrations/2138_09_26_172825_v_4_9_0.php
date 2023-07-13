<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V490 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('migrations')
            ->whereIn('migration', [
                '2138_09_26_172825_withdrawals_fee'
            ])
            ->delete();

        //TODO + Add migration file deletion?

        $sc = DB::table('settings')
            ->whereIn('key', [
                'payments.withdrawal_default_fee_percentage',
                'payments.withdrawal_allow_fees',
            ])
            ->count();

        if (Schema::hasTable('settings') && !($sc == 2) ) {
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'payments.withdrawal_default_fee_percentage',
                        'display_name' => 'Withdrawal fee percentage',
                        'value' => 0,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 96,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.withdrawal_allow_fees',
                        'display_name' => 'Enable withdrawal fee',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Will enable admins to add a default fee percentage which will automatically apply to each withdrawal request."
                        }',
                        'type' => 'checkbox',
                        'order' => 94,
                        'group' => 'Payments',
                    )
                )
            );

            if (Schema::hasTable('withdrawals') &&  !Schema::hasColumn('users', 'phone')) {
                Schema::table('withdrawals', function (Blueprint $table) {
                    $table->float('fee')->after('message')->default(0)->nullable();
                });
            }
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
                'payments.withdrawal_default_fee_percentage',
                'payments.withdrawal_allow_fees',
            ])
            ->delete();

        Schema::table('withdrawals', function (Blueprint $table) {
            $table->dropColumn('fee');
        });
    }
}
