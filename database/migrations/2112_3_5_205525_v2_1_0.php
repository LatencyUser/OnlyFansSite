<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V210 extends Migration
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
                    array(
                        'key' => 'site.homepage_redirect',
                        'display_name' => 'Homepage redirect',
                        'value' => '',
                        'details' => '{
                        "description": "If this setting is used, the `Homepage type` setting will not be used anymore."
                    }',
                        'type' => 'text',
                        'order' => 76,
                        'group' => 'Site',
                    ),
                    array(
                        'key' => 'media.users_covers_size',
                        'display_name' => 'User cover images size',
                        'value' => '599x180',
                        'details' => '{
                        "description": "Increasing the resolution will allow you to host higher quality cover images but increasing sizes. Make sure to use the same aspect ratio."
                    }',
                        'type' => 'text',
                        'order' => 1120,
                        'group' => 'Media',
                    )
                ));

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
