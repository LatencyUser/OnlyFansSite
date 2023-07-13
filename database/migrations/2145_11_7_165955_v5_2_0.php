<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V520 extends Migration
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
                    'key' => 'storage.minio_access_key',
                    'display_name' => 'Minio Access Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 130,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.minio_secret_key',
                    'display_name' => 'Minio Secret Key',
                    'value' => '',
                    'type' => 'text',
                    'order' => 140,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.minio_region',
                    'display_name' => 'Minio Region',
                    'value' => '',
                    'type' => 'text',
                    'order' => 150,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.minio_bucket_name',
                    'display_name' => 'Minio Bucket',
                    'value' => '',
                    'type' => 'text',
                    'order' => 160,
                    'group' => 'Storage',
                )));

            \DB::table('settings')->insert(array(
                array(
                    'key' => 'storage.minio_endpoint',
                    'display_name' => 'Minio Endpoint',
                    'value' => '',
                    'type' => 'text',
                    'order' => 170,
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
"do_spaces": "DigitalOcean Spaces",
"minio": "Minio"
}
}'
                ]);

        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\DataTypesTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\DataRowsTableSeeder']);
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Artisan::call('optimize:clear');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {



        // Delete old
        DB::table('settings')
            ->where('key', 'storage.minio_access_key')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.minio_secret_key')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.minio_region')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.minio_endpoint')
            ->delete();

        // Delete old
        DB::table('settings')
            ->where('key', 'storage.minio_bucket_name')
            ->delete();

        // Settings updates
        DB::table('settings')
            ->where('key', 'storage.driver')
            ->update(['details' => '{
"default" : "s3",
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
