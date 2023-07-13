<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class V510 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('user_messages')) {
            Schema::table('user_messages', function (Blueprint $table) {
                $table->float('price')->after('isSeen')->nullable();
            });
        }

        // Adds user_message_id on transactions table
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->unsignedBigInteger('user_message_id')->nullable()->after('invoice_id');
                $table->foreign('user_message_id')->references('id')->on('user_messages')->onDelete('cascade');
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
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropForeign('transactions_user_message_id_foreign');
                $table->dropColumn('user_message_id');
            });
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        Schema::table('user_messages', function (Blueprint $table) {
            $table->dropColumn('price');
        });
    }
}
