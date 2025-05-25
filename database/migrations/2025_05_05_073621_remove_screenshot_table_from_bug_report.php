<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->dropColumn('screenshot_url');
        });
    }

    public function down()
    {
        Schema::table('bug_reports', function (Blueprint $table) {
            $table->string('screenshot_url')->nullable();
        });
    }
};