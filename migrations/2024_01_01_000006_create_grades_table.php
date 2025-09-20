<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Grades Table Migration
 * 
 * Creates the grades table with student and discipline relationships
 */
class Migration_2024_01_01_000006_CreateGradesTable extends Migration
{
    public function up(): void
    {
        $this->createTable('grades', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'student_id', 'type' => 'INT', 'nullable' => false],
            ['name' => 'discipline_id', 'type' => 'INT', 'nullable' => false],
            ['name' => 'grade', 'type' => 'DECIMAL', 'precision' => 3, 'scale' => 2, 'nullable' => false],
            ['name' => 'date', 'type' => 'DATE', 'nullable' => false],
            ['name' => 'exam_type', 'type' => 'ENUM(\'written\',\'oral\',\'practical\',\'project\',\'continuous_assessment\')', 'default' => 'written'],
            ['name' => 'notes', 'type' => 'TEXT'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_student_discipline (student_id, discipline_id)',
            'CONSTRAINT fk_grades_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE ON UPDATE CASCADE',
            'CONSTRAINT fk_grades_discipline FOREIGN KEY (discipline_id) REFERENCES disciplines(id) ON DELETE CASCADE ON UPDATE CASCADE',
            'CHECK (grade BETWEEN 2.00 AND 6.00)'
        ], [
            ['name' => 'idx_grades_student', 'columns' => 'student_id'],
            ['name' => 'idx_grades_discipline', 'columns' => 'discipline_id'],
            ['name' => 'idx_grades_date', 'columns' => 'date'],
            ['name' => 'idx_grades_grade', 'columns' => 'grade']
        ]);
        
        // Insert sample data
        $this->insertData('grades', [
            [
                'student_id' => 1,
                'discipline_id' => 1,
                'grade' => 5.50,
                'date' => '2021-01-20',
                'exam_type' => 'written',
                'notes' => 'Excellent understanding of programming concepts'
            ],
            [
                'student_id' => 1,
                'discipline_id' => 2,
                'grade' => 4.75,
                'date' => '2022-01-15',
                'exam_type' => 'written',
                'notes' => 'Good algorithmic thinking'
            ],
            [
                'student_id' => 1,
                'discipline_id' => 3,
                'grade' => 5.25,
                'date' => '2022-06-10',
                'exam_type' => 'practical',
                'notes' => 'Strong database design skills'
            ],
            [
                'student_id' => 2,
                'discipline_id' => 1,
                'grade' => 4.25,
                'date' => '2022-01-20',
                'exam_type' => 'written',
                'notes' => 'Good basic programming skills'
            ],
            [
                'student_id' => 2,
                'discipline_id' => 4,
                'grade' => 5.00,
                'date' => '2022-06-15',
                'exam_type' => 'written',
                'notes' => 'Solid mathematical foundation'
            ],
            [
                'student_id' => 3,
                'discipline_id' => 1,
                'grade' => 5.75,
                'date' => '2020-01-20',
                'exam_type' => 'written',
                'notes' => 'Outstanding programming abilities'
            ],
            [
                'student_id' => 3,
                'discipline_id' => 2,
                'grade' => 5.50,
                'date' => '2021-01-15',
                'exam_type' => 'written',
                'notes' => 'Excellent algorithmic problem solving'
            ],
            [
                'student_id' => 3,
                'discipline_id' => 3,
                'grade' => 5.00,
                'date' => '2021-06-10',
                'exam_type' => 'practical',
                'notes' => 'Good database implementation'
            ],
            [
                'student_id' => 3,
                'discipline_id' => 6,
                'grade' => 4.50,
                'date' => '2022-06-20',
                'exam_type' => 'project',
                'notes' => 'Well-structured software project'
            ],
            [
                'student_id' => 4,
                'discipline_id' => 1,
                'grade' => 3.50,
                'date' => '2023-01-20',
                'exam_type' => 'written',
                'notes' => 'Needs improvement in programming logic'
            ],
            [
                'student_id' => 5,
                'discipline_id' => 1,
                'grade' => 4.00,
                'date' => '2021-01-20',
                'exam_type' => 'written',
                'notes' => 'Adequate programming skills'
            ],
            [
                'student_id' => 5,
                'discipline_id' => 4,
                'grade' => 4.75,
                'date' => '2021-06-15',
                'exam_type' => 'written',
                'notes' => 'Strong mathematical abilities'
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('grades');
    }
    
    public function getName(): string
    {
        return 'Create Grades Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the grades table with student and discipline relationships';
    }
}
?>
