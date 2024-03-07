<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmAssignVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_assign_vehicles', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

            $table->integer('vehicle_id')->nullable()->unsigned();
            $table->foreign('vehicle_id')->references('id')->on('sm_vehicles')->onDelete('cascade');

            $table->integer('route_id')->nullable()->unsigned();
            $table->foreign('route_id')->references('id')->on('sm_routes')->onDelete('cascade');

            $table->integer('created_by')->nullable()->default(1)->unsigned();

            $table->integer('updated_by')->nullable()->default(1)->unsigned();

            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
            
            $table->integer('church_year_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_year_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sm_assign_vehicles');
    }
}
