<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bug_reports', function (Blueprint $table) {
            $table->uuid('bug_id')->primary();
            $table->uuid('task_id');
            $table->foreign('task_id')->references('task_id')->on('uat_tasks')->onDelete('cascade');
            $table->uuid('worker_id');
            $table->foreign('worker_id')->references('worker_id')->on('crowdworkers')->onDelete('cascade');
            $table->text('bug_description');
            $table->text('steps_to_reproduce');
            $table->string('severity')->default('low');
            $table->string('screenshot_url')->nullable();
            $table->uuid('original_bug_id')->nullable();
            $table->foreign('original_bug_id')
                ->references('bug_id')
                ->on('bug_reports')
                ->onDelete('set null');
            $table->boolean('is_revision')->default(false);
            $table->integer('revision_number')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bug_reports');
    }
};