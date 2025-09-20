<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Departments Table Migration
 * 
 * Creates the departments table for organizing teachers by department
 */
class Migration_2024_01_01_000001_CreateDepartmentsTable extends Migration
{
    public function up(): void
    {
        $this->createTable('departments', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'name', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
            ['name' => 'description', 'type' => 'TEXT'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_departments_name (name)'
        ]);
        
        // Insert sample data
        $this->insertData('departments', [
            [
                'name' => 'Computer Science',
                'description' => 'Department of Computer Science and Information Technologies'
            ],
            [
                'name' => 'Mathematics',
                'description' => 'Department of Mathematics and Statistics'
            ],
            [
                'name' => 'Physics',
                'description' => 'Department of Physics and Astronomy'
            ],
            [
                'name' => 'Engineering',
                'description' => 'Department of Engineering Sciences'
            ],
            [
                'name' => 'Business',
                'description' => 'Department of Business Administration'
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('departments');
    }
    
    public function getName(): string
    {
        return 'Create Departments Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the departments table for organizing teachers by department';
    }
}
?>
