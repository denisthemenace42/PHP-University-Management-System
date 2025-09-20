<?php
/**
 * Database Migration Runner
 * 
 * Command-line tool for running database migrations
 * 
 * Usage:
 *   php migrate.php migrate        - Run all pending migrations
 *   php migrate.php rollback       - Rollback the last batch
 *   php migrate.php status         - Show migration status
 *   php migrate.php create <name>  - Create a new migration
 * 
 * @author University System
 * @version 1.0
 */

require_once __DIR__ . '/migrations/MigrationManager.php';

// Check if running from command line
if (php_sapi_name() !== 'cli') {
    echo "<h1>Database Migration Tool</h1>";
    echo "<p>This tool should be run from command line.</p>";
    echo "<p>Usage examples:</p>";
    echo "<ul>";
    echo "<li><code>php migrate.php migrate</code> - Run all pending migrations</li>";
    echo "<li><code>php migrate.php rollback</code> - Rollback the last batch</li>";
    echo "<li><code>php migrate.php status</code> - Show migration status</li>";
    echo "<li><code>php migrate.php create &lt;name&gt;</code> - Create a new migration</li>";
    echo "</ul>";
    exit;
}

try {
    $manager = new MigrationManager(__DIR__ . '/migrations');
    
    $command = $argv[1] ?? 'help';
    
    switch ($command) {
        case 'migrate':
        case 'up':
            $manager->migrate();
            break;
            
        case 'rollback':
        case 'down':
            $manager->rollback();
            break;
            
        case 'status':
            $manager->status();
            break;
            
        case 'create':
            if (!isset($argv[2])) {
                echo "❌ Error: Migration name is required.\n";
                echo "Usage: php migrate.php create <migration_name>\n";
                exit(1);
            }
            
            $name = $argv[2];
            $description = $argv[3] ?? '';
            $manager->createMigration($name, $description);
            break;
            
        case 'help':
        case '--help':
        case '-h':
        default:
            showHelp();
            break;
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}

function showHelp(): void
{
    echo "🎓 University System - Database Migration Tool\n";
    echo "==============================================\n\n";
    echo "USAGE:\n";
    echo "  php migrate.php <command> [options]\n\n";
    echo "COMMANDS:\n";
    echo "  migrate, up         Run all pending migrations\n";
    echo "  rollback, down      Rollback the last batch of migrations\n";
    echo "  status              Show the status of all migrations\n";
    echo "  create <name>       Create a new migration file\n";
    echo "  help                Show this help message\n\n";
    echo "EXAMPLES:\n";
    echo "  php migrate.php migrate\n";
    echo "  php migrate.php rollback\n";
    echo "  php migrate.php status\n";
    echo "  php migrate.php create \"Add user profile table\"\n\n";
    echo "NOTES:\n";
    echo "  - Migrations are run in chronological order\n";
    echo "  - Each migration should implement both up() and down() methods\n";
    echo "  - Migration files are stored in the migrations/ directory\n";
    echo "  - Migration history is tracked in the 'migrations' table\n\n";
}
?>
