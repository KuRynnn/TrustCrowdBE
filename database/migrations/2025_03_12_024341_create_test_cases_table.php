<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('test_cases', function (Blueprint $table) {
            $table->uuid('test_id')->primary();
            $table->uuid('app_id');
            $table->foreign('app_id')
                ->references('app_id')
                ->on('applications')
                ->onDelete('cascade');
            $table->uuid('qa_id');
            $table->foreign('qa_id')
                ->references('qa_id')
                ->on('qa_specialists')
                ->onDelete('cascade');
            $table->string('test_title');
            $table->text('test_steps');
            $table->text('expected_result');
            $table->string('priority')->default('medium');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_cases');
    }
};