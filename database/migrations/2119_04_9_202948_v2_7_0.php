<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V270 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        // Fixing entries for users that have already modified & broke the keys for these settings
        DB::table('settings')
            ->where('key', 'custom-css-ads.site.custom_css')
            ->update([
                'key' => 'custom-code-ads.custom_css',
                'group' => 'Custom Code / Ads'
            ]);

        DB::table('settings')
            ->where('key', 'custom-css-ads.site.custom_js')
            ->update([
                'key' => 'custom-code-ads.custom_js',
                'group' => 'Custom Code / Ads'
            ]);

        DB::table('settings')
            ->where('key', 'custom-css-ads.ad-spaces.sidebar_ad_spot')
            ->update([
                'key' => 'custom-code-ads.sidebar_ad_spot',
                'group' => 'Custom Code / Ads'
            ]);


        // Fixing entries for users that kept these settings un-touched
        DB::table('settings')
            ->where('key', 'site.custom_css')
            ->update([
                'key' => 'custom-code-ads.custom_css',
                'group' => 'Custom Code / Ads'
            ]);

        DB::table('settings')
            ->where('key', 'site.custom_js')
            ->update([
                'key' => 'custom-code-ads.custom_js',
                'group' => 'Custom Code / Ads'
            ]);

        DB::table('settings')
            ->where('key', 'ad-spaces.sidebar_ad_spot')
            ->update([
                'key' => 'custom-code-ads.sidebar_ad_spot',
                'group' => 'Custom Code / Ads'
            ]);

        // Websockets renaming
        DB::table('settings')
            ->where('group', 'Messenger & Notifications')
            ->update([
                'group' => 'Websockets'
            ]);


        DB::table('settings')
            ->where('key', 'messenger-notifications.pusher_app_id')
            ->update([
                'key' => 'websockets.pusher_app_id',
                'group' => 'Websockets'
            ]);

        DB::table('settings')
            ->where('key', 'messenger-notifications.pusher_app_cluster')
            ->update([
                'key' => 'websockets.pusher_app_cluster',
                'group' => 'Websockets'
            ]);

        DB::table('settings')
            ->where('key', 'messenger-notifications.pusher_app_secret')
            ->update([
                'key' => 'websockets.pusher_app_secret',
                'group' => 'Websockets'
            ]);

        DB::table('settings')
            ->where('key', 'messenger-notifications.pusher_app_key')
            ->update([
                'key' => 'websockets.pusher_app_key',
                'group' => 'Websockets'
            ]);


        // New media settings + updates
        DB::table('settings')
            ->where('key', 'media.users_covers_size')
            ->update([
                'display_name' => 'User cover images (re)size',
                'details' => '{
                        "description": "Size to which the covers will be resized to. Increasing the resolution will give higher quality cover images, but bigger files. Make sure to use the same aspect ratio."
                    }'
            ]);
        DB::table('settings')->insert(
            array(
                array(
                    'key' => 'media.users_avatars_size',
                    'display_name' => 'User avatar images (re)size',
                    'value' => '96x96',
                    'details' => '{
                        "description": "Size to which the avatars will be resized to. Increasing the resolution will give higher quality cover images, but bigger files. Make sure to use the same aspect ratio."
                    }',
                    'type' => 'text',
                    'order' => 1130,
                    'group' => 'Media',
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

    }
}
