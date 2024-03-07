<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmVisitorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_visitors', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 255);
            $table->string('phone', 255)->nullable();
            $table->string('visitor_id', 255)->nullable();
            $table->integer('no_of_person')->nullable();
            $table->string('purpose', 255)->nullable();
            $table->date('date')->nullable();
            $table->string('in_time', 255)->nullable();
            $table->string('out_time', 255)->nullable();
            $table->string('file', 255)->nullable();
            $table->tinyInteger('active_status')->default(1);
            $table->timestamps();

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
        Schema::dropIfExists('sm_visitors');
    }
}
