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
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('type_id')->nullable();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            // $table->unsignedBigInteger('engineer_office_id')->nullable();
            $table->string('name')->nullable();
            $table->string('kroky')->nullable();
            $table->string('prefix')->nullable();
            $table->string('commercial')->nullable();
            $table->string('license')->nullable();
            $table->string('owner_name')->nullable();
            $table->string('owner_hardcopyid')->nullable();
            $table->string('commercial_expiration')->nullable();
            $table->timestamp('deleted_at')->nullable();
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
        Schema::dropIfExists('companies');
    }
};
