<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Specialties Table Migration
 * 
 * Creates the specialties table for student specializations
 */
class Migration_2024_01_01_000002_CreateSpecialtiesTable extends Migration
{
    public function up(): void
    {
        $this->createTable('specialties', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'name', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
            ['name' => 'code', 'type' => 'VARCHAR', 'length' => 20, 'nullable' => false],
            ['name' => 'description', 'type' => 'TEXT'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_specialties_name (name)',
            'UNIQUE KEY uk_specialties_code (code)'
        ]);
        
        // Insert sample data
        $this->insertData('specialties', [
            [
                'name' => 'Software Engineering',
                'code' => 'SE',
                'description' => 'Software development and engineering'
            ],
            [
                'name' => 'Computer Science',
                'code' => 'CS',
                'description' => 'Computer science and algorithms'
            ],
            [
                'name' => 'Information Systems',
                'code' => 'IS',
                'description' => 'Information systems and databases'
            ],
            [
                'name' => 'Applied Mathematics',
                'code' => 'AM',
                'description' => 'Applied mathematics and statistics'
            ],
            [
                'name' => 'Physics',
                'code' => 'PH',
                'description' => 'Theoretical and applied physics'
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('specialties');
    }
    
    public function getName(): string
    {
        return 'Create Specialties Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the specialties table for student specializations';
    }
}
?>
