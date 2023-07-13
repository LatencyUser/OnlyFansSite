<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V560 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function($table)
        {
            $table->float('profile_access_price_6_months')->default(5)->nullable()->change();
            $table->float('profile_access_price_12_months')->default(5)->nullable()->change();


            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'compliance.admin_approved_posts_limit',
                        'display_name' => 'Admin pre-approved posts limit',
                        'value' => '0',
                        'type' => 'text',
                        'order' => 1180,
                        'details' => "
                        {
\"description\" : \"The number of posts that needs admin approval. After this number of posts has been reached, the creator can post freely (value = 0 means no limit).\"
}",
                        'group' => 'Compliance'
                    ),
                    array (
                        'key' => 'compliance.minimum_posts_until_creator',
                        'display_name' => 'Required number of posts to be able to receive payments',
                        'value' => 0,
                        'details' => '{
                        "description": "Set a minimum number of posts for users to be able to earn money. Users won`t be able to receive money until they reach this limit (value = 0 means no limit)."
                        }',
                        'type' => 'text',
                        'order' => 1170,
                        'group' => 'Compliance',
                    ),
                    array (
                        'key' => 'compliance.minimum_posts_deletion_limit',
                        'display_name' => "Deletion minimum posts limit",
                        'value' => 0,
                        'details' => '{
                        "description": "Set a minimum posts deletion limit for creators. Enforce them to have a minimum number of posts on their accounts (value = 0 means no limit)."
                        }',
                        'type' => 'text',
                        'order' => 1190,
                        'group' => 'Compliance',
                    )
                )
            );

        });


        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'compliance.monthly_posts_before_inactive',
                    'display_name' => 'Monthly posts required to keep account active',
                    'value' => '0',
                    'type' => 'text',
                    'order' => 1170,
                    'details' => "
                        {
\"description\" : \"The minimum monthly posts number a creator must publish before having his account marked as inactive. If value = 0, no inactivity rule will be applied.\"
}",
                    'group' => 'Compliance'
                ),
            )
        );

        DB::table('settings')->insert(
            array(
                array(
                    'key' => 'compliance.disable_creators_ppv_delete',
                    'display_name' => 'Disable creators ability to delete purchased PPV content',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, creators won\'t be able to delete paid PPV content (paid posts/messages) if already paid by a customer."
                    }',
                    'type' => 'checkbox',
                    'order' => 1190,
                    'group' => 'Compliance',
                )
            )
        );


//        TODO: DB:SAVE AND RE-SEED the breads
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\DataTypesTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\DataRowsTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\MenusTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\MenuItemsTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\RolesTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\PermissionsTableSeeder']);
        Artisan::call('db:seed',['--force'=>true,'--class'=>'Database\Seeders\PermissionRoleTableSeeder']);
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
        DB::table('settings')
            ->whereIn('key', [
                'compliance.monthly_posts_before_inactive',
                'compliance.admin_approved_posts_limit',
                'compliance.minimum_posts_until_creator',
                'compliance.minimum_posts_deletion_limit',
                'compliance.disable_creators_ppv_delete',
            ])
            ->delete();
    }
}
