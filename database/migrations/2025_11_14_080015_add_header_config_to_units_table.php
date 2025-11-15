<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_header_config_to_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->json('header_config')->nullable()->after('theme');
        });
    }

    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('header_config');
        });
    }
};