<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEquipUpdateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equip_update_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('date');
            $table->string('shift');
            $table->integer('smu');
            $table->string('unit');
            $table->string('equipment_class');
            $table->string('summary')->nullable();
            $table->float('parked_hrs')->default('12.00');
            $table->float('operated_hrs')->nullable();
            $table->float('down_hrs')->nullable()->default('0.00');
            $table->string('start_of_shift_status');
            $table->string('comments')->nullable();
            $table->string('current_status')->nullable();
            $table->time('down_at')->nullable();
            $table->time('up_at')->nullable();
            $table->time('time_entry')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equip_update_logs');
    }
}
