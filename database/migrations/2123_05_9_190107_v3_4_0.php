<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V340 extends Migration
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
                    array (
                        'key' => 'payments.default_subscription_price',
                        'display_name' => 'Default subscription price',
                        'value' => '5',
                        'type' => 'text',
                        'order' => 75,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.min_tip_value',
                        'display_name' => 'Min tips value',
                        'value' => '1',
                        'type' => 'text',
                        'order' => 85,
                        'group' => 'Payments'
                    ),
                    array (
                        'key' => 'payments.max_tip_value',
                        'display_name' => 'Max tips value',
                        'value' => '500',
                        'type' => 'text',
                        'order' => 95,
                        'group' => 'Payments'
                    ),
                )
            );

            DB::table('settings')->insert(
            array (
                'key' => 'media.ffmpeg_video_conversion_quality_preset',
                'display_name' => 'FFMpeg video conversion quality preset',
                'details' => '{
"description" : "Going for better quality will reduce the processing time but increase the file size, next to it\'s original size.",
"default" : "size",
"options" : {
"size": "Size optimized",
"balanced": "Balanced profile",
"quality": "Quality optimized"
}
}',
                'value' => 'balanced',
                'type' => 'radio_btn',
                'order' => 13,
                'group' => 'Media'
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
            ->where('key', 'payments.default_subscription_price')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.min_tip_value')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.max_tip_value')
            ->delete();

        DB::table('settings')
            ->where('key', 'media.ffmpeg_video_conversion_quality_preset')
            ->delete();

    }
}
