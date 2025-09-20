<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Teachers Table Migration
 * 
 * Creates the teachers table with department relationships
 */
class Migration_2024_01_01_000003_CreateTeachersTable extends Migration
{
    public function up(): void
    {
        $this->createTable('teachers', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'name', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
            ['name' => 'title', 'type' => 'ENUM(\'assistant\',\'chief_assistant\',\'associate_professor\',\'professor\')', 'nullable' => false],
            ['name' => 'phone', 'type' => 'VARCHAR', 'length' => 20],
            ['name' => 'email', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
            ['name' => 'department_id', 'type' => 'INT', 'nullable' => false],
            ['name' => 'hire_date', 'type' => 'DATE', 'nullable' => false],
            ['name' => 'salary', 'type' => 'DECIMAL', 'precision' => 10, 'scale' => 2],
            ['name' => 'status', 'type' => 'ENUM(\'active\',\'inactive\',\'retired\')', 'default' => 'active'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_teachers_email (email)',
            'CONSTRAINT fk_teachers_department FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE RESTRICT ON UPDATE CASCADE'
        ], [
            ['name' => 'idx_teachers_email', 'columns' => 'email'],
            ['name' => 'idx_teachers_department', 'columns' => 'department_id'],
            ['name' => 'idx_teachers_title', 'columns' => 'title'],
            ['name' => 'idx_teachers_status', 'columns' => 'status']
        ]);
        
        // Insert sample data
        $this->insertData('teachers', [
            [
                'name' => 'Prof. Ivan Petrov',
                'title' => 'professor',
                'phone' => '+359888123456',
                'email' => 'i.petrov@university.bg',
                'department_id' => 1,
                'hire_date' => '2010-09-01',
                'salary' => 3500.00,
                'status' => 'active'
            ],
            [
                'name' => 'Doc. Maria Georgieva',
                'title' => 'associate_professor',
                'phone' => '+359888234567',
                'email' => 'm.georgieva@university.bg',
                'department_id' => 1,
                'hire_date' => '2015-02-15',
                'salary' => 2800.00,
                'status' => 'active'
            ],
            [
                'name' => 'As. Stoyan Dimitrov',
                'title' => 'assistant',
                'phone' => '+359888345678',
                'email' => 's.dimitrov@university.bg',
                'department_id' => 2,
                'hire_date' => '2020-09-01',
                'salary' => 2200.00,
                'status' => 'active'
            ],
            [
                'name' => 'Prof. Elena Nikolova',
                'title' => 'professor',
                'phone' => '+359888456789',
                'email' => 'e.nikolova@university.bg',
                'department_id' => 3,
                'hire_date' => '2008-03-10',
                'salary' => 3600.00,
                'status' => 'active'
            ],
            [
                'name' => 'Doc. Georgi Stoyanov',
                'title' => 'associate_professor',
                'phone' => '+359888567890',
                'email' => 'g.stoyanov@university.bg',
                'department_id' => 4,
                'hire_date' => '2012-09-01',
                'salary' => 2900.00,
                'status' => 'active'
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('teachers');
    }
    
    public function getName(): string
    {
        return 'Create Teachers Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the teachers table with department relationships';
    }
}
?>
