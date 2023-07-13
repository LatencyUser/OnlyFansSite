<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V230 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('settings')) {


            // New settings
            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.do_access_key',
                    'display_name' => 'DO Access Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 120,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.do_secret_key',
                    'display_name' => 'DO Secret Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 121,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.do_region',
                    'display_name' => 'DO Region',
                    'value' => '',
                    'type' => 'text',
                    'order' => 123,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.do_endpoint',
                    'display_name' => 'DO Endpoint',
                    'value' => '',
                    'type' => 'text',
                    'order' => 124,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.do_bucket_name',
                    'display_name' => 'DO Bucket',
                    'value' => '',
                    'type' => 'text',
                    'order' => 122,
                    'group' => 'Storage',
                )));

            // Settings updates
            DB::table('settings')
                ->where('key', 'storage.driver')
                ->update(['details' => '{
"default" : "public",
"options" : {
"public": "Local",
"s3": "S3",
"wasabi": "Wasabi",
"do_spaces": "DigitalOcean Spaces"
}
}'
                ]);

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('enable_2fa');
        });

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.do_access_key')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.do_secret_key')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.do_region')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.do_endpoint')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.do_bucket_name')
            ->delete();

        // Settings updates
        DB::table('settings')
            ->where('key', 'storage.driver')
            ->update(['details' => '{
"default" : "s3",
"options" : {
"public": "Local",
"s3": "S3",
"wasabi": "Wasabi"
}
}'
            ]);

    }
}
