<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class V480 extends Migration
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
                    'key' => 'license.product_license_key',
                    'display_name' => 'Product license key',
                    'value' => NULL,
                    'details' => '{
                        "description": "Your product license key. Can be taken out of your Codecanyon downloads page."
                        }',
                    'type' => 'text',
                    'order' => 0,
                    'group' => 'License',
                )
            )
        );

        DB::table('settings')
            ->whereIn('key', [
                'security.trusted_proxies',
            ])
            ->delete();

        $hasInstalledFile = Storage::disk('local')->exists('installed');
        if($hasInstalledFile){
            $licenseData = json_decode(Storage::disk('local')->get('installed'));
            if($licenseData){
                if(isset($licenseData->code)){
                    DB::table('settings')
                        ->where('key', 'license.product_license_key')
                        ->update(['value' => $licenseData->code]);
                }
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')
            ->whereIn('key', [
                'license.product_license_key',
            ])
            ->delete();

        DB::table('settings')->insert(
            array (
                'key' => 'security.trusted_proxies',
                'display_name' => 'Trust all proxies for forwarded traffic ',
                'value' => NULL,
                'details' => '{
"on" : "On",
"off" : "Off",
"checked" : false,
"description": "For servers running behind load balancers, this setting might need to be used if encountering issues with email verifications."
}',
                'type' => 'checkbox',
                'order' => 1230,
                'group' => 'Security',
            )
        );

    }
}
