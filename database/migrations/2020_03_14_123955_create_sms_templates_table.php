<?php

use App\SmSchool;
use App\SmsTemplate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsTemplatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_templates', function (Blueprint $table) {
            $table->id();
            $table->string('type')->comment('email, sms');
            $table->text('purpose');
            $table->text('subject');
            $table->longText('body');
            $table->string('module');
            $table->text('variable');
            $table->integer('status')->default(1)->comment('Enable & Disable');
            $table->integer('church_id')->nullable()->default(1)->unsigned();
            $table->foreign('church_id')->references('id')->on('sm_schools')->onDelete('cascade');
            $table->timestamps();
        });

        $allTempletes = [
            // SMS Start

            ['sms', 'student_admission', '', 'Dear [member_name], Your Admission Is Completed You Can Login To Your Account Using username:[user_name] Password:[password], Thank You [church_name]', '', '[member_name], [user_name], [password], [church_name]'],
            ['sms', 'student_admission_for_parent', '', 'Dear Parent [parent_name], Your Child [member_name] Admission Is Completed You Can Login To Your Account Using username:[user_name] Password:[password], Thank You [church_name]', '', '[parent_name], [member_name], [user_name], [password], [church_name]'],
            ['sms', 'exam_schedule_for_student', '', 'Dear [member_name], your next exam: [exam_type] on [exam_date], [exam_time],Please Attend In Exam, Thank You [church_name]', '', '[member_name], [exam_type], [exam_date], [exam_time], [church_name]'],
            ['sms', 'exam_schedule_for_parent', '', 'Dear [parent_name], your children [member_name] next exam: [exam_type] on [exam_date], [exam_time], Thank You [church_name]', '', '[parent_name], [member_name], [exam_type], [exam_date], [exam_time], [church_name]'],
            ['sms', 'user_login_permission', '', 'Dear [name], your login permission is disabled username:[user_name], Thank You [church_name]', '', '[name], [user_name], [church_name]'],
            ['sms', 'student_promote', '', 'Hi [member_name] , Welcome to [church_name]. Congratulations ! You have promoted in the next class. Thank You [church_name]', '', '[member_name], [church_name]'],
            ['sms', 'communicate_sms', '', 'In Communicate SMS description is: [description]. Thank You. Thank You [church_name]', '', '[description], [church_name]'],

            ['sms', 'student_attendance', '', 'Dear [member_name], your are came to the school at [attendance_date], Thank You [church_name]', '', '[member_name], [attendance_date], [church_name]'],
            ['sms', 'student_attendance_for_parent', '', 'Dear Parent [parent_name], your child [member_name] came to the school at [attendance_date], Thank You [church_name]', '', '[parent_name], [member_name], [attendance_date], [church_name]'],
            ['sms', 'student_absent', '', 'Dear [member_name], your are absent to the school on [attendance_date], Thank You [church_name]', '', '[member_name], [attendance_date], [church_name]'],
            ['sms', 'student_absent_for_parent', '', 'Dear parent [parent_name], your child [member_name] is absent to the school on [attendance_date], Thank You [church_name]', '', '[parent_name], [member_name], [attendance_date], [church_name]'],
            ['sms', 'student_late', '', 'Dear [member_name], your are late to the school on [attendance_date], Thank You [church_name]', '', '[student_late], [attendance_date], [church_name]'],
            ['sms', 'student_late_for_parent', '', 'Dear parent [parent_name], your child [member_name] is late to the school on [attendance_date], Thank You [church_name]', '', '[parent_name], [member_name], [attendance_date], [church_name]'],
            ['sms', 'student_leave_appllication', '', 'Dear [member_name], Thank you for your leave application. Please wait for approval, Thank You [church_name]', '', '[member_name], [church_name]'],
            ['sms', 'student_leave_approve', '', 'Dear [member_name], Thank you for your leave application. Your Leave approve. Thank You [church_name]', '', '[member_name], [church_name]'],
            ['sms', 'parent_leave_appllication_for_student', '', 'Dear [parent_name], Thank you for your leave [member_name] application. Please wait for approval, Thank You [church_name]. Thanks', '', '[parent_name], [member_name], [church_name]'],
            ['sms', 'parent_leave_approve_for_student', '', 'Dear [parent_name], Thank you for your leave [member_name] application. Your Leave approve. Thank You [church_name]', '', '[parent_name], [member_name], [church_name]'],
            ['sms', 'student_library_book_issue', '', 'Dear [member_name], Library book  is issued to you studying in class: [age_group_name] , section: [mgender_name] with roll no:[roll_no] On [issue_date] .Please find the details , Book Title: [book_title], Book No: [book_no], Due Date: [due_date], Thank You [church_name]', '', '[member_name], [age_group_name], [mgender_name],[roll_no],[issue_date], [book_title], [book_no], [due_date], [church_name]'],
            ['sms', 'parent_library_book_issue', '', 'Dear parent [parent_name], Library book  is issued On [issue_date] .Please find the details , Book Title: [book_title], Book No: [book_no], Due Date: [due_date], Thank You [church_name]', '', '[parent_name], [issue_date], [book_title], [book_no], [due_date], [church_name]'],
            ['sms', 'student_return_issue_book', '', 'Dear [member_name], Library book  is returned by you studying in class: [age_group_name] , section: [mgender_name] with roll no:[roll_no] On [return_date] .Please find the details , Book Title: [book_title], Book No: [book_no], Issue Date: [issue_date], Due Date: [due_date], Thank You [church_name]', '', '[member_name], [age_group_name], [mgender_name], [roll_no], [return_date], [issue_date], [book_title], [book_no], [due_date], [church_name]'],
            ['sms', 'parent_return_issue_book', '', 'Dear parent [parent_name], Library book  is returned On [return_date] .Please find the details , Book Title: [book_title], Book No: [book_no], Issue Date: [issue_date], Due Date: [due_date], Thank You [church_name]', '', '[parent_name], [return_date], [issue_date], [book_title], [book_no], [due_date], [church_name]'],
            ['sms', 'exam_mark_student', '', 'Hi [member_name] , You are in class [age_group_name] ([mgender_name]), Your exam type [exam_type], [subject_marks]. School Name- [church_name], Thank You [church_name]', '', '[member_name], [age_group_name], [mgender_name], [exam_type], [subject_names], [total_mark], [church_name], [subject_marks]'],
            ['sms', 'exam_mark_parent', '', 'Hello, [parent_name], your child [member_name] of class [age_group_name] ([mgender_name]) exam type [exam_type], [subject_marks]. School Name- [church_name], Thank You [church_name]', '', '[parent_name], [member_name], [age_group_name], [mgender_name], [exam_type], [subject_names], [total_mark], [church_name], [subject_marks]'],
            ['sms', 'student_fees_due', '', 'Hi [member_name], You fees due amount [dues_amount] for [fees_name] on [date]. Thank You [church_name]', '', '[member_name], [dues_amount], [fees_name], [date], [church_name]'],
            ['sms', 'student_fees_due_for_parent', '', 'Hi [parent_name], You fees due amount [dues_amount] for [fees_name] on [date]. Thank You [church_name]', '', '[parent_name], [dues_amount], [fees_name], [date], [church_name]'],

            ['sms', 'staff_credentials', '', 'Dear staff [staff_name] your login details: username:[user_name] Password:[password], Thank You [church_name]', '', '[staff_name], [user_name], [password], [church_name]'],
            ['sms', 'staff_attendance', '', 'Dear [staff_name], your are came to the school at [attendance_date], Thank You [church_name]', '', '[staff_name],[attendance_date], [church_name]'],
            ['sms', 'staff_absent', '', 'Dear [staff_name], your are absent to the school on [attendance_date], Thank You [church_name]', '', '[staff_name], [attendance_date], [church_name]'],
            ['sms', 'staff_late', '', 'Dear [staff_name], your are late to the school on [attendance_date], Thank You [church_name]', '', '[staff_name], |StudentName|, [attendance_date], [church_name]'],
            ['sms', 'staff_leave_appllication', '', 'Dear staff [staff_name], Thank you for your leave application. Please wait for approval. Thank You [church_name]', '', '[staff_name], [church_name]'],
            ['sms', 'staff_leave_approve', '', 'Dear staff [staff_name], Thank you for your leave application. Your Leave approve. Thank You [church_name]', '', '[staff_name], [church_name]'],

            ['sms', 'holiday', '', 'This is to update you that [holiday_date] is holiday, Thank You [church_name]', '', '[holiday_date], [church_name]'],
            ['sms', 'student_birthday', '', 'Dear [member_name], Warm wishes to your birthday. Happy Birthday, Thank You [church_name]', '', '[member_name], [church_name]'],
            ['sms', 'staff_birthday', '', 'Dear staff [staff_name], Warm wishes to your birthday. Happy Birthday, Thank You [church_name]. Thanks', '', '[staff_name], [church_name]'],
            
            
            //Module Base SMS Sending Start
            // Module Name : InfixBiometrics
                ['sms', 'student_early_checkout', '', 'Dear parent [parent_name], your child [member_name] is checkout  at [attendance_date] to the school on [attendance_date], Thank You [church_name]','InfixBiometrics', '[parent_name], [member_name], [attendance_date], [attendance_date], [church_name]'],
                ['sms', 'student_checkout', '', 'Dear Parent [parent_name], your child [member_name] left the school at [left_time], Thank You [church_name]','InfixBiometrics', '[parent_name], [member_name], [church_name]'],
            
            // Module Name : Fees 2.0
                ['sms', 'student_dues_fees', '', 'Hi [member_name], You fees due amount [dues_amount] for [fees_name] on [date]. Thank You [church_name]','Fees', '[member_name], [dues_amount], [fees_name], [date], [church_name]'],
                ['sms', 'student_dues_fees_for_parent', '', 'Hi [parent_name], You fees due amount [dues_amount] for [fees_name] on [date]. Thank You [church_name]','Fees', '[parent_name], [dues_amount], [fees_name], [date], [church_name]'],

            // Module Name : ParentRegistration
                ['sms', 'student_admission_in_progress', '', 'Dear parent [parent_name], your child [member_name] admission is in process, Thank You [church_name]','ParentRegistration','[parent_name], [member_name], [church_name]'],
            // Module Base SMS Sending End

            ['sms', 'student_absent_notification', '', 'Hi [parent_name], Your child [member_name] absent for [number_of_subject] subjects. Those are [subject_list] on [date], Thank You [church_name]', '', '[parent_name], [member_name], [number_of_subject], [subject_list], [date], [church_name]'],
            // SMS End

            // Email Start
            ['email', 'test_mail', 'Test Mail',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Test Mail</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [user_name] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            It is test email . If your email configuration is okay then you will be got this email .Thank You [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[user_name], [church_name]'],


            ['email', 'password_reset', 'Password Reset',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                    <tbody>
                        <tr style="vertical-align:top;" valign="top">
                            <td style="vertical-align:top;" valign="top">
                                <div style="background-color:#415094;">
                                    <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                        <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                            <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                                <div class="col_cont" style="width:100%;">
                                                    <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                        <a href="#">
                                                            <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="background-color:#415094;">
                                    <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                        <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                            <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                                <div class="col_cont" style="width:100%;">
                                                    <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                        <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="background-color:#7c32ff;">
                                    <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                        <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                            <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                                <div class="col_cont" style="width:100%;">
                                                    <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                        <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                            <span style="font-size:36px;">Password Reset</span>
                                                        </font>
                                                    </h1>
                                                    <div style="line-height:1.8;padding:20px 15px;">
                                                        <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                            <h1>Hi [name],</h1>
                                                            <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                                Tap the button below to reset your account password. If you didnt request a new password, you can safely delete this email.
                                                            </p>
                                                            <p style="text-align:center;margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);"> 
                                                                <a href="[reset_link]" target="_blank" style="font-size:12px;line-height:40px;background:#7c32ff;color:#fff;letter-spacing:1px;font-weight:500;padding:19px 52px;text-align:center;text-transform:uppercase;border:0;text-decoration:none;font-family:Arial, Helvetica Neue, Helvetica;" rel="noreferrer noopener">
                                                                    RESET LINK
                                                                </a>
                                                            </p>
                                                            <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                                This password reset link will expire in 60 minutes.
                                                            </p>
                                                            <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                                If you did not request a password reset, no further action is required.
                                                            </p>
                                                            <p style="text-align:left;margin:0px;line-height:1.8;">
                                                                <br>
                                                            </p>
                                                            </br>
                                                            Thank You, [church_name]
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div style="background-color:#7c32ff;">
                                    <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                        <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                            <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                                <div class="col_cont" style="width:100%;">
                                                    <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                        <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                            <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                                <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                    © 2020 Infix Education software| 
                                                                </span>
                                                                <span style="background-color:transparent;text-align:left;">
                                                                    <font color="#ffffff">
                                                                        Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                    </font>
                                                                </span>
                                                                <br>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
            </table>', '', '[name], [reset_link], [church_name]'],

            ['email', 'student_login_credentials', 'Student Login Credentials',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Student Login Credentials</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [member_name] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Welcome to [church_name]. Congratulations ! You have registered successfully. Please login using this username [username] and password [password]. Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[member_name], [church_name], [username], [password]'],

            ['email', 'frontend_contact', 'Contact',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Contact Email From : [contact_name]
                                                            </br>
                                                            Email : [contact_email]
                                                            </br>
                                                            Subject : [contact_subject]
                                                            </br>
                                                            message: [contact_message]
                                                            </br>
                                                            Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[contact_name], [contact_email], [contact_subject], [contact_message], [church_name]'],

            ['email', 'communication_sent_email', 'Sent Email',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;"></span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1></h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Title : [title]
                                                            </br>
                                                            Description : [description]
                                                            </br>
                                                            Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[title], [description], [church_name]'],

            ['email', 'parent_login_credentials', 'Parent Login Credentials', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Parent Login Credentials</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [father_name] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Welcome to [church_name]. Congratulations ! You have registered successfully. Please login using this username [username] and password [password]. Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[member_name], [father_name], [church_name], [username], [password]'],


            ['email', 'staff_login_credentials', 'Staff Login Credentials', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Staff Login Credentials</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Welcome to [church_name]. Congratulations ! You have registered successfully. Please login using this username [username] and password [password]. Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[name], [church_name], [username], [password]'],

            ['email', 'due_fees_payment', 'Duee Fees Payment', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Dues Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [member_name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            You fees due amount [due_amount] for [fees_name] on [date]. Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','', '[member_name], [due_amount], [fees_name], [date], [church_name]'],

            ['email', 'dues_payment', 'Dues Payment', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Dues Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [member_name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            You fees due amount [due_amount] for [fees_name] on [date]. Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Fees', '[member_name], [due_amount], [fees_name], [date], [church_name]'],
            
            ['email', 'parent_reject_bank_payment', 'Reject Bank Payment', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Reject Bank Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Dear [parent_name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            your child [member_name] fees rejected . Reject Note [note] [date].Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[parent_name], [member_name], [note], [date], [church_name]'],

            ['email', 'student_reject_bank_payment', 'Reject Bank Payment', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Reject Bank Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Dear [member_name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Fees rejected . Reject Note [note] at [date] .Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', '', '[member_name], [note], [date], [church_name]'],

            ['email', 'wallet_approve', 'Approve Wallet Payment',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Approve Wallet Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Your Wallet Diposit Request [add_balance] via [method] on [create_date] is approved by [church_name] .Your current balance is now [current_balance] <br> Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Wallet','[name], [add_balance], [method], [create_date], [church_name], [current_balance]'],

            ['email', 'wallet_reject', 'Reject Wallet Payment', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Reject Wallet Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Your Wallet Diposit Request [add_balance] via [method] on [create_date] is <strong>Reject</strong> by [church_name] <strong>Reason: [reject_reason] </strong> .Your current balance is now [current_balance] <br> Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Wallet', '[name], [add_balance], [method], [create_date], [church_name], [reject_reason], [current_balance]'],

            ['email', 'fees_extra_amount_add', 'Fee Extra Amoun Add', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Fee Extra Amoun Add</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Your Xtra Fees Amount Add [church_name] , In Your Wallet Amount:  [add_balance] . Which Is Fees Add Payment Via [method] on [create_date] Your previous Balance Was [previous_balance] <br> .Your Current Balance Is Now [current_balance] <br> Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Wallet', '[name], [church_name], [add_balance], [method], [create_date], [previous_balance], [current_balance]'],

            ['email', 'wallet_refund', 'Wallet Refund',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Wallet Refund</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Your Amount [refund_amount] Is Successfully Refunded by [church_name] on [create_date] <br>.Your Current Balance Is Now [current_balance].Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Wallet', '[name], [refund_amount], [church_name], [create_date], [current_balance]'],
            
            ['email', 'student_registration', 'Student Registration',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Student Registration</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Welcome to [church_name]. Congratulations ! You have registered successfully.Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','ParentRegistration', '[name], [church_name]'],

            ['email', 'student_registration_for_parents', 'Student Registration', 
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Student Registration</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [father_name] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Welcome to [church_name]. Congratulations ! You have registered successfully. Please login using this username [username] and password [password]. Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','ParentRegistration','[father_name], [church_name], [username], [password]'],


            // lead reminder 
            ['email', 'lead_reminder', 'Lead Reminder ',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Test Mail</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [assign_user] ,</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">

                                                           title : [lead_name], <br>
                                                           Phone : [lead_phone],<br>
                                                           Email : [lead_email],<br>
                                                           Date Time : [lead_date] [lead_time],<br>
                                                           Details : [lead_description],</br>
                                                           Thank You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>', 'Lead', '[assign_user], [lead_name], [lead_phone], [lead_email], [lead_date], [lead_time], [lead_description], [church_name]'],

            ['email', 'student_course_purchase', 'Lms Course Purchase',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Lms Course Purchase</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Your Course Purchase Sucessfully <br> Thanks You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Lms','[name], [church_name]'],

            ['email', 'parent_course_purchase', 'Lms Course Purchase',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Lms Course Purchase</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Your Course Purchase Sucessfully <br> Thanks You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Lms','[name], [church_name]'],

            ['email', 'student_approve_payment', 'Approve Lms Course Payment',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Approve Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Approve Payment.Thanks You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Lms','[name], [church_name]'],

            ['email', 'parent_approve_payment', 'Approve Lms Course Payment',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Approve Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Approve Payment.Thanks You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Lms','[name], [church_name]'],
            
            ['email', 'student_reject_payment', 'Reject Lms Course Payment',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Reject Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Reject Payment.Thanks You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Lms','[name], [church_name]'],

            ['email', 'parent_reject_payment', 'Reject Lms Course Payment',
            '<table bgcolor="#FFFFFF" cellpadding="0" cellspacing="0" class="nl-container" style="table-layout:fixed;vertical-align:top;min-width:320px;border-spacing:0;border-collapse:collapse;background-color:#FFFFFF;width:100%;" width="100%">
                <tbody>
                    <tr style="vertical-align:top;" valign="top">
                        <td style="vertical-align:top;" valign="top">
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;background-position:center top;background-repeat:no-repeat;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center fixedwidth" style="padding-right:30px;padding-left:30px;">
                                                    <a href="#">
                                                        <img border="0" class="center fixedwidth" src="" style="padding-top:30px;padding-bottom:30px;text-decoration:none;height:auto;border:0;max-width:150px;" width="150" alt="logo.png">
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#415094;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;padding-top:25px;border-top-right-radius:30px;border-top-left-radius:30px;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div align="center" class="img-container center autowidth" style="padding-right:20px;padding-left:20px;">
                                                    <img border="0" class="center autowidth" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU" style="text-decoration:none;height:auto;border:0;max-width:541px;" width="541" alt="images?q=tbn:ANd9GcRGF00Oi-zJNU_EvYGueBVz_sqXmFjk8pxNtg&amp;usqp=CAU">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:#ffffff;border-bottom-right-radius:30px;border-bottom-left-radius:30px;overflow:hidden;">
                                    <div style="border-collapse:collapse;width:100%;background-color:#ffffff;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <h1 style="line-height:120%;text-align:center;margin-bottom:0px;">
                                                    <font color="#555555" face="Arial, Helvetica Neue, Helvetica, sans-serif">
                                                        <span style="font-size:36px;">Reject Payment</span>
                                                    </font>
                                                </h1>
                                                <div style="line-height:1.8;padding:20px 15px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.8;">
                                                        <h1>Hi [name],</h1>
                                                        <p style="margin:10px 0px 30px;line-height:1.929;font-size:16px;color:rgb(113,128,150);">
                                                            Reject Payment. Thanks You, [church_name]
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="background-color:#7c32ff;">
                                <div class="block-grid" style="min-width:320px;max-width:600px;margin:0 auto;background-color:transparent;">
                                    <div style="border-collapse:collapse;width:100%;background-color:transparent;">
                                        <div class="col num12" style="min-width:320px;max-width:600px;vertical-align:top;width:600px;">
                                            <div class="col_cont" style="width:100%;">
                                                <div style="color:#262b30;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;line-height:1.2;padding-top:30px;padding-right:5px;padding-bottom:5px;padding-left:5px;">
                                                    <div class="txtTinyMce-wrapper" style="line-height:1.2;font-size:12px;font-family:Arial, Helvetica Neue, Helvetica, sans-serif;color:#262b30;">
                                                        <p style="margin:0;font-size:12px;line-height:1.2;text-align:center;margin-top:0;margin-bottom:0;">
                                                            <span style="font-size:14px;color:rgb(255,255,255);font-family:Arial;">
                                                                © 2020 Infix Education software| 
                                                            </span>
                                                            <span style="background-color:transparent;text-align:left;">
                                                                <font color="#ffffff">
                                                                    Copyright &copy; 2020 All rights reserved | This application is made by Codethemes
                                                                </font>
                                                            </span>
                                                            <br>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>','Lms','[name], [church_name]'],

            // end lead reminder

            //Email End
        ];
        // $schools=SmSchool::get(['id','church_name']);
        // foreach($schools as $school){
            foreach($allTempletes as $allTemplete){
                $storeTemplete = new SmsTemplate();
                $storeTemplete->type = $allTemplete[0];
                $storeTemplete->purpose = $allTemplete[1];
                $storeTemplete->subject = $allTemplete[2];
                $storeTemplete->body = $allTemplete[3];
                $storeTemplete->module = $allTemplete[4];
                $storeTemplete->variable = $allTemplete[5];               
                // $storeTemplete->church_id = $school->id;
                $storeTemplete->save();
            }
        //  }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sms_templates');
    }
}
