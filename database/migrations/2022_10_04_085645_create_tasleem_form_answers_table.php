<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasleem_form_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            // $table->unsignedBigInteger('company_id');
            // $table->unsignedBigInteger('square_id');
            // $table->unsignedBigInteger('camp_id');
            $table->unsignedBigInteger('assign_camps_id');
            $table->unsignedBigInteger('form_id');
            $table->unsignedBigInteger('question_id');
            $table->string('answer')->nullable();
            $table->string('note')->nullable();
            // $table->string('name');
            // $table->enum('status',['signed','unsigned'])->default('unsigned');
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
        Schema::dropIfExists('tasleem_form_answers');
    }
};
