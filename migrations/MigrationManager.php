<?php

require_once __DIR__ . '/Migration.php';

/**
 * Migration Manager
 * 
 * Manages the execution of database migrations, tracks migration history,
 * and provides rollback functionality.
 * 
 * @author University System
 * @version 1.0
 */
class MigrationManager
{
    private $db;
    private $migrationsPath;
    private $migrationsTable = 'migrations';
    
    public function __construct(?string $migrationsPath = null)
    {
        require_once __DIR__ . '/../Database.php';
        $this->db = Database::getInstance();
        $this->migrationsPath = $migrationsPath ?? __DIR__;
        
        $this->createMigrationsTable();
    }
    
    /**
     * Create the migrations tracking table
     */
    private function createMigrationsTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$this->migrationsTable}` (
            `id` INT PRIMARY KEY AUTO_INCREMENT,
            `migration` VARCHAR(255) NOT NULL UNIQUE,
            `batch` INT NOT NULL,
            `executed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
    }
    
    /**
     * Run all pending migrations
     */
    public function migrate(): void
    {
        echo "🚀 Starting database migrations...\n";
        echo "=====================================\n\n";
        
        $migrations = $this->getPendingMigrations();
        
        if (empty($migrations)) {
            echo "✅ No pending migrations found.\n";
            return;
        }
        
        $batch = $this->getNextBatchNumber();
        $successCount = 0;
        
        foreach ($migrations as $migrationFile) {
            try {
                echo "🔄 Running migration: {$migrationFile}\n";
                
                $migration = $this->loadMigration($migrationFile);
                $migration->up();
                
                $this->recordMigration($migrationFile, $batch);
                $successCount++;
                
                echo "✅ Completed: {$migration->getName()}\n";
                echo "   Description: {$migration->getDescription()}\n\n";
                
            } catch (Exception $e) {
                echo "❌ Failed migration: {$migrationFile}\n";
                echo "   Error: " . $e->getMessage() . "\n\n";
                break;
            }
        }
        
        echo "=====================================\n";
        echo "🎉 Migration completed! {$successCount} migrations executed.\n";
    }
    
    /**
     * Rollback the last batch of migrations
     */
    public function rollback(): void
    {
        echo "⏪ Starting migration rollback...\n";
        echo "=================================\n\n";
        
        $lastBatch = $this->getLastBatchNumber();
        if (!$lastBatch) {
            echo "❌ No migrations to rollback.\n";
            return;
        }
        
        $migrations = $this->getMigrationsInBatch($lastBatch);
        $rollbackCount = 0;
        
        // Rollback in reverse order
        foreach (array_reverse($migrations) as $migrationRecord) {
            try {
                echo "⏪ Rolling back: {$migrationRecord['migration']}\n";
                
                $migration = $this->loadMigration($migrationRecord['migration']);
                $migration->down();
                
                $this->removeMigrationRecord($migrationRecord['migration']);
                $rollbackCount++;
                
                echo "✅ Rolled back: {$migration->getName()}\n\n";
                
            } catch (Exception $e) {
                echo "❌ Failed rollback: {$migrationRecord['migration']}\n";
                echo "   Error: " . $e->getMessage() . "\n\n";
                break;
            }
        }
        
        echo "=================================\n";
        echo "🎉 Rollback completed! {$rollbackCount} migrations rolled back.\n";
    }
    
    /**
     * Show migration status
     */
    public function status(): void
    {
        echo "📊 Migration Status\n";
        echo "==================\n\n";
        
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = $this->getExecutedMigrations();
        
        if (empty($allMigrations)) {
            echo "❌ No migration files found.\n";
            return;
        }
        
        echo sprintf("%-40s %-10s %-20s\n", "Migration", "Status", "Executed At");
        echo str_repeat("-", 72) . "\n";
        
        foreach ($allMigrations as $migration) {
            $executed = isset($executedMigrations[$migration]);
            $status = $executed ? "✅ Done" : "⏳ Pending";
            $executedAt = $executed ? $executedMigrations[$migration]['executed_at'] : "-";
            
            echo sprintf("%-40s %-10s %-20s\n", $migration, $status, $executedAt);
        }
        
        echo "\n";
        $pendingCount = count($allMigrations) - count($executedMigrations);
        echo "Total migrations: " . count($allMigrations) . "\n";
        echo "Executed: " . count($executedMigrations) . "\n";
        echo "Pending: " . $pendingCount . "\n";
    }
    
    /**
     * Create a new migration file
     */
    public function createMigration(string $name, string $description = ''): string
    {
        $timestamp = date('Y_m_d_His');
        $className = 'Migration_' . $timestamp . '_' . $this->camelCase($name);
        $fileName = $timestamp . '_' . strtolower(str_replace(' ', '_', $name)) . '.php';
        $filePath = $this->migrationsPath . '/' . $fileName;
        
        $template = $this->getMigrationTemplate($className, $name, $description);
        
        file_put_contents($filePath, $template);
        
        echo "✅ Created migration: {$fileName}\n";
        echo "📁 Location: {$filePath}\n";
        
        return $filePath;
    }
    
    /**
     * Get all migration files
     */
    private function getAllMigrationFiles(): array
    {
        $files = glob($this->migrationsPath . '/*.php');
        $migrations = [];
        
        foreach ($files as $file) {
            $fileName = basename($file);
            if ($fileName !== 'Migration.php' && $fileName !== 'MigrationManager.php') {
                $migrations[] = $fileName;
            }
        }
        
        sort($migrations);
        return $migrations;
    }
    
    /**
     * Get pending migrations
     */
    private function getPendingMigrations(): array
    {
        $allMigrations = $this->getAllMigrationFiles();
        $executedMigrations = array_keys($this->getExecutedMigrations());
        
        return array_diff($allMigrations, $executedMigrations);
    }
    
    /**
     * Get executed migrations
     */
    private function getExecutedMigrations(): array
    {
        $result = $this->db->select("SELECT migration, executed_at FROM {$this->migrationsTable} ORDER BY id");
        
        $migrations = [];
        foreach ($result as $row) {
            $migrations[$row['migration']] = $row;
        }
        
        return $migrations;
    }
    
    /**
     * Load a migration class
     */
    private function loadMigration(string $fileName): Migration
    {
        $filePath = $this->migrationsPath . '/' . $fileName;
        
        if (!file_exists($filePath)) {
            throw new Exception("Migration file not found: {$fileName}");
        }
        
        require_once $filePath;
        
        // Extract class name from file
        $content = file_get_contents($filePath);
        preg_match('/class\s+(\w+)\s+extends\s+Migration/', $content, $matches);
        
        if (empty($matches[1])) {
            throw new Exception("Could not find migration class in file: {$fileName}");
        }
        
        $className = $matches[1];
        
        if (!class_exists($className)) {
            throw new Exception("Migration class not found: {$className}");
        }
        
        return new $className();
    }
    
    /**
     * Record a migration as executed
     */
    private function recordMigration(string $migration, int $batch): void
    {
        $this->db->executeQuery(
            "INSERT INTO {$this->migrationsTable} (migration, batch) VALUES (?, ?)",
            [$migration, $batch]
        );
    }
    
    /**
     * Remove a migration record
     */
    private function removeMigrationRecord(string $migration): void
    {
        $this->db->executeQuery(
            "DELETE FROM {$this->migrationsTable} WHERE migration = ?",
            [$migration]
        );
    }
    
    /**
     * Get next batch number
     */
    private function getNextBatchNumber(): int
    {
        $result = $this->db->selectOne("SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}");
        return ($result && $result['max_batch']) ? $result['max_batch'] + 1 : 1;
    }
    
    /**
     * Get last batch number
     */
    private function getLastBatchNumber(): ?int
    {
        $result = $this->db->selectOne("SELECT MAX(batch) as max_batch FROM {$this->migrationsTable}");
        return $result ? $result['max_batch'] : null;
    }
    
    /**
     * Get migrations in a specific batch
     */
    private function getMigrationsInBatch(int $batch): array
    {
        return $this->db->select(
            "SELECT migration, executed_at FROM {$this->migrationsTable} WHERE batch = ? ORDER BY id",
            [$batch]
        );
    }
    
    /**
     * Convert string to camelCase
     */
    private function camelCase(string $string): string
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }
    
    /**
     * Get migration template
     */
    private function getMigrationTemplate(string $className, string $name, string $description): string
    {
        return "<?php

require_once __DIR__ . '/Migration.php';

/**
 * {$name} Migration
 * 
 * {$description}
 */
class {$className} extends Migration
{
    public function up(): void
    {
        // TODO: Implement migration logic here
        // Example:
        // \$this->createTable('example_table', [
        //     ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
        //     ['name' => 'name', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
        //     ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP']
        // ], [
        //     'PRIMARY KEY (id)'
        // ]);
    }
    
    public function down(): void
    {
        // TODO: Implement rollback logic here
        // Example:
        // \$this->dropTable('example_table');
    }
    
    public function getName(): string
    {
        return '{$name}';
    }
    
    public function getDescription(): string
    {
        return '{$description}';
    }
}
?>";
    }
}
?>
