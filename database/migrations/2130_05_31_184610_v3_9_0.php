<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V390 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

       // Rename recaptcha > security
        DB::table('settings')
            ->where('key', 'recaptcha.enabled')
            ->update(['group' => 'Security','key' => 'security.recaptcha_enabled']);

        DB::table('settings')
            ->where('key', 'recaptcha.site_key')
            ->update(['group' => 'Security','key' => 'security.recaptcha_site_key']);

        DB::table('settings')
            ->where('key', 'recaptcha.site_secret_key')
            ->update(['group' => 'Security','key' => 'security.recaptcha_site_secret_key']);

        // Move SSL to this cat
        DB::table('settings')
            ->where('key', 'site.enforce_app_ssl')
            ->update(['group' => 'Security', 'key' => 'security.enforce_app_ssl']);

        // New trusted policy setting
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'security.trusted_proxies',
                    'display_name' => 'Trust all proxies for forwarded traffic ',
                    'value' => NULL,
                    'details' => '{
"on" : "On",
"off" : "Off",
"checked" : false,
"description": "For servers running behind load balancers, this setting might need to be used if encountering issues with email verifications."
}',
                    'type' => 'checkbox',
                    'order' => 1230,
                    'group' => 'Security',
                ),
            )
        );

        // New post min description - if empty, at least one attachment will be requierd
        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'feed.min_post_description',
                    'display_name' => 'Minimum post description length',
                    'value' => '10',
                    'details' => '{
"description": "If set to 0/empty, at least one post attachment is required. Otherwise, attachments are optional."
}',
                    'type' => 'text',
                    'order' => 130,
                    'group' => 'Feed',
                ),
            )
        );

        //TODO: Move 2fa To security
        DB::table('settings')
            ->where('key', 'site.enable_2fa')
            ->update(['group' => 'Security','key' => 'security.enable_2fa']);

        DB::table('settings')
            ->where('key', 'site.default_2fa_on_register')
            ->update(['group' => 'Security','key' => 'security.default_2fa_on_register']);

        DB::table('settings')
            ->where('key', 'site.allow_users_2fa_switch')
            ->update(['group' => 'Security', 'key' => 'security.allow_users_2fa_switch']);


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'media.max_avatar_cover_file_size',
                    'display_name' => 'Max avatar&cover file size',
                    'value' => '4',
                    'details' => '{
"description": "File size in MB. Used for both avatar & cover"
}',
                    'type' => 'text',
                    'order' => 1140,
                    'group' => 'Media',
                ),
            )
        );

        Schema::table('posts', function($table)
        {
            $table->longText('text')->nullable()->change();
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')
            ->where('key', 'security.trusted_proxies')
            ->delete();

        DB::table('settings')
            ->where('key', 'feed.min_post_description')
            ->delete();

        DB::table('settings')
            ->where('key', 'media.max_avatar_cover_file_size')
            ->delete();

    }
}
