<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestEvidenceTable extends Migration
{
    public function up()
    {
        Schema::create('test_evidence', function (Blueprint $table) {
            $table->uuid('evidence_id')->primary(); // ✅ UUID primary key
            $table->uuid('bug_id')->nullable();
            $table->uuid('task_id')->nullable();
            $table->integer('step_number');
            $table->text('step_description')->nullable();
            $table->string('screenshot_url');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('bug_id')->references('bug_id')->on('bug_reports')->onDelete('cascade');
            $table->foreign('task_id')->references('task_id')->on('uat_tasks')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('test_evidence');
    }
}
