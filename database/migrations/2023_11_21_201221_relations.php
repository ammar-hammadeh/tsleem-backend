<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Relations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('type_id')->references('id')->on('types')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            // $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
        });

        Schema::table('users_attachements', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::table('camps', function (Blueprint $table) {
            $table->foreign('square_id')->references('id')->on('square')->onDelete('cascade');
        });
        Schema::table('assign_camps', function (Blueprint $table) {
            $table->foreign('assigner_company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('receiver_company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            $table->foreign('square_id')->references('id')->on('square')->onDelete('cascade');
        });


        Schema::table('companies', function (Blueprint $table) {
            $table->foreign('type_id')->references('id')->on('types')->onDelete('set null');
            $table->foreign('owner_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('parent_id')->references('id')->on('companies')->onDelete('set null');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('type_id')->references('id')->on('types')->onDelete('set null');
        });
        Schema::table('users_appointments', function (Blueprint $table) {
            $table->foreign('assign_camp_id')->references('id')->on('assign_camps')->onDelete('cascade');
            // $table->foreign('square_id')->references('id')->on('square')->onDelete('cascade');
            // $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->foreign('input_id')->references('id')->on('inputs')->onDelete('set null');
        });
        Schema::table('form_questions', function (Blueprint $table) {
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('form_tamplates')->onDelete('cascade');
        });
        Schema::table('tasleem_form_answers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            // $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            // $table->foreign('square_id')->references('id')->on('square')->onDelete('cascade');
            // $table->foreign('camp_id')->references('id')->on('camps')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('form_tamplates')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

        Schema::table('company_attachments', function (Blueprint $table) {
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });
        Schema::table('forms_signs', function (Blueprint $table) {
            $table->foreign('assign_camps_id')->references('id')->on('assign_camps')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('form_tamplates')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
        });

        Schema::table('contracts', function (Blueprint $table) {
            $table->foreign('assign_camps_id')->references('id')->on('assign_camps')->onDelete('set null');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('ministry')->references('id')->on('users')->onDelete('set null');
            $table->foreign('kidana')->references('id')->on('users')->onDelete('set null');
        });


        Schema::table('form_signers', function (Blueprint $table) {
            $table->foreign('form_id')->references('id')->on('form_tamplates')->onDelete('cascade');
            $table->foreign('type_id')->references('id')->on('types')->onDelete('cascade');
        });

        Schema::table('question_category_relations', function (Blueprint $table) {
            $table->foreign('question_category_id')->references('id')->on('question_categories')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('questions')->onDelete('cascade');
        });

        Schema::table('form_categories', function (Blueprint $table) {
            $table->foreign('question_category_id')->references('id')->on('question_categories')->onDelete('cascade');
            $table->foreign('form_id')->references('id')->on('form_tamplates')->onDelete('cascade');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('action_id')->references('id')->on('log_actions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign('type_id');
            $table->dropForeign('city_id');
            $table->dropForeign('parent_id');
            $table->dropForeign('company_id');
            $table->dropForeign('category_id');
        });

        Schema::table('users_attachements', function (Blueprint $table) {
            $table->dropForeign('user_id');
        });

        Schema::table('camps', function (Blueprint $table) {
            $table->dropForeign('square_id');
            $table->dropForeign('assigner_company_id');
            $table->dropForeign('receiver_company_id');
        });

        Schema::table('assign_camps', function (Blueprint $table) {
            // $table->dropForeign('assigner_company_id');
            $table->dropForeign('receiver_company_id');
            $table->dropForeign('camp_id');
        });


        Schema::table('companies', function (Blueprint $table) {
            $table->dropForeign('type_id');
            $table->dropForeign('owner_id');
            $table->dropForeign('engineer_office_id');
        });
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('type_id');
        });
        Schema::table('users_appointments', function (Blueprint $table) {
            $table->dropForeign('assign_camps');
            // $table->dropForeign('square_id');
            // $table->dropForeign('camp_id');
        });
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign('input_id');
        });
        Schema::table('form_questions', function (Blueprint $table) {
            $table->dropForeign('form_id');
            $table->dropForeign('question_id');
        });
        Schema::table('tasleem_form_answers', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('assign_camps_id');
            // $table->dropForeign('square_id');
            // $table->dropForeign('camp_id');
            $table->dropForeign('form_id');
            $table->dropForeign('question_id');
        });
        Schema::table('forms_signs', function (Blueprint $table) {
            $table->dropForeign('assign_camps_id');
            $table->dropForeign('form_id');
            $table->dropForeign('user_id');
            $table->dropForeign('type_id');
        });

        Schema::table('company_attachments', function (Blueprint $table) {
            $table->dropForeign('company_id');
        });
        // Schema::table('answer_form_signed', function (Blueprint $table) {
        //     $table->dropForeign('answer_form_id');
        //     $table->dropForeign('user_id');
        //     $table->dropForeign('type_id');
        // });
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropForeign('assign_camps_id');
            $table->dropForeign('company_id');
            $table->dropForeign('user_id');
            $table->dropForeign('ministry');
            $table->dropForeign('kidana');
        });

        Schema::table('question_category_relations', function (Blueprint $table) {
            $table->dropForeign('question_category_id');
            $table->dropForeign('question_id');
        });

        Schema::table('form_categories', function (Blueprint $table) {
            $table->dropForeign('question_category_id');
            $table->dropForeign('form_id');
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->dropForeign('user_id');
            $table->dropForeign('action_id');
        });
    }
}
