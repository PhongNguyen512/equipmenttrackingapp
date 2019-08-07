<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEstTimeRepairInEquipUpdateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equip_update_logs', function (Blueprint $table) {
            $table->string('est_date_of_repair')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equip_update_logs', function (Blueprint $table) {
            $table->dropColumn('est_date_of_repair');
        });
    }
}
