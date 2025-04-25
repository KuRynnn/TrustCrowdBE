<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bug_validations', function (Blueprint $table) {
            $table->uuid('validation_id')->primary();
            $table->uuid('bug_id');
            $table->foreign('bug_id')->references('bug_id')->on('bug_reports')->onDelete('cascade');
            $table->uuid('qa_id');
            $table->foreign('qa_id')->references('qa_id')->on('qa_specialists')->onDelete('cascade');
            $table->string('validation_status')->default('pending');
            $table->text('comments')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bug_validations');
    }
};