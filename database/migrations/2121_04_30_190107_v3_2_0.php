<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V320 extends Migration
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
                        'key' => 'colors.theme_color_code',
                        'display_name' => 'Theme color code',
                        'value' => '',
                        'type' => 'text',
                        'order' => 210,
                        'group' => 'Colors'
                    ),
                    array (
                        'key' => 'colors.theme_gradient_from',
                        'display_name' => 'Theme gradient from',
                        'value' => '',
                        'type' => 'text',
                        'order' => 220,
                        'group' => 'Colors',
                    ),
                    array (
                        'key' => 'colors.theme_gradient_to',
                        'display_name' => 'Theme gradient to',
                        'value' => '',
                        'type' => 'text',
                        'order' => 230,
                        'group' => 'Colors',
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('streams');
        Schema::dropIfExists('stream_messages');
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign('transactions_stream_id_foreign');
                $table->dropColumn('stream_id');
            });
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        DB::table('settings')
            ->whereIn('key', [
                'streams.allow_1080p',
                'streams.allow_720p',
                'streams.allow_576p',
                'streams.allow_360p',
                'streams.allow_480p',
                'streams.allow_mux',
                'streams.allow_dvr',
                'streams.pushr_encoder',
                'streams.pushr_zone_id',
                'streams.pushr_key',
                'streams.max_live_duration'
            ])
            ->delete();
    }
}
