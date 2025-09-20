<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Disciplines Table Migration
 * 
 * Creates the disciplines table with teacher relationships
 */
class Migration_2024_01_01_000005_CreateDisciplinesTable extends Migration
{
    public function up(): void
    {
        $this->createTable('disciplines', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'name', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
            ['name' => 'code', 'type' => 'VARCHAR', 'length' => 20, 'nullable' => false],
            ['name' => 'semester', 'type' => 'TINYINT', 'nullable' => false],
            ['name' => 'teacher_id', 'type' => 'INT', 'nullable' => false],
            ['name' => 'credits', 'type' => 'TINYINT', 'nullable' => false],
            ['name' => 'hours_per_week', 'type' => 'TINYINT', 'default' => '2'],
            ['name' => 'type', 'type' => 'ENUM(\'mandatory\',\'elective\',\'optional\')', 'default' => 'mandatory'],
            ['name' => 'description', 'type' => 'TEXT'],
            ['name' => 'prerequisites', 'type' => 'TEXT'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_disciplines_code (code)',
            'CONSTRAINT fk_disciplines_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE RESTRICT ON UPDATE CASCADE',
            'CHECK (semester BETWEEN 1 AND 12)',
            'CHECK (credits BETWEEN 1 AND 10)'
        ], [
            ['name' => 'idx_disciplines_code', 'columns' => 'code'],
            ['name' => 'idx_disciplines_semester', 'columns' => 'semester'],
            ['name' => 'idx_disciplines_teacher', 'columns' => 'teacher_id'],
            ['name' => 'idx_disciplines_type', 'columns' => 'type']
        ]);
        
        // Insert sample data
        $this->insertData('disciplines', [
            [
                'name' => 'Programming Fundamentals',
                'code' => 'CS101',
                'semester' => 1,
                'teacher_id' => 1,
                'credits' => 6,
                'hours_per_week' => 4,
                'type' => 'mandatory',
                'description' => 'Introduction to programming concepts and algorithms'
            ],
            [
                'name' => 'Data Structures and Algorithms',
                'code' => 'CS201',
                'semester' => 3,
                'teacher_id' => 1,
                'credits' => 5,
                'hours_per_week' => 3,
                'type' => 'mandatory',
                'description' => 'Advanced data structures and algorithmic thinking'
            ],
            [
                'name' => 'Database Systems',
                'code' => 'CS301',
                'semester' => 5,
                'teacher_id' => 2,
                'credits' => 4,
                'hours_per_week' => 3,
                'type' => 'mandatory',
                'description' => 'Relational databases and SQL'
            ],
            [
                'name' => 'Linear Algebra',
                'code' => 'MA101',
                'semester' => 2,
                'teacher_id' => 3,
                'credits' => 4,
                'hours_per_week' => 3,
                'type' => 'mandatory',
                'description' => 'Vectors, matrices and linear transformations'
            ],
            [
                'name' => 'Physics I',
                'code' => 'PH101',
                'semester' => 1,
                'teacher_id' => 4,
                'credits' => 5,
                'hours_per_week' => 4,
                'type' => 'mandatory',
                'description' => 'Classical mechanics and thermodynamics'
            ],
            [
                'name' => 'Software Engineering',
                'code' => 'SE401',
                'semester' => 7,
                'teacher_id' => 2,
                'credits' => 6,
                'hours_per_week' => 4,
                'type' => 'mandatory',
                'description' => 'Software development methodologies and practices'
            ],
            [
                'name' => 'Web Development',
                'code' => 'CS302',
                'semester' => 6,
                'teacher_id' => 1,
                'credits' => 3,
                'hours_per_week' => 2,
                'type' => 'elective',
                'description' => 'Modern web technologies and frameworks'
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('disciplines');
    }
    
    public function getName(): string
    {
        return 'Create Disciplines Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the disciplines table with teacher relationships';
    }
}
?>
