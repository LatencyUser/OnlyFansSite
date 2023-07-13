<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V170 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        /**
         * User gender - pronoun & other tweaks
         */
        Schema::create('user_genders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('gender_name')->index();
            $table->timestamps();
        });

        \DB::table('user_genders')->insert(array(
            array('gender_name' => 'Male'),
            array('gender_name' => 'Female'),
            array('gender_name' => 'Couple'),
            array('gender_name' => 'Other'),
        ));

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedBigInteger('gender_id')->nullable()->after('password');
                $table->foreign('gender_id')->references('id')->on('user_genders');
                $table->string('gender_pronoun')->nullable()->after('gender_id');
                $table->index('birthdate');
                $table->index('location');
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(array(
                0 => array(
                    'key' => 'site.allow_gender_pronouns',
                    'display_name' => 'Allow gender pronouns',
                    'value' => '1',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true,
                        "description": "If enabled, users will be able to enter custom pronouns which will be shown on profiles."
                    }',
                    'type' => 'checkbox',
                    'order' => 79,
                    'group' => 'Site',

                )
            ));
        }


        /**
         * Some new settings
         */

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(array(
                0 => array(
                    'key' => 'site.redirect_page_after_register',
                    'display_name' => 'Redirect page after register',
                    'value' => 'feed',
                    'details' => '
                    {
"default" : "feed",
"options" : {
"feed": "Feed page",
"settings": "User profile settings page"
}
}',
                    'type' => 'radio_btn',
                    'order' => 76,
                    'group' => 'Site',

                )
            ));
        }

        /**
         * Offline payments
         */
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedBigInteger('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->string('status')->default('pending');
            $table->string('type')->nullable();
            $table->string('reason')->nullable();
            $table->string('message')->nullable();
            $table->float('amount')->nullable();

            $table->timestamps();
        });

        if (Schema::hasTable('attachments')) {
            Schema::table('attachments', function (Blueprint $table) {
                $table->unsignedBigInteger('payment_request_id')->nullable();
                $table->foreign('payment_request_id')->references('id')->on('payment_requests')->onDelete('cascade');
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(array(
                0 => array(
                    'key' => 'payments.allow_manual_payments',
                    'display_name' => 'Allow manual payments',
                    'value' => '0',
                    'details' => '{
                        "true" : "On",
                        "false" : "Off",
                        "checked" : true
                    }',
                    'type' => 'checkbox',
                    'order' => 43,
                    'group' => 'Payments',

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
        Schema::dropIfExists('payment_request');
    }
}
