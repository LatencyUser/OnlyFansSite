<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;

class V162 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        DB::table('settings')->insert(array(
            array (
                'key' => 'site.allow_profile_qr_code',
                'display_name' => 'Allow QR code generate on profiles',
                'value' => NULL,
                'details' => '{
"on" : "On",
"off" : "Off",
"checked" : false,
"description" : "If enabled, a button that allows generating and saving QR codes is shown."
}',
                'type' => 'checkbox',
                'order' => 79,
                'group' => 'Site',
            ),
        ));

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
