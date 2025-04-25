#!/bin/bash

# Script to run all migrations individually in Laravel 12
# This is useful when migration files use the older class-based format instead of anonymous classes

echo "Running all migrations individually..."

# Get the directory of the script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Path to migrations directory
MIGRATIONS_DIR="$SCRIPT_DIR/database/migrations"

# Check if migrations directory exists
if [ ! -d "$MIGRATIONS_DIR" ]; then
    echo "Error: Migrations directory not found at $MIGRATIONS_DIR"
    exit 1
fi

# Count total migrations
TOTAL_MIGRATIONS=$(find "$MIGRATIONS_DIR" -type f -name "*.php" | wc -l)
echo "Found $TOTAL_MIGRATIONS migration files."

# Initialize counters
SUCCESS_COUNT=0
FAIL_COUNT=0

# Process each migration file
while IFS= read -r migration_file; do
    filename=$(basename "$migration_file")
    relative_path="database/migrations/$filename"
    
    echo "Running migration: $filename"
    
    # Run the migration with the specific path
    if php artisan migrate --path="$relative_path"; then
        echo "✓ Migration successful: $filename"
        ((SUCCESS_COUNT++))
    else
        echo "✗ Migration failed: $filename"
        ((FAIL_COUNT++))
    fi
    
    echo "-----------------------------------"
done < <(find "$MIGRATIONS_DIR" -type f -name "*.php" | sort)

# Show summary
echo ""
echo "Migration Summary:"
echo "- Total migrations: $TOTAL_MIGRATIONS"
echo "- Successful: $SUCCESS_COUNT"

if [ $FAIL_COUNT -gt 0 ]; then
    echo "- Failed: $FAIL_COUNT"
    exit 1
fi

echo "All migrations completed successfully!"
exit 0
