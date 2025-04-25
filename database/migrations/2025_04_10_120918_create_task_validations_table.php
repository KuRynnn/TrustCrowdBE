<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaskValidationsTable extends Migration
{
    public function up()
    {
        Schema::create('task_validations', function (Blueprint $table) {
            $table->uuid('validation_id')->primary();
            $table->uuid('task_id');
            $table->uuid('qa_id');
            $table->string('validation_status'); // Valid, Invalid, Needs More Info
            $table->text('comments')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();

            $table->foreign('task_id')->references('task_id')->on('uat_tasks')->onDelete('cascade');
            $table->foreign('qa_id')->references('qa_id')->on('qa_specialists')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('task_validations');
    }
}
