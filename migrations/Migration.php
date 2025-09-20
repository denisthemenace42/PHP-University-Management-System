<?php

/**
 * Base Migration Class
 * 
 * Abstract class that defines the structure for database migrations.
 * All migration classes should extend this class and implement the up() and down() methods.
 * 
 * @author University System
 * @version 1.0
 */
abstract class Migration
{
    protected $db;
    protected $tableName;
    
    public function __construct()
    {
        require_once __DIR__ . '/../Database.php';
        $this->db = Database::getInstance();
    }
    
    /**
     * Run the migration (create tables, add columns, etc.)
     */
    abstract public function up(): void;
    
    /**
     * Reverse the migration (drop tables, remove columns, etc.)
     */
    abstract public function down(): void;
    
    /**
     * Get migration name
     */
    abstract public function getName(): string;
    
    /**
     * Get migration description
     */
    abstract public function getDescription(): string;
    
    /**
     * Create a table with the given structure
     * 
     * @param string $tableName
     * @param array $columns
     * @param array $constraints
     * @param array $indexes
     */
    protected function createTable(string $tableName, array $columns, array $constraints = [], array $indexes = []): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (\n";
        
        // Add columns
        $columnDefinitions = [];
        foreach ($columns as $column) {
            $columnDefinitions[] = "    " . $this->buildColumnDefinition($column);
        }
        
        $sql .= implode(",\n", $columnDefinitions);
        
        // Add constraints
        if (!empty($constraints)) {
            $sql .= ",\n";
            $constraintDefinitions = [];
            foreach ($constraints as $constraint) {
                $constraintDefinitions[] = "    " . $constraint;
            }
            $sql .= implode(",\n", $constraintDefinitions);
        }
        
        $sql .= "\n) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        $this->db->executeQuery($sql);
        
        // Add indexes
        foreach ($indexes as $index) {
            $this->addIndex($tableName, $index);
        }
        
        echo "✅ Created table: {$tableName}\n";
    }
    
    /**
     * Drop a table
     * 
     * @param string $tableName
     */
    protected function dropTable(string $tableName): void
    {
        $sql = "DROP TABLE IF EXISTS `{$tableName}`";
        $this->db->executeQuery($sql);
        echo "❌ Dropped table: {$tableName}\n";
    }
    
    /**
     * Add an index to a table
     * 
     * @param string $tableName
     * @param array $indexConfig
     */
    protected function addIndex(string $tableName, array $indexConfig): void
    {
        $indexName = $indexConfig['name'];
        $columns = is_array($indexConfig['columns']) ? implode(', ', $indexConfig['columns']) : $indexConfig['columns'];
        $type = $indexConfig['type'] ?? 'INDEX';
        
        $sql = "CREATE {$type} `{$indexName}` ON `{$tableName}` ({$columns})";
        
        try {
            $this->db->executeQuery($sql);
            echo "  📊 Added {$type}: {$indexName} on {$tableName}\n";
        } catch (Exception $e) {
            // Index might already exist, that's ok
            if (!str_contains($e->getMessage(), 'Duplicate key name')) {
                throw $e;
            }
        }
    }
    
    /**
     * Build column definition SQL
     * 
     * @param array $column
     * @return string
     */
    private function buildColumnDefinition(array $column): string
    {
        $definition = "`{$column['name']}` {$column['type']}";
        
        if (isset($column['length'])) {
            $definition .= "({$column['length']})";
        }
        
        if (isset($column['precision']) && isset($column['scale'])) {
            $definition .= "({$column['precision']},{$column['scale']})";
        }
        
        if (isset($column['unsigned']) && $column['unsigned']) {
            $definition .= " UNSIGNED";
        }
        
        if (isset($column['nullable']) && !$column['nullable']) {
            $definition .= " NOT NULL";
        }
        
        if (isset($column['auto_increment']) && $column['auto_increment']) {
            $definition .= " AUTO_INCREMENT";
        }
        
        if (isset($column['default'])) {
            if ($column['default'] === 'CURRENT_TIMESTAMP') {
                $definition .= " DEFAULT CURRENT_TIMESTAMP";
            } elseif ($column['default'] === 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP') {
                $definition .= " DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
            } else {
                $definition .= " DEFAULT '{$column['default']}'";
            }
        }
        
        if (isset($column['comment'])) {
            $definition .= " COMMENT '{$column['comment']}'";
        }
        
        return $definition;
    }
    
    /**
     * Insert sample data into a table
     * 
     * @param string $tableName
     * @param array $data
     */
    protected function insertData(string $tableName, array $data): void
    {
        if (empty($data)) {
            return;
        }
        
        $columns = array_keys($data[0]);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $sql = "INSERT INTO `{$tableName}` (`" . implode('`, `', $columns) . "`) VALUES ({$placeholders})";
        
        $insertedCount = 0;
        foreach ($data as $row) {
            try {
                $this->db->executeQuery($sql, array_values($row));
                $insertedCount++;
            } catch (Exception $e) {
                // Skip duplicates
                if (!str_contains($e->getMessage(), 'Duplicate entry')) {
                    throw $e;
                }
            }
        }
        
        if ($insertedCount > 0) {
            echo "  📝 Inserted {$insertedCount} records into {$tableName}\n";
        }
    }
    
    /**
     * Check if table exists
     * 
     * @param string $tableName
     * @return bool
     */
    protected function tableExists(string $tableName): bool
    {
        $result = $this->db->selectOne(
            "SELECT COUNT(*) as count FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?",
            [$tableName]
        );
        
        return $result && $result['count'] > 0;
    }
    
    /**
     * Execute raw SQL
     * 
     * @param string $sql
     * @param array $params
     */
    protected function executeSQL(string $sql, array $params = []): void
    {
        $this->db->executeQuery($sql, $params);
    }
}
?>
