<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class V181 extends Migration
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
                ->where('key', 'emails.smtp_encryption')
                ->update(['details' => '{
"default" : "tls",
"options" : {
"tls": "TLS",
"ssl": "SSL"
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
    }
}
