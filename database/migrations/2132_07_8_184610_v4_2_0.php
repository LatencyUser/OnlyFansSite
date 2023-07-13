<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V420 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('settings')
            ->where('key', 'websockets.pusher_app_secret')
            ->update(['order' => '30',]);

        DB::table('settings')
            ->where('key', 'websockets.pusher_app_key')
            ->update(['order' => '20']);

        DB::table('settings')
            ->where('key', 'websockets.pusher_app_id')
            ->update(['order' => '10']);

        DB::table('settings')
            ->where('key', 'websockets.pusher_app_cluster')
            ->update(['order' => '40']);

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
