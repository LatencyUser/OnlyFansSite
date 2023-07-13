<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V220 extends Migration
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
                    array(
                        'key' => 'site.enable_2fa',
                        'display_name' => 'Enable email 2FA on logins',
                        'value' => '1',
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, users which have 2FA enabled for their account, will be prompted with a security check when logging from new devices."
                        }',
                        'type' => 'checkbox',
                        'order' => 85,
                        'group' => 'Site',
                    ),
                    array(
                        'key' => 'site.default_2fa_on_register',
                        'display_name' => 'Default 2FA setting on user register',
                        'value' => '0',
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, users will have 2FA enabled by default, when creating new accounts."
                        }',
                        'type' => 'checkbox',
                        'order' => 90,
                        'group' => 'Site',
                    ),
                    array(
                        'key' => 'site.allow_users_2fa_switch',
                        'display_name' => 'Allow users to turn off 2FA',
                        'value' => '1',
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If disabled, users won\'t be able to change their 2FA settings."
                        }',
                        'type' => 'checkbox',
                        'order' => 95,
                        'group' => 'Site',
                    ),
                    array(
                        'key' => 'site.default_profile_type_on_register',
                        'display_name' => 'Default profile type on user register',
                        'value' => 'paid',
                        'details' => '{
"default" : "paid",
"options" : {
"paid": "Paid profile",
"free": "Free profile"
}
}',
                        'type' => 'radio_btn',
                        'order' => 100,
                        'group' => 'Site',
                    ),
                    array(
                        'key' => 'feed.default_users_to_follow',
                        'display_name' => 'Default users to follow on user register',
                        'value' => '',
                        'details' => '{
                        "description": "List of user-IDs to be followed by all users when registering, separated by a comma. If users are free, their content will be shown on the feed of new users."
                        }',
                        'type' => 'text',
                        'order' => 120,
                        'group' => 'Feed',
                    ),
                ));
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('enable_2fa')->nullable()->after('auth_provider_id');
            });
        }

        Schema::create('user_codes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('user_devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('address');
            $table->string('agent');
            $table->string('signature');
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_codes');
        Schema::dropIfExists('user_devices');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('enable_2fa');
        });

        // Delete old
        DB::table('settings')
            ->where('key', 'site.default_2fa_on_register')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'site.allow_users_2fa_switch')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'site.enable_2fa')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'site.default_profile_type_on_register')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'feed.default_users_to_follow')
            ->delete();

    }
}
