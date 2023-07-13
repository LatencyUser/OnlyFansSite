<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V550 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('settings')->insert(
            array(
                array(
                    'key' => 'referrals.referrals_default_link_page',
                    'display_name' => 'Default referrals link page',
                    'value' => 'profile',
                    'details' => '{
"default" : "profile",
"options" : {
"profile": "User profile page",
"register": "Register page",
"home": "Homepage"
},
"description": "The default page for which the referral link will be created for."
}',
                    'type' => 'radio_btn',
                    'order' => 1248,
                    'group' => 'Referrals',
                ),


                array(
                    'key' => 'site.allow_profile_bio_markdown',
                    'display_name' => 'Allow users to use markdown in profile description',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, users will be able to use markdown in their profile bios."
                    }',
                    'type' => 'checkbox',
                    'order' => 80,
                    'group' => 'Site',
                ),

                array(
                    'key' => 'site.allow_profile_bio_markdown_links',
                    'display_name' => 'Allow hyperlinks in the markdown formatting',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, users will be able to post links within their descriptions."
                    }',
                    'type' => 'checkbox',
                    'order' => 81,
                    'group' => 'Site',
                ),

                array(
                    'key' => 'site.disable_profile_bio_excerpt',
                    'display_name' => 'Disable profile\'s bio field excerpt',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If set to On, the bio will be auto-expanded and show more/less labels will be hidden."
                    }',
                    'type' => 'checkbox',
                    'order' => 82,
                    'group' => 'Site',
                ),

                array(
                    'key' => 'site.max_profile_bio_length',
                    'display_name' => 'Maximum profile bio characters length',
                    'value' => '1500',
                    'details' => '{
                        "description": "Max profile bio length. If set to 0, no limit will be set."
                    }',
                    'type' => 'text',
                    'order' => 83,
                    'group' => 'Site',
                ),

                array(
                    'key' => 'websockets.driver',
                    'display_name' => 'Websockets driver',
                    'value' => 'pusher',
                    'details' => '{
"default" : "pusher",
"options" : {
"pusher": "Pusher",
"soketi": "Soketi"
}
}',
                    'type' => 'select_dropdown',
                    'order' => 1,
                    'group' => 'Websockets',
                ),

            )
        );

        DB::table('settings')->insert(
            array(

                array(
                    'key' => 'websockets.soketi_host_address',
                    'display_name' => 'Soketi Host Address',
                    'value' => '',
                    'type' => 'text',
                    'order' => 10,
                    'group' => 'Websockets',
                ),

                array(
                    'key' => 'websockets.soketi_host_port',
                    'display_name' => 'Soketi Host Port',
                    'value' => '',
                    'type' => 'text',
                    'order' => 20,
                    'group' => 'Websockets',
                ),

                array(
                    'key' => 'websockets.soketi_app_id',
                    'display_name' => 'Soketi App ID',
                    'value' => '',
                    'type' => 'text',
                    'order' => 30,
                    'group' => 'Websockets',
                ),

                array(
                    'key' => 'websockets.soketi_app_key',
                    'display_name' => 'Soketi App Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 40,
                    'group' => 'Websockets',
                ),

                array(
                    'key' => 'websockets.soketi_app_secret',
                    'display_name' => 'Soketi App Secret',
                    'value' => '',
                    'type' => 'text',
                    'order' => 50,
                    'group' => 'Websockets',
                ),
            ));

        DB::table('settings')->insert(
            array(
                array(
                    'key' => 'websockets.soketi_use_TSL',
                    'display_name' => 'Use TSL for Soketi',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                    }',
                    'type' => 'checkbox',
                    'order' => 60,
                    'group' => 'Websockets',
                ),
            ));


        // Settings updates
        DB::table('settings')
            ->where('key', 'media.ffmpeg_audio_encoder')
            ->update(['details' => '{
"default" : "aac",
"options" : {
"aac": "AAC Encoder",
"libfdk_aac": "libfdk_aac Encoder",
"libmp3lame": "LAME MP3 Encoder"
},
"description": "AAC is recommended"
}'
            ]);

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
                'referrals.referrals_default_link_page',
                'site.allow_profile_bio_markdown',
                'site.disable_profile_bio_excerpt',
                'site.max_profile_bio_length',
                'site.allow_profile_bio_markdown_links',
                'websockets.driver',
                'websockets.soketi_host_address',
                'websockets.soketi_host_port',
                'websockets.soketi_app_id',
                'websockets.soketi_app_key',
                'websockets.soketi_app_secret',
                'websockets.soketi_use_TSL'
            ])
            ->delete();
    }
}
