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
        Schema::create('users_appointments', function (Blueprint $table) {
            $table->id();
            // $table->unsignedBigInteger('company_id');
            // $table->unsignedBigInteger('square_id');
            $table->unsignedBigInteger('assign_camp_id');
            $table->enum('appointment_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->enum('deliver_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('appointment');
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
        Schema::dropIfExists('users_appointments');
    }
};
