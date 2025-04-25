<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RunAllMigrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:all {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all migrations individually to support older migration file formats';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Running all migrations individually...');

        // Get all migration files
        $migrationPath = database_path('migrations');
        $migrationFiles = glob($migrationPath . '/*.php');

        if (empty($migrationFiles)) {
            $this->error('No migration files found in ' . $migrationPath);
            return 1;
        }

        $this->info('Found ' . count($migrationFiles) . ' migration files.');

        // Sort migration files by name to ensure they run in the correct order
        sort($migrationFiles);

        $successCount = 0;
        $failCount = 0;

        // Create a progress bar
        $bar = $this->output->createProgressBar(count($migrationFiles));
        $bar->start();

        foreach ($migrationFiles as $file) {
            $relativePath = str_replace(base_path() . '/', '', $file);

            // Build the command
            $command = 'migrate --path=' . $relativePath;

            // Add --force if specified
            if ($this->option('force')) {
                $command .= ' --force';
            }

            // Run the migration
            $this->newLine();
            $this->info("Running migration: " . basename($file));

            $exitCode = $this->callSilent($command);

            if ($exitCode === 0) {
                $this->info("✓ Migration successful: " . basename($file));
                $successCount++;
            } else {
                $this->error("✗ Migration failed: " . basename($file));
                $failCount++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        // Show summary
        $this->info("Migration Summary:");
        $this->info("- Total migrations: " . count($migrationFiles));
        $this->info("- Successful: " . $successCount);

        if ($failCount > 0) {
            $this->error("- Failed: " . $failCount);
            return 1;
        }

        $this->info("All migrations completed successfully!");
        return 0;
    }
}
