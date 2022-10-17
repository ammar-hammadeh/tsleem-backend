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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assign_camps_id')->nullable();
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('ministry')->nullable();
            $table->unsignedBigInteger('kidana')->nullable();
            $table->string('user_signiture')->nullable();
            $table->string('ministry_signiture')->nullable();
            $table->string('kidana_signiture')->nullable();
            $table->string('license')->nullable();
            $table->string('qr')->nullable();
            $table->enum('status', ['signed', 'unsigned'])->default('unsigned');
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
        Schema::dropIfExists('contracts');
    }
};
