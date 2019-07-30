<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateEquipUpdateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equip_update_logs', function (Blueprint $table) { 
            $table->bigInteger('equipment_id')->unsigned();             
            $table->foreign('equipment_id')->references('id')->on('equipments');  

            $table->bigInteger('user_id')->unsigned();             
            $table->foreign('user_id')->references('id')->on('users');            
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
            $table->dropForeign('equip_update_logs_equipment_id_foreign');            
            $table->dropColumn('equipment_id'); 

            $table->dropForeign('equip_update_logs_user_id_foreign');            
            $table->dropColumn('user_id');              
        });  
    }
}
