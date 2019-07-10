<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEquipmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipments', function (Blueprint $table) {
            $table->bigInteger('equipment_category_id')->unsigned();
            $table->bigInteger('site_id')->unsigned();            
            $table->foreign('equipment_category_id')->references('id')->on('equipment_classes');  
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
        Schema::table('equipments', function (Blueprint $table) {
            $table->dropForeign('equipment_equipment_category_id_foreign');
            $table->dropColumn('equipment_category_id');
            $table->dropForeign('equipment_site_id_foreign');
            $table->dropColumn('site_id');
        });
    }
}
