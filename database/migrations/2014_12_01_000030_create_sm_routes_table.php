<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\SmRoute;

class CreateSmRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sm_routes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title', 200);
            $table->float('far', 10, 2);
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
        Schema::dropIfExists('sm_routes');
    }
}
