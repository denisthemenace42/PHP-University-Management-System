<?php
require_once __DIR__ . '/../Database.php';
class User
{
    private $db;
    private $table = 'users';
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    public function authenticate(string $username, string $password)
    {
        try {
            if (empty($username) || empty($password)) {
                return false;
            }
            $sql = "SELECT 
                u.*,
                s.id as student_record_id,
                s.faculty_number,
                s.course,
                sp.name as specialty_name,
                t.id as teacher_record_id,
                t.name as teacher_name,
                t.title as teacher_title,
                t.department_id,
                d.name as department_name
            FROM {$this->table} u
            LEFT JOIN students s ON u.student_id = s.id
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN teachers t ON u.teacher_id = t.id
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE u.username = ?";
            $user = $this->db->selectOne($sql, [$username]);
            if ($user && password_verify($password, $user['password'])) {
                unset($user['password']);
                $user['id'] = (int)$user['id'];
                $user['student_id'] = $user['student_id'] ? (int)$user['student_id'] : null;
                $user['teacher_id'] = $user['teacher_id'] ? (int)$user['teacher_id'] : null;
                return $user;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error authenticating user: " . $e->getMessage());
            return false;
        }
    }
    public function getUserById(int $id)
    {
        try {
            $sql = "SELECT 
                u.*,
                s.id as student_record_id,
                s.faculty_number,
                s.course,
                sp.name as specialty_name,
                t.id as teacher_record_id,
                t.name as teacher_name,
                t.title as teacher_title,
                t.department_id,
                d.name as department_name
            FROM {$this->table} u
            LEFT JOIN students s ON u.student_id = s.id
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN teachers t ON u.teacher_id = t.id
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE u.id = ?";
            $user = $this->db->selectOne($sql, [$id]);
            if ($user) {
                unset($user['password']);
                $user['id'] = (int)$user['id'];
                $user['student_id'] = $user['student_id'] ? (int)$user['student_id'] : null;
                $user['teacher_id'] = $user['teacher_id'] ? (int)$user['teacher_id'] : null;
                return $user;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error getting user by ID: " . $e->getMessage());
            return false;
        }
    }
    public function getUserByUsername(string $username)
    {
        try {
            $sql = "SELECT 
                u.*,
                s.id as student_record_id,
                s.faculty_number,
                s.course,
                sp.name as specialty_name,
                t.id as teacher_record_id,
                t.name as teacher_name,
                t.title as teacher_title,
                t.department_id,
                d.name as department_name
            FROM {$this->table} u
            LEFT JOIN students s ON u.student_id = s.id
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN teachers t ON u.teacher_id = t.id
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE u.username = ?";
            $user = $this->db->selectOne($sql, [$username]);
            if ($user) {
                unset($user['password']);
                $user['id'] = (int)$user['id'];
                $user['student_id'] = $user['student_id'] ? (int)$user['student_id'] : null;
                $user['teacher_id'] = $user['teacher_id'] ? (int)$user['teacher_id'] : null;
                return $user;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error getting user by username: " . $e->getMessage());
            return false;
        }
    }
    public function createUser(array $data)
    {
        try {
            $requiredFields = ['username', 'password', 'role'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new InvalidArgumentException("Required field '$field' is missing.");
                }
            }
            if ($this->getUserByUsername($data['username'])) {
                throw new InvalidArgumentException("Username already exists.");
            }
            // Hash the password before storing
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $sql = "INSERT INTO {$this->table} (
                username, password, role, first_name, last_name, 
                email, phone, student_id, teacher_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['username'],
                $hashedPassword,
                $data['role'],
                $data['first_name'] ?? null,
                $data['last_name'] ?? null,
                $data['email'] ?? null,
                $data['phone'] ?? null,
                $data['student_id'] ?? null,
                $data['teacher_id'] ?? null
            ];
            $userId = $this->db->insert($sql, $params);
            if ($userId) {
                error_log("User created successfully with ID: $userId");
                return (int)$userId;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }
    public function updateUser(int $id, array $data): bool
    {
        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Invalid user ID.");
            }
            if (!$this->getUserById($id)) {
                throw new InvalidArgumentException("User not found.");
            }
            $allowedFields = [
                'username', 'password', 'role', 'first_name', 'last_name',
                'email', 'phone', 'student_id', 'teacher_id'
            ];
            $updateFields = [];
            $params = [];
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updateFields[] = "$field = ?";
                    // Hash password if it's being updated
                    if ($field === 'password' && !empty($value)) {
                        $params[] = password_hash($value, PASSWORD_DEFAULT);
                    } else {
                        $params[] = $value;
                    }
                }
            }
            if (empty($updateFields)) {
                throw new InvalidArgumentException("No valid fields provided for update.");
            }
            $params[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $affectedRows = $this->db->update($sql, $params);
            if ($affectedRows > 0) {
                error_log("User updated successfully. ID: $id");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating user: " . $e->getMessage());
            return false;
        }
    }
    public function deleteUser(int $id): bool
    {
        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Invalid user ID.");
            }
            if (!$this->getUserById($id)) {
                throw new InvalidArgumentException("User not found.");
            }
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $affectedRows = $this->db->delete($sql, [$id]);
            if ($affectedRows > 0) {
                error_log("User deleted successfully. ID: $id");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting user: " . $e->getMessage());
            return false;
        }
    }
    public function getAllUsers(array $filters = []): array
    {
        try {
            $sql = "SELECT 
                u.id,
                u.username,
                u.role,
                u.first_name,
                u.last_name,
                u.email,
                u.phone,
                u.created_at,
                s.faculty_number,
                sp.name as specialty_name,
                t.name as teacher_name,
                t.title as teacher_title,
                d.name as department_name
            FROM {$this->table} u
            LEFT JOIN students s ON u.student_id = s.id
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN teachers t ON u.teacher_id = t.id
            LEFT JOIN departments d ON t.department_id = d.id";
            $whereConditions = [];
            $params = [];
            if (!empty($filters['role'])) {
                $whereConditions[] = "u.role = ?";
                $params[] = $filters['role'];
            }
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $whereConditions[] = "(u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
            }
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            $sql .= " ORDER BY u.role, u.username";
            $users = $this->db->select($sql, $params);
            foreach ($users as &$user) {
                $user['id'] = (int)$user['id'];
            }
            return $users;
        } catch (Exception $e) {
            error_log("Error getting all users: " . $e->getMessage());
            return [];
        }
    }
    public static function hasPermission(string $role, string $action): bool
    {
        $permissions = [
            'admin' => [
                'view_dashboard',
                'manage_students',
                'manage_teachers',
                'manage_disciplines',
                'manage_grades',
                'manage_users',
                'view_all_grades',
                'create_student',
                'edit_student',
                'delete_student',
                'create_teacher',
                'edit_teacher',
                'delete_teacher',
                'create_discipline',
                'edit_discipline',
                'delete_discipline',
                'create_grade',
                'edit_grade',
                'delete_grade',
                'view_reports'
            ],
            'teacher' => [
                'view_dashboard',
                'manage_grades',
                'view_students',
                'view_disciplines',
                'create_grade',
                'edit_grade',
                'view_my_students',
                'view_my_grades'
            ],
            'student' => [
                'view_dashboard',
                'view_my_profile',
                'edit_my_profile',
                'view_my_grades',
                'view_my_disciplines'
            ]
        ];
        return isset($permissions[$role]) && in_array($action, $permissions[$role]);
    }
    public function getUserStatistics(): array
    {
        try {
            $stats = [];
            $totalUsers = $this->db->selectOne("SELECT COUNT(*) as total FROM {$this->table}");
            $stats['total_users'] = (int)$totalUsers['total'];
            $roleStats = $this->db->select(
                "SELECT role, COUNT(*) as count FROM {$this->table} GROUP BY role ORDER BY role"
            );
            $stats['by_role'] = $roleStats;
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting user statistics: " . $e->getMessage());
            return [];
        }
    }
}
if (basename($_SERVER['PHP_SELF']) === 'User.php') {
    echo "=== User Model Test ===\n";
    try {
        $user = new User();
        echo "\n--- Testing Authentication ---\n";
        $adminUser = $user->authenticate('admin', 'admin');
        if ($adminUser) {
            echo "✅ Admin login successful: {$adminUser['first_name']} {$adminUser['last_name']} ({$adminUser['role']})\n";
        } else {
            echo "❌ Admin login failed\n";
        }
        $studentUser = $user->authenticate('student', 'student');
        if ($studentUser) {
            echo "✅ Student login successful: {$studentUser['first_name']} {$studentUser['last_name']} ({$studentUser['role']})\n";
            if ($studentUser['student_record_id']) {
                echo "   Student ID: {$studentUser['student_record_id']}, Faculty Number: {$studentUser['faculty_number']}\n";
            }
        } else {
            echo "❌ Student login failed\n";
        }
        $teacherUser = $user->authenticate('teacher', 'teacher');
        if ($teacherUser) {
            echo "✅ Teacher login successful: {$teacherUser['first_name']} {$teacherUser['last_name']} ({$teacherUser['role']})\n";
            if ($teacherUser['teacher_record_id']) {
                echo "   Teacher ID: {$teacherUser['teacher_record_id']}, Department: {$teacherUser['department_name']}\n";
            }
        } else {
            echo "❌ Teacher login failed\n";
        }
        echo "\n--- Testing Permissions ---\n";
        echo "Admin can manage students: " . (User::hasPermission('admin', 'manage_students') ? 'Yes' : 'No') . "\n";
        echo "Student can manage students: " . (User::hasPermission('student', 'manage_students') ? 'Yes' : 'No') . "\n";
        echo "Teacher can create grades: " . (User::hasPermission('teacher', 'create_grade') ? 'Yes' : 'No') . "\n";
        echo "Student can view own grades: " . (User::hasPermission('student', 'view_my_grades') ? 'Yes' : 'No') . "\n";
        echo "\n--- Testing Statistics ---\n";
        $stats = $user->getUserStatistics();
        echo "📊 Total users: " . $stats['total_users'] . "\n";
        echo "📊 Users by role:\n";
        foreach ($stats['by_role'] as $roleStat) {
            echo "   {$roleStat['role']}: {$roleStat['count']} users\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n=== Test Complete ===\n";
}
?>
