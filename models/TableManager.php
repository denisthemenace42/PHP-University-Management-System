<?php

require_once __DIR__ . '/../Database.php';

/**
 * Generic Table Manager
 * 
 * Provides CRUD operations for any database table with automatic
 * form generation and data validation.
 * 
 * @author University System
 * @version 1.0
 */
class TableManager
{
    private $db;
    private $tableName;
    private $primaryKey;
    private $tableStructure;
    private $displayName;
    
    // Tables that should not be directly managed
    private const PROTECTED_TABLES = [
        'migrations',
        'grade_history',
        'student_details',
        'teacher_details', 
        'discipline_details',
        'grade_report'
    ];
    
    // User-friendly table names
    private const TABLE_DISPLAY_NAMES = [
        'departments' => 'Катедри',
        'specialties' => 'Специалности',
        'teachers' => 'Преподаватели',
        'students' => 'Студенти',
        'disciplines' => 'Дисциплини',
        'grades' => 'Оценки',
        'users' => 'Потребители'
    ];
    
    public function __construct(string $tableName)
    {
        $this->db = Database::getInstance();
        $this->tableName = $tableName;
        $this->displayName = self::TABLE_DISPLAY_NAMES[$tableName] ?? ucfirst($tableName);
        
        if (in_array($tableName, self::PROTECTED_TABLES)) {
            throw new Exception("Таблицата '{$tableName}' не може да бъде редактирана директно.");
        }
        
        $this->loadTableStructure();
        $this->findPrimaryKey();
    }
    
    /**
     * Load table structure from database
     */
    private function loadTableStructure(): void
    {
        $this->tableStructure = $this->db->getTableStructure($this->tableName);
        
        if (empty($this->tableStructure)) {
            throw new Exception("Таблицата '{$this->tableName}' не съществува.");
        }
    }
    
    /**
     * Find the primary key column
     */
    private function findPrimaryKey(): void
    {
        foreach ($this->tableStructure as $column) {
            if ($column['Key'] === 'PRI') {
                $this->primaryKey = $column['Field'];
                return;
            }
        }
        
        throw new Exception("Не е намерен primary key за таблица '{$this->tableName}'.");
    }
    
    /**
     * Get all records from table
     */
    public function getAllRecords(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT * FROM `{$this->tableName}` ORDER BY `{$this->primaryKey}` DESC LIMIT ? OFFSET ?";
        return $this->db->select($sql, [$perPage, $offset]);
    }
    
    /**
     * Get total count of records
     */
    public function getTotalCount(): int
    {
        $result = $this->db->selectOne("SELECT COUNT(*) as count FROM `{$this->tableName}`");
        return (int)($result['count'] ?? 0);
    }
    
    /**
     * Get a single record by ID
     */
    public function getRecord($id): ?array
    {
        $sql = "SELECT * FROM `{$this->tableName}` WHERE `{$this->primaryKey}` = ?";
        return $this->db->selectOne($sql, [$id]) ?: null;
    }
    
    /**
     * Create a new record
     */
    public function createRecord(array $data): bool
    {
        // Remove primary key from data if it's auto-increment
        if (isset($data[$this->primaryKey]) && $this->isAutoIncrement($this->primaryKey)) {
            unset($data[$this->primaryKey]);
        }
        
        // Filter only valid columns
        $data = $this->filterValidColumns($data);
        
        if (empty($data)) {
            throw new Exception("Няма валидни данни за записване.");
        }
        
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        
        $sql = "INSERT INTO `{$this->tableName}` (`" . implode('`, `', $columns) . "`) VALUES ({$placeholders})";
        
        try {
            $this->db->executeQuery($sql, array_values($data));
            return true;
        } catch (Exception $e) {
            throw new Exception("Грешка при създаване на запис: " . $e->getMessage());
        }
    }
    
    /**
     * Update an existing record
     */
    public function updateRecord($id, array $data): bool
    {
        // Remove primary key from data
        unset($data[$this->primaryKey]);
        
        // Filter only valid columns
        $data = $this->filterValidColumns($data);
        
        if (empty($data)) {
            throw new Exception("Няма валидни данни за обновяване.");
        }
        
        $setParts = [];
        $values = [];
        
        foreach ($data as $column => $value) {
            $setParts[] = "`{$column}` = ?";
            $values[] = $value;
        }
        
        $values[] = $id;
        
        $sql = "UPDATE `{$this->tableName}` SET " . implode(', ', $setParts) . " WHERE `{$this->primaryKey}` = ?";
        
        try {
            $affectedRows = $this->db->update($sql, $values);
            return $affectedRows > 0;
        } catch (Exception $e) {
            throw new Exception("Грешка при обновяване на запис: " . $e->getMessage());
        }
    }
    
    /**
     * Delete a record
     */
    public function deleteRecord($id): bool
    {
        $sql = "DELETE FROM `{$this->tableName}` WHERE `{$this->primaryKey}` = ?";
        
        try {
            $affectedRows = $this->db->delete($sql, [$id]);
            return $affectedRows > 0;
        } catch (Exception $e) {
            throw new Exception("Грешка при изтриване на запис: " . $e->getMessage());
        }
    }
    
    /**
     * Get foreign key options for a column
     */
    public function getForeignKeyOptions(string $column): array
    {
        // Common foreign key mappings
        $foreignKeys = [
            'specialty_id' => ['table' => 'specialties', 'display' => 'name', 'value' => 'id'],
            'department_id' => ['table' => 'departments', 'display' => 'name', 'value' => 'id'],
            'teacher_id' => ['table' => 'teachers', 'display' => 'name', 'value' => 'id'],
            'student_id' => ['table' => 'students', 'display' => 'CONCAT(first_name, " ", last_name)', 'value' => 'id'],
            'discipline_id' => ['table' => 'disciplines', 'display' => 'name', 'value' => 'id']
        ];
        
        if (!isset($foreignKeys[$column])) {
            return [];
        }
        
        $fk = $foreignKeys[$column];
        $sql = "SELECT `{$fk['value']}` as value, {$fk['display']} as display FROM `{$fk['table']}` ORDER BY {$fk['display']}";
        
        try {
            return $this->db->select($sql);
        } catch (Exception $e) {
            return [];
        }
    }
    
    /**
     * Filter data to only include valid table columns
     */
    private function filterValidColumns(array $data): array
    {
        $validColumns = array_column($this->tableStructure, 'Field');
        
        return array_intersect_key($data, array_flip($validColumns));
    }
    
    /**
     * Check if column is auto-increment
     */
    private function isAutoIncrement(string $column): bool
    {
        foreach ($this->tableStructure as $col) {
            if ($col['Field'] === $column) {
                return strpos($col['Extra'], 'auto_increment') !== false;
            }
        }
        return false;
    }
    
    /**
     * Get table structure for form generation
     */
    public function getTableStructure(): array
    {
        return $this->tableStructure;
    }
    
    /**
     * Get table name
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }
    
    /**
     * Get display name
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }
    
    /**
     * Get primary key column name
     */
    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * Get all manageable tables
     */
    public static function getManageableTables(): array
    {
        $db = Database::getInstance();
        $allTables = $db->getAllTables();
        
        // Filter out protected tables
        $manageableTables = array_diff($allTables, self::PROTECTED_TABLES);
        
        // Return with display names
        $result = [];
        foreach ($manageableTables as $table) {
            $result[$table] = self::TABLE_DISPLAY_NAMES[$table] ?? ucfirst($table);
        }
        
        return $result;
    }
    
    /**
     * Generate HTML form field for a column
     */
    public function generateFormField(array $column, $value = null): string
    {
        $field = $column['Field'];
        $type = $column['Type'];
        $isNull = $column['Null'] === 'YES';
        $default = $column['Default'];
        $extra = $column['Extra'];
        
        $html = '';
        $required = !$isNull && $default === null ? 'required' : '';
        $value = $value ?? $default ?? '';
        
        // Skip auto-increment primary keys
        if (strpos($extra, 'auto_increment') !== false) {
            return '';
        }
        
        // Generate label
        $label = ucfirst(str_replace('_', ' ', $field));
        $html .= "<div class=\"mb-3\">\n";
        $html .= "<label for=\"{$field}\" class=\"form-label\">{$label}</label>\n";
        
        // Handle different field types
        if (strpos($type, 'enum') !== false) {
            // ENUM field - dropdown
            preg_match_all("/'([^']+)'/", $type, $matches);
            $options = $matches[1];
            
            $html .= "<select class=\"form-select\" name=\"{$field}\" id=\"{$field}\" {$required}>\n";
            if (!$required) {
                $html .= "<option value=\"\">-- Избери --</option>\n";
            }
            foreach ($options as $option) {
                $selected = ($value === $option) ? 'selected' : '';
                $html .= "<option value=\"{$option}\" {$selected}>" . ucfirst($option) . "</option>\n";
            }
            $html .= "</select>\n";
            
        } elseif (strpos($field, '_id') !== false && $field !== 'id') {
            // Foreign key - dropdown
            $options = $this->getForeignKeyOptions($field);
            
            $html .= "<select class=\"form-select\" name=\"{$field}\" id=\"{$field}\" {$required}>\n";
            if (!$required) {
                $html .= "<option value=\"\">-- Избери --</option>\n";
            }
            foreach ($options as $option) {
                $selected = ($value == $option['value']) ? 'selected' : '';
                $html .= "<option value=\"{$option['value']}\" {$selected}>{$option['display']}</option>\n";
            }
            $html .= "</select>\n";
            
        } elseif (strpos($type, 'text') !== false) {
            // TEXT field - textarea
            $html .= "<textarea class=\"form-control\" name=\"{$field}\" id=\"{$field}\" rows=\"3\" {$required}>" . htmlspecialchars($value) . "</textarea>\n";
            
        } elseif (strpos($type, 'date') !== false) {
            // DATE field
            $inputType = strpos($type, 'datetime') !== false || strpos($type, 'timestamp') !== false ? 'datetime-local' : 'date';
            if ($inputType === 'datetime-local' && $value && $value !== '0000-00-00 00:00:00') {
                $value = date('Y-m-d\TH:i', strtotime($value));
            }
            $html .= "<input type=\"{$inputType}\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"" . htmlspecialchars($value) . "\" {$required}>\n";
            
        } elseif (strpos($type, 'decimal') !== false || strpos($type, 'float') !== false || strpos($type, 'double') !== false) {
            // Numeric field with decimals
            $html .= "<input type=\"number\" step=\"0.01\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"" . htmlspecialchars($value) . "\" {$required}>\n";
            
        } elseif (strpos($type, 'int') !== false || strpos($type, 'tinyint') !== false) {
            // Integer field
            $html .= "<input type=\"number\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"" . htmlspecialchars($value) . "\" {$required}>\n";
            
        } elseif ($field === 'email') {
            // Email field
            $html .= "<input type=\"email\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"" . htmlspecialchars($value) . "\" {$required}>\n";
            
        } elseif ($field === 'phone') {
            // Phone field
            $html .= "<input type=\"tel\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"" . htmlspecialchars($value) . "\" {$required}>\n";
            
        } elseif ($field === 'password') {
            // Password field
            $html .= "<input type=\"password\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"\" placeholder=\"Оставете празно за запазване на старата парола\">\n";
            
        } else {
            // Default VARCHAR/text input
            $maxLength = '';
            if (preg_match('/varchar\((\d+)\)/', $type, $matches)) {
                $maxLength = "maxlength=\"{$matches[1]}\"";
            }
            $html .= "<input type=\"text\" class=\"form-control\" name=\"{$field}\" id=\"{$field}\" value=\"" . htmlspecialchars($value) . "\" {$required} {$maxLength}>\n";
        }
        
        $html .= "</div>\n";
        
        return $html;
    }
}
?>
