<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V330 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('settings')) {

            DB::table('settings')
                ->where('id', 119)
                ->update(['details' => '{
"on" : "On",
"off" : "Off",
"checked" : true,
"description" : "If enabled, users will only be able to post content & start streams if ID is verified."
}']);

            DB::table('settings')
                ->where('key', 'site.google_analytics_tracking_id')
                ->delete();

        }
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
