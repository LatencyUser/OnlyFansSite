<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V450 extends Migration
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
                ->where('key', 'site.default_profile_type_on_register')
                ->update(['details' => '{
                            "default" : "paid",
                            "options" : {
                                "paid": "Paid profile",
                                "free": "Free profile",
                                "open": "Open profile"
                            }}'
            ]);

            DB::table('settings')->insert(array(
                0 => array(
                    'key' => 'site.allow_users_enabling_open_profiles',
                    'display_name' => 'Allow users making their profiles open',
                    'value' => '0',
                    'details' => '{
                        "true" : "Off",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, users will be able to make their profiles open so anyone can see them."
                    }',
                    'type' => 'checkbox',
                    'order' => 130,
                    'group' => 'Site',

                )
            ));
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('open_profile')->nullable()->default(0)->after('enable_geoblocking');
                $table->index('open_profile');
            });
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
                'site.allow_users_enabling_open_profiles',
            ])
            ->delete();

        DB::table('settings')
            ->where('key', 'site.default_profile_type_on_register')
            ->update(['details' => '{
                            "default" : "paid",
                            "options" : {
                                "paid": "Paid profile",
                                "free": "Free profile",
                            }}'
            ]);

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('open_profile');
            });
        }
    }
}
