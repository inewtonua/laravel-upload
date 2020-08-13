<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UploadMigration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uploads', function (Blueprint $table) {

//            $table->bigIncrements('id');
//            $table->unsignedBigInteger('user_id');
//            $table->boolean('status')->default(false);
//
//            $table->string('model');
//            $table->unsignedInteger('model_id')->nullable();
//            $table->string('uploadable_entity', 64);
//
//            $table->string('original_name');
//            $table->string('path');
//            $table->string('disk', 32);
//            $table->string('file_name');
//            $table->string('file_mime');
//            $table->boolean('private')->default(false);
//            $table->unsignedInteger('weight')->nullable();
//
//            $table->json('meta')->nullable();
//            $table->json('styles')->nullable();
//
//            $table->timestamps();
//
//            $table->index(['status', 'model', 'model_id', 'uploadable_entity']);

        });

    }
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('uploads');
    }
}