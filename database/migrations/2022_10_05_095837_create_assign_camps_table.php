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
        Schema::create('assign_camps', function (Blueprint $table) {
            $table->id();
            $table->string('assigner_cr')->nullable();
            // $table->string('receiver_cr')->nullable();
            $table->unsignedBigInteger('assigner_company_id')->nullable();
            $table->unsignedBigInteger('receiver_company_id')->nullable();
            $table->unsignedBigInteger('square_id');
            $table->unsignedBigInteger('camp_id');
            $table->timestamp('deleted_at')->nullable();
            $table->enum('contract_status', ['signed', 'unsigned', 'not_created'])->default('not_created');
            $table->enum('status', ['pending', 'returned', 'appointment', 'answered', 'deliverd'])->default('pending');
            $table->enum('forms_status', ['signed', 'unsigned'])->default('unsigned');
            $table->tinyInteger('notified')->default('0');
            $table->timestamp('last_notified')->nullable();
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
        Schema::dropIfExists('assign_camps');
    }
};
