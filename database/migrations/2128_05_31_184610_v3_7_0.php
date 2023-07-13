<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V370 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_devices', function (Blueprint $table) {
            $table->text('agent')->nullable()->change();
        });

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'payments.maximum_subscription_price',
                    'display_name' => 'Maximum subscription price',
                    'value' => '500',
                    'type' => 'text',
                    'order' => 77,
                    'group' => 'Payments'
                ),
                array (
                    'key' => 'payments.minimum_subscription_price',
                    'display_name' => 'Minimum subscription price',
                    'value' => '1',
                    'type' => 'text',
                    'order' => 76,
                    'group' => 'Payments'
                )
            )
        );


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'media.ffmpeg_audio_encoder',
                    'display_name' => 'FFMPEG Audio encoder',
                    'value' => 'aac',
                    'details' => '{
"default" : "aac",
"options" : {
"aac": "AAC Encoder",
"libmp3lame": "LAME MP3 Encoder"
},
"description": "AAC is recommended"
}',
                    'type' => 'select_dropdown',
                    'order' => 13,
                    'group' => 'Media'
                ),
            )
        );

        DB::table('settings')
            ->where('key', 'payments.max_tip_value')
            ->update(['order' => 86
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
            ->where('key', 'media.ffmpeg_audio_encoder')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.maximum_subscription_price')
            ->delete();

        DB::table('settings')
            ->where('key', 'payments.minimum_subscription_price')
            ->delete();
    }
}
