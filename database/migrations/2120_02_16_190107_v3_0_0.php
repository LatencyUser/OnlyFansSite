<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V300 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('streams', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('status');
            $table->index('status');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->string('poster')->nullable();
            $table->float('price')->default(0);
            $table->boolean('requires_subscription')->nullable()->default(0);
            $table->boolean('sent_expiring_reminder')->nullable()->default(0);
            $table->boolean('is_public')->nullable()->default(1);
            $table->unsignedBigInteger('pushr_id')->unique();
            $table->string('rtmp_key')->nullable();
            $table->string('rtmp_server')->nullable();
            $table->string('hls_link')->nullable();
            $table->string('vod_link')->nullable();
            $table->text('settings')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->timestamps();
        });

        Schema::create('stream_messages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('stream_id');
            $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
            $table->longtext('message');
            $table->timestamps();
        });

        // Adds stream_id on transactions table
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('stream_id')->nullable()->after('post_id');
                $table->foreign('stream_id')->references('id')->on('streams')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'streams.allow_streams',
                        'display_name' => 'Allow streams',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        }',
                        'type' => 'checkbox',
                        'order' => 163,
                        'group' => 'Streams',
                    ),
                )
            );
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'streams.max_live_duration',
                        'display_name' => 'Max Live Stream Duration',
                        'value' => '2',
                        'type' => 'text',
                        'order' => 165,
                        'group' => 'Streams',
                        'details' => '{
                            "description": "Maximum time duration for a live stream set in hours"
                        }',
                    ),
                )
            );
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'streams.pushr_key',
                        'display_name' => 'Pushr Key',
                        'value' => '',
                        'type' => 'text',
                        'order' => 175,
                        'group' => 'Streams'
                    ),
                    array (
                        'key' => 'streams.pushr_zone_id',
                        'display_name' => 'Pushr Zone Id',
                        'value' => '',
                        'type' => 'text',
                        'order' => 185,
                        'group' => 'Streams',
                    )
                )
            );
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'streams.allow_dvr',
                        'display_name' => 'Allow VOD',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "Enabling VODs allow creators to watch their previous live streams (no extra fees when using Pushr provider)"
                        }',
                        'type' => 'checkbox',
                        'order' => 205,
                        'group' => 'Streams',
                    ),
                )
            );
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'streams.pushr_encoder',
                        'display_name' => 'Pushr Encoder',
                        'details' => '{
                            "description": "Pushr stream encoder. EG: `eu`"
                        }',
                        'value' => '',
                        'type' => 'text',
                        'order' => 195,
                        'group' => 'Streams',
                    ),
                )
            );

            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'streams.allow_mux',
                        'display_name' => 'Allow MUX',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                        'type' => 'checkbox',
                        'order' => 215,
                        'group' => 'Streams',
                    ),
                    array (
                        'key' => 'streams.allow_480p',
                        'display_name' => 'Allow 480p',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                        'type' => 'checkbox',
                        'order' => 235,
                        'group' => 'Streams',
                    ),
                    array (
                        'key' => 'streams.allow_360p',
                        'display_name' => 'Allow 360p',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                        'type' => 'checkbox',
                        'order' => 225,
                        'group' => 'Streams',
                    ),
                    array (
                        'key' => 'streams.allow_576p',
                        'display_name' => 'Allow 576p',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                        'type' => 'checkbox',
                        'order' => 245,
                        'group' => 'Streams',
                    ),
                    array (
                        'key' => 'streams.allow_720p',
                        'display_name' => 'Allow 720p',
                        'value' => 0,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                        'type' => 'checkbox',
                        'order' => 255,
                        'group' => 'Streams',
                    ),
                    array (
                        'key' => 'streams.allow_1080p',
                        'display_name' => 'Allow 1080p',
                        'value' => 1,
                        'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false
                        }',
                        'type' => 'checkbox',
                        'order' => 265,
                        'group' => 'Streams',
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
                'streams.max_live_duration',
                'streams.allow_streams'
            ])
            ->delete();
    }
}
