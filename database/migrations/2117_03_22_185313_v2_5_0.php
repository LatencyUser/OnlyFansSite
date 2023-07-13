<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V250 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Add now payments related column for transactions table
        if (Schema::hasTable('creator_offers')) {
            Schema::table('creator_offers', function (Blueprint $table) {
                DB::statement("ALTER TABLE `creator_offers` CHANGE COLUMN `expires_at` `expires_at` DATETIME NULL AFTER `old_profile_access_price_12_months`;");
            });
        }
        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                array(
                    array(
                        'key' => 'site.default_user_privacy_setting_on_register',
                        'display_name' => 'Default user privacy setting on user register',
                        'value' => 'public',
                        'details' => '{
"default" : "public",
"options" : {
"public": "Public profile",
"private": "Private profile"
}
}',
                        'type' => 'radio_btn',
                        'order' => 120,
                        'group' => 'Site',
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
            ->where('key', 'site.default_user_privacy_setting_on_register')
            ->delete();
    }
}
