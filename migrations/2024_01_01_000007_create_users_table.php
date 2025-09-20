<?php

require_once __DIR__ . '/Migration.php';

/**
 * Create Users Table Migration
 * 
 * Creates the users table for authentication with student and teacher relationships
 */
class Migration_2024_01_01_000007_CreateUsersTable extends Migration
{
    public function up(): void
    {
        $this->createTable('users', [
            ['name' => 'id', 'type' => 'INT', 'auto_increment' => true, 'nullable' => false],
            ['name' => 'username', 'type' => 'VARCHAR', 'length' => 50, 'nullable' => false],
            ['name' => 'password', 'type' => 'VARCHAR', 'length' => 255, 'nullable' => false],
            ['name' => 'role', 'type' => 'ENUM(\'admin\',\'teacher\',\'student\')', 'nullable' => false],
            ['name' => 'first_name', 'type' => 'VARCHAR', 'length' => 50],
            ['name' => 'last_name', 'type' => 'VARCHAR', 'length' => 50],
            ['name' => 'email', 'type' => 'VARCHAR', 'length' => 100],
            ['name' => 'phone', 'type' => 'VARCHAR', 'length' => 20],
            ['name' => 'student_id', 'type' => 'INT'],
            ['name' => 'teacher_id', 'type' => 'INT'],
            ['name' => 'created_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP'],
            ['name' => 'updated_at', 'type' => 'TIMESTAMP', 'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP']
        ], [
            'PRIMARY KEY (id)',
            'UNIQUE KEY uk_users_username (username)',
            'CONSTRAINT fk_users_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE SET NULL ON UPDATE CASCADE',
            'CONSTRAINT fk_users_teacher FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL ON UPDATE CASCADE'
        ], [
            ['name' => 'idx_users_username', 'columns' => 'username'],
            ['name' => 'idx_users_role', 'columns' => 'role'],
            ['name' => 'idx_users_email', 'columns' => 'email']
        ]);
        
        // Insert sample users with hashed passwords
        $adminPassword = password_hash('admin', PASSWORD_DEFAULT);
        $teacherPassword = password_hash('teacher', PASSWORD_DEFAULT);
        $studentPassword = password_hash('student', PASSWORD_DEFAULT);
        
        $this->insertData('users', [
            [
                'username' => 'admin',
                'password' => $adminPassword,
                'role' => 'admin',
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@university.bg'
            ],
            [
                'username' => 'teacher',
                'password' => $teacherPassword,
                'role' => 'teacher',
                'first_name' => 'Диян',
                'last_name' => 'Желев Динев',
                'email' => 'teacher@university.bg',
                'teacher_id' => 1
            ],
            [
                'username' => 'student',
                'password' => $studentPassword,
                'role' => 'student',
                'first_name' => 'Денис',
                'last_name' => 'Мехмед',
                'email' => 'student@university.bg',
                'student_id' => 1
            ]
        ]);
    }
    
    public function down(): void
    {
        $this->dropTable('users');
    }
    
    public function getName(): string
    {
        return 'Create Users Table';
    }
    
    public function getDescription(): string
    {
        return 'Creates the users table for authentication with student and teacher relationships';
    }
}
?>
