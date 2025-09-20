<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Students Table Migration
 * 
 * Creates the students table with specialty relationships
 */
class Migration_2024_01_01_000004_CreateStudentsTable extends Migration
{
    public function up(): void
    {
        $this->createTable('students', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'faculty_number', 'type' => 'VARCHAR', 'length' => 20, 'nullable' => false],
            ['name' => 'first_name', 'type' => 'VARCHAR', 'length' => 50, 'nullable' => false],
            ['name' => 'middle_name', 'type' => 'VARCHAR', 'length' => 50],
            ['name' => 'last_name', 'type' => 'VARCHAR', 'length' => 50, 'nullable' => false],
            ['name' => 'specialty_id', 'type' => 'INT', 'nullable' => false],
            ['name' => 'course', 'type' => 'TINYINT', 'nullable' => false],
            ['name' => 'email', 'type' => 'VARCHAR', 'length' => 100, 'nullable' => false],
            ['name' => 'address', 'type' => 'TEXT'],
            ['name' => 'phone', 'type' => 'VARCHAR', 'length' => 20],
            ['name' => 'birth_date', 'type' => 'DATE'],
            ['name' => 'enrollment_date', 'type' => 'DATE', 'nullable' => false],
            ['name' => 'status', 'type' => 'ENUM(\'active\',\'inactive\',\'graduated\',\'expelled\')', 'default' => 'active'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_students_faculty_number (faculty_number)',
            'UNIQUE KEY uk_students_email (email)',
            'CONSTRAINT fk_students_specialty FOREIGN KEY (specialty_id) REFERENCES specialties(id) ON DELETE RESTRICT ON UPDATE CASCADE',
            'CHECK (course BETWEEN 1 AND 6)'
        ], [
            ['name' => 'idx_students_faculty_number', 'columns' => 'faculty_number'],
            ['name' => 'idx_students_email', 'columns' => 'email'],
            ['name' => 'idx_students_specialty_course', 'columns' => 'specialty_id, course'],
            ['name' => 'idx_students_status', 'columns' => 'status']
        ]);
        
        // Insert sample data
        $this->insertData('students', [
            [
                'faculty_number' => '121220001',
                'first_name' => 'Александър',
                'middle_name' => 'Петров',
                'last_name' => 'Иванов',
                'specialty_id' => 1,
                'course' => 3,
                'email' => 'a.ivanov@student.university.bg',
                'address' => 'ул. Витоша 15, София',
                'phone' => '+359887123456',
                'birth_date' => '2002-05-15',
                'enrollment_date' => '2020-09-15',
                'status' => 'active'
            ],
            [
                'faculty_number' => '121220002',
                'first_name' => 'Мария',
                'middle_name' => 'Георгиева',
                'last_name' => 'Петрова',
                'specialty_id' => 2,
                'course' => 2,
                'email' => 'm.petrova@student.university.bg',
                'address' => 'бул. България 25, Пловдив',
                'phone' => '+359887234567',
                'birth_date' => '2003-03-22',
                'enrollment_date' => '2021-09-15',
                'status' => 'active'
            ],
            [
                'faculty_number' => '121220003',
                'first_name' => 'Стоян',
                'middle_name' => 'Димитров',
                'last_name' => 'Николов',
                'specialty_id' => 1,
                'course' => 4,
                'email' => 's.nikolov@student.university.bg',
                'address' => 'ул. Раковски 8, Варна',
                'phone' => '+359887345678',
                'birth_date' => '2001-11-08',
                'enrollment_date' => '2019-09-15',
                'status' => 'active'
            ],
            [
                'faculty_number' => '121220004',
                'first_name' => 'Елена',
                'middle_name' => 'Стоянова',
                'last_name' => 'Димитрова',
                'specialty_id' => 3,
                'course' => 1,
                'email' => 'e.dimitrova@student.university.bg',
                'address' => 'ул. Шипка 12, Бургас',
                'phone' => '+359887456789',
                'birth_date' => '2004-07-30',
                'enrollment_date' => '2022-09-15',
                'status' => 'active'
            ],
            [
                'faculty_number' => '121220005',
                'first_name' => 'Георги',
                'middle_name' => 'Николов',
                'last_name' => 'Стоянов',
                'specialty_id' => 2,
                'course' => 3,
                'email' => 'g.stoyanov@student.university.bg',
                'address' => 'бул. Левски 33, Русе',
                'phone' => '+359887567890',
                'birth_date' => '2002-12-12',
                'enrollment_date' => '2020-09-15',
                'status' => 'active'
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('students');
    }
    
    public function getName(): string
    {
        return 'Create Students Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the students table with specialty relationships';
    }
}
?>
