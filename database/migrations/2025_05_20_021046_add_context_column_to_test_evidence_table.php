<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('test_evidence', function (Blueprint $table) {
            // Add context column after notes column (if notes column exists)
            // Set default to 'then' as that's the most common context for bug evidence
            // Using enum to restrict values to given, when, then
            if (Schema::hasColumn('test_evidence', 'notes')) {
                $table->enum('context', ['given', 'when', 'then'])->nullable()->after('notes');
            } else {
                // If notes column doesn't exist, add it at the end
                $table->enum('context', ['given', 'when', 'then'])->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('test_evidence', function (Blueprint $table) {
            $table->dropColumn('context');
        });
    }
};
