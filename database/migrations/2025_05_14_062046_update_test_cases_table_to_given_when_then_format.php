<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('test_cases', function (Blueprint $table) {
            // First add the new columns
            $table->text('given_context')->nullable()->after('test_title');
            $table->text('when_action')->nullable()->after('given_context');
            $table->text('then_result')->nullable()->after('when_action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('test_cases', function (Blueprint $table) {
            $table->dropColumn(['given_context', 'when_action', 'then_result']);
        });
    }

};
