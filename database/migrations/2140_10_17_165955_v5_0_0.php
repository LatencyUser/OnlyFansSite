<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class V500 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('user_messages')) {
            Schema::table('user_messages', function (Blueprint $table) {
                DB::statement("ALTER TABLE `user_messages` CHANGE COLUMN `message` `message` LONGTEXT NULL COLLATE 'utf8mb4_unicode_ci' AFTER `replyTo`;");
            });
        }

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'payments.withdrawal_allow_only_for_verified',
                    'display_name' => 'Enable only for verified creators',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Will enable withdrawal section into creators wallet only if they verified their identity."
                        }',
                    'type' => 'checkbox',
                    'order' => 98,
                    'group' => 'Payments',
                )
            )
        );
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
                'payments.withdrawal_allow_only_for_verified'
            ])
            ->delete();
    }
}
