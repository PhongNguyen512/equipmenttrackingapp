<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) { 
            $table->bigInteger('user_role_id')->unsigned();             
            $table->foreign('user_role_id')->references('id')->on('user_roles');             
        }); 
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) { 
            $table->dropForeign('users_user_role_id_foreign');            
            $table->dropColumn('user_role_id');             
        });         
    }
}
