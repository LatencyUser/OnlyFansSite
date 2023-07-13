<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V531 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code')->after('username')->unique()->nullable();
            });
        }

        // here we need to process previous users which don't have a referral_code yet
        $existingUsers = \App\User::all();
        foreach ($existingUsers as $user) {
            try {
                $user->update(['referral_code' => \App\Providers\AuthServiceProvider::generateReferralCode(8)]);
            } catch (\Exception $exception) {
            }
        }

        Schema::create('referral_code_usages', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('used_by');
            $table->foreign('used_by')->references('id')->on('users')->onDelete('cascade');
            $table->string('referral_code');
            $table->index('used_by');
            $table->index('referral_code');
            $table->timestamps();
        });

        Schema::create('rewards', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('from_user_id')->nullable();
            $table->unsignedBigInteger('to_user_id')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable();
            $table->unsignedBigInteger('referral_code_usage_id')->nullable();
            $table->foreign('from_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('to_user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreign('referral_code_usage_id')->references('id')->on('referral_code_usages')->onDelete('cascade');
            $table->float('amount')->nullable();
            $table->integer('reward_type');
            $table->index('from_user_id');
            $table->index('to_user_id');
            $table->index('reward_type');
            $table->index('transaction_id');
            $table->index('referral_code_usage_id');
            $table->timestamps();
        });

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'referrals.enabled',
                    'display_name' => 'Enable referral system',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        }',
                    'type' => 'checkbox',
                    'order' => 1240,
                    'group' => 'Referrals',
                ),
                array(
                    'key' => 'referrals.fee_percentage',
                    'display_name' => 'Referrals earning fee percentage',
                    'value' => '5',
                    'details' => '{
                        "description": "Payout percentage given to users from their referred people total earnings. If set to 0, referred users will generate no income."
                    }',
                    'type' => 'text',
                    'order' => 1242,
                    'group' => 'Referrals',
                ),
                array(
                    'key' => 'referrals.apply_for_months',
                    'display_name' => 'Referrals months limit',
                    'value' => '12',
                    'details' => '{
                        "description": "Represents the number of months since users created their accounts so people who referred them earn a fee from their total earnings."
                    }',
                    'type' => 'text',
                    'order' => 1244,
                    'group' => 'Referrals',
                ),
                array(
                    'key' => 'referrals.fee_limit',
                    'display_name' => 'Referrals fee limit',
                    'value' => '1000',
                    'details' => '{
                        "description": "Allows users to earn up to the specified limit per referred user."
                    }',
                    'type' => 'text',
                    'order' => 1246,
                    'group' => 'Referrals',
                )
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'site.default_wallet_balance_on_register',
                    'display_name' => 'Default wallet balance on user register',
                    'value' => 0,
                    'details' => '{
                        "description" : "Default wallet balance to be credited to new users."
                    }
                    ',
                    'type' => 'text',
                    'order' => 140,
                    'group' => 'Site',
                ),
            )
        );

        DB::table('settings')->insert(
            array(
                array (
                    'key' => 'feed.suggestions_skip_unverified_profiles',
                    'display_name' => 'Skip unverified profiles out of the suggestions list',
                    'value' => 0,
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : false,
                        }',
                    'type' => 'checkbox',
                    'order' => 73,
                    'group' => 'Feed',
                ),
            )
        );

        DB::table('settings')
            ->where('key', 'media.max_avatar_cover_file_size')
            ->update(['display_name' => 'Max avatar & cover file size']);



        if (Schema::hasTable('public_pages')) {
            Schema::table('public_pages', function (Blueprint $table) {
                $table->unsignedInteger('page_order')->default(0);
                $table->boolean('shown_in_footer')->nullable();
            });

            DB::table('public_pages')
                ->where('slug', 'help')
                ->update(['shown_in_footer' => 1, 'page_order'=>1]);

            DB::table('public_pages')
                ->where('slug', 'privacy')
                ->update(['shown_in_footer' => 1, 'page_order'=>2]);

            DB::table('public_pages')
                ->where('slug', 'terms-and-conditions')
                ->update(['shown_in_footer' => 1, 'page_order'=>3]);

        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Schema::dropIfExists('referral_code_usages');
        Schema::dropIfExists('rewards');
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('referral_code');
        });

        Schema::table('public_pages', function (Blueprint $table) {
            $table->dropColumn('page_order');
            $table->dropColumn('shown_in_footer');
        });

        DB::table('settings')
            ->whereIn('key', [
                'referrals.enabled',
                'referrals.fee_percentage',
                'referrals.apply_for_months',
                'referrals.fee_limit',
                'referrals.allow_only_for_verified',
                'site.default_wallet_balance_on_register',
                'feed.suggestions_skip_unverified_profiles'
            ])
            ->delete();
    }
}
