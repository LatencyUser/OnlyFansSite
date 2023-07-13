<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V200 extends Migration
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
                    'key' => 'site.enforce_email_validation',
                    'display_name' => 'Enforce email validations',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        "description": "If enabled, all users will be disabled site access until they verified the email. If turned of, users will still receive a confirmation pop up in user settings."
                    }',
                    'type' => 'checkbox',
                    'order' => 77,
                    'group' => 'Site',
                )
            ));

            DB::table('settings')
                ->where('id', 153)
                ->update(['group' => 'Custom CSS & Ads']);

            DB::table('settings')
                ->where('id', 145)
                ->update(['group' => 'Custom CSS & Ads','order'=>154]);

            DB::table('settings')
                ->where('id', 144)
                ->update(['group' => 'Custom CSS & Ads','order'=>155]);

        }

        if (Schema::hasTable('notifications')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->boolean('read')->default(0)->after('user_message_id');
            });

            \App\Model\Notification::query()->update(['read' => true]);
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
