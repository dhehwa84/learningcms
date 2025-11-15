<?php
// database/migrations/xxxx_xx_xx_xxxxxx_add_folder_name_to_units_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Unit;

return new class extends Migration
{
    public function up()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->string('folder_name')->nullable()->after('order');
        });

        // Set default folder_name for existing units
        $units = Unit::all();
        foreach ($units as $unit) {
            $unit->folder_name = 'unit-' . $unit->id;
            $unit->save();
        }

        // Make folder_name non-nullable after setting defaults
        Schema::table('units', function (Blueprint $table) {
            $table->string('folder_name')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('units', function (Blueprint $table) {
            $table->dropColumn('folder_name');
        });
    }
};