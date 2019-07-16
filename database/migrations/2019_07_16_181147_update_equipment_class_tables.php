<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEquipmentClassTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_classes', function (Blueprint $table) {
            $table->bigInteger('site_id')->unsigned();            
            $table->foreign('site_id')->references('id')->on('sites'); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('equipment_classes', function (Blueprint $table) {
            $table->dropForeign('equipment_classes_site_id_foreign');
            $table->dropColumn('site_id');
        });
    }
}
