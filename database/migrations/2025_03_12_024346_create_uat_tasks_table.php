<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('uat_tasks', function (Blueprint $table) {
            $table->uuid('task_id')->primary();
            $table->uuid('app_id');
            $table->foreign('app_id')
                ->references('app_id')
                ->on('applications')
                ->onDelete('cascade');
            $table->uuid('test_id');
            $table->foreign('test_id')
                ->references('test_id')
                ->on('test_cases')
                ->onDelete('cascade');
            $table->uuid('worker_id');
            $table->foreign('worker_id')
                ->references('worker_id')
                ->on('crowdworkers')
                ->onDelete('cascade');
            $table->string('status')->default('assigned');
            $table->integer('revision_count')->default(0);
            $table->string('revision_status')->default('None');
            $table->text('revision_comments')->nullable();
            $table->timestamp('last_revised_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('uat_tasks');
    }
};