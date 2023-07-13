<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V380 extends Migration
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
                array (
                    'key' => 'recaptcha.enabled',
                    'display_name' => 'Enable Google reCAPTCHA',
                    'value' => NULL,
                    'details' => '{
"on" : "On",
"off" : "Off",
"checked" : false,
"description": "If enabled, it will be used on all public form pages."
}',
                    'type' => 'checkbox',
                    'order' => 1200,
                    'group' => 'ReCaptcha',
                ),
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'recaptcha.site_key',
                    'display_name' => 'reCAPTCHA Site Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 1210,
                    'group' => 'ReCaptcha'
                ),
                array (
                    'key' => 'recaptcha.site_secret_key',
                    'display_name' => 'reCAPTCHA Secret Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 1220,
                    'group' => 'ReCaptcha'
                )
            )
        );


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        DB::table('settings')
            ->where('key', 'recaptcha.enabled')
            ->delete();

        DB::table('settings')
            ->where('key', 'recaptcha.site_key')
            ->delete();

        DB::table('settings')
            ->where('key', 'recaptcha.site_secret_key')
            ->delete();

    }
}
