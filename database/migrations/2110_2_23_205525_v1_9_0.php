<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V190 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(array(
                array(
                    'key' => 'media.use_chunked_uploads',
                    'display_name' => 'Use chunked uploads',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, file uploads will be split across multiple requests, allowing to bypass Cloudflare or max file limits."
                    }',
                    'type' => 'checkbox',
                    'order' => 15,
                    'group' => 'Media',
                ),
                array(
                    'key' => 'media.upload_chunk_size',
                    'display_name' => 'Chunks size',
                    'value' => '2',
                    'details' => '{
                        "description": "File upload chunks size in MB. Can not exceed maximum server upload size."
                    }',
                    'type' => 'text',
                    'order' => 15,
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
