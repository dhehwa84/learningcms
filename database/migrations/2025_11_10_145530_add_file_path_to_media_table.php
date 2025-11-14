<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_file_path_to_media_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->string('file_path', 500)->nullable()->after('url');
        });
    }

    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('file_path');
        });
    }
};