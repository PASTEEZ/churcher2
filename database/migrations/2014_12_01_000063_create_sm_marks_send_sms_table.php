<?php

    use Illuminate\Support\Facades\Schema;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Database\Migrations\Migration;

    class CreateSmMarksSendSmsTable extends Migration
    {
        /**
         * Run the migrations.
         *
         * @return void
         */
        public function up()
        {
            Schema::create('sm_marks_send_sms', function (Blueprint $table) {
                $table->increments('id');
                $table->tinyInteger('sms_send_status')->default(1);
                $table->tinyInteger('active_status')->default(1);
                $table->timestamps();

                $table->integer('exam_id')->nullable()->unsigned();
                $table->foreign('exam_id')->references('id')->on('sm_exams')->onDelete('cascade');

                $table->integer('member_id')->nullable()->unsigned();
                $table->foreign('member_id')->references('id')->on('sm_students')->onDelete('cascade');

                $table->integer('created_by')->nullable()->default(1)->unsigned();

                $table->integer('updated_by')->nullable()->default(1)->unsigned();

                $table->integer('church_id')->nullable()->default(1)->unsigned();
                $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
                
                $table->integer('church_year_id')->nullable()->default(1)->unsigned();
                $table->foreign('church_year_id')->references('id')->on('sm_academic_years')->onDelete('cascade');
            });

            //  Schema::table('sm_marks_send_sms', function($table) {
            //     $table->foreign('exam_id')->references('id')->on('sm_exams');
            //     $table->foreign('member_id')->references('id')->on('sm_students');


            // });
        }

        /**
         * Reverse the migrations.
         *
         * @return void
         */
        public function down()
        {
            Schema::dropIfExists('sm_marks_send_sms');
        }
    }
