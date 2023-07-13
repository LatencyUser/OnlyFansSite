<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class V360 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('ccbill_payment_token')->after('nowpayments_order_id')->nullable();
                $table->string('ccbill_transaction_id')->after('ccbill_payment_token')->nullable();
                $table->string('ccbill_subscription_id')->after('ccbill_transaction_id')->nullable();
            });
        }

        if (Schema::hasTable('subscriptions')) {
            Schema::table('subscriptions', function (Blueprint $table) {
                $table->string('ccbill_subscription_id')->after('paypal_plan_id')->nullable();
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $table->float('profile_access_price_3_months')->after('profile_access_price_6_months')->default(5)->nullable();
            });
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'payments.ccbill_account_number',
                        'display_name' => 'CCBill Account Number',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 28,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.ccbill_subaccount_number_recurring',
                        'display_name' => 'CCBill SubAccount Number Recurring Payments',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 29,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.ccbill_subaccount_number_one_time',
                        'display_name' => 'CCBill SubAccount Number One Time Payments',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 30,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.ccbill_flex_form_id',
                        'display_name' => 'CCBill FlexForm Id',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 31,
                        'group' => 'Payments',
                    ),
                    array (
                        'key' => 'payments.ccbill_salt_key',
                        'display_name' => 'CCBill Salt Key',
                        'value' => NULL,
                        'details' => NULL,
                        'type' => 'text',
                        'order' => 32,
                        'group' => 'Payments',
                    )
                )
            );
        }

        if (Schema::hasTable('settings')) {
            DB::table('settings')
                ->where('key', 'invoices.sender_name')
                ->update(['key' => 'payments.invoices_sender_name', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.sender_country_name')
                ->update(['key' => 'payments.invoices_sender_country_name', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.sender_street_address')
                ->update(['key' => 'payments.invoices_sender_street_address', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.sender_state_name')
                ->update(['key' => 'payments.invoices_sender_state_name', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.sender_city_name')
                ->update(['key' => 'payments.invoices_sender_city_name', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.sender_postcode')
                ->update(['key' => 'payments.invoices_sender_postcode', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.sender_company_number')
                ->update(['key' => 'payments.invoices_sender_company_number', 'group' => 'Payments']);

            DB::table('settings')
                ->where('key', 'invoices.prefix')
                ->update(['key' => 'payments.invoices_prefix', 'group' => 'Payments']);


            // Adult entry dialog
            DB::table('settings')
                ->where('key', 'site.enable_cookies_box')
                ->update(['key' => 'compliance.enable_cookies_box', 'group' => 'Compliance', 'order' => 1130]);

            // Order: 1130
            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'compliance.enable_age_verification_dialog',
                        'display_name' => 'Enable age verification dialog',
                        'value' => NULL,
                        'details' => '{
"on" : "On",
"off" : "Off",
"checked" : true,
"description" "Can be generally used for denying user access for minors, adult content info, etc."
}',
                        'type' => 'checkbox',
                        'order' => 1140,
                        'group' => 'Compliance',
                    ),
                )
            );

            DB::table('settings')->insert(
                array(
                    array (
                        'key' => 'compliance.age_verification_cancel_url',
                        'display_name' => 'Age verification box cancel url',
                        'value' => 'https://google.com',
                        'type' => 'text',
                        'order' => 1150,
                        'group' => 'Compliance'
                    ),
                )
            );


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
                'payments.ccbill_account_number',
                'payments.ccbill_subaccount_number_recurring',
                'payments.ccbill_subaccount_number_one_time',
                'payments.ccbill_flex_form_id',
                'payments.ccbill_salt_key',
                'compliance.enable_age_verification_dialog',
                'compliance.age_verification_cancel_url'
            ])
            ->delete();

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('ccbill_payment_token');
            $table->dropColumn('ccbill_transaction_id');
            $table->dropColumn('ccbill_subscription_id');
        });
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('ccbill_subscription_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_access_price_3_months');
        });
    }
}
