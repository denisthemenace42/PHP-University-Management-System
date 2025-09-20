<?php
require_once __DIR__ . '/../Database.php';
class Student
{
    private $db;
    private $table = 'students';
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    public function createStudent(array $data)
    {
        try {
            $requiredFields = ['faculty_number', 'first_name', 'last_name', 'specialty_id', 'course', 'email', 'enrollment_date'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new InvalidArgumentException("Required field '$field' is missing or empty.");
                }
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email format.");
            }
            if ($data['course'] < 1 || $data['course'] > 6) {
                throw new InvalidArgumentException("Course must be between 1 and 6.");
            }
            $sql = "INSERT INTO {$this->table} (
                faculty_number, 
                first_name, 
                middle_name, 
                last_name, 
                specialty_id, 
                course, 
                email, 
                address, 
                phone, 
                birth_date, 
                enrollment_date, 
                status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $data['faculty_number'],
                $data['first_name'],
                $data['middle_name'] ?? null,
                $data['last_name'],
                $data['specialty_id'],
                $data['course'],
                $data['email'],
                $data['address'] ?? null,
                $data['phone'] ?? null,
                $data['birth_date'] ?? null,
                $data['enrollment_date'],
                $data['status'] ?? 'active'
            ];
            $studentId = $this->db->insert($sql, $params);
            if ($studentId) {
                error_log("Student created successfully with ID: $studentId");
                return (int)$studentId;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error creating student: " . $e->getMessage());
            return false;
        }
    }
    public function getStudentById(int $id)
    {
        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Student ID must be a positive integer.");
            }
            $sql = "SELECT 
                s.*,
                sp.name as specialty_name,
                sp.code as specialty_code
            FROM {$this->table} s
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            WHERE s.id = ?";
            $student = $this->db->selectOne($sql, [$id]);
            if ($student) {
                $student['id'] = (int)$student['id'];
                $student['specialty_id'] = (int)$student['specialty_id'];
                $student['course'] = (int)$student['course'];
                return $student;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error getting student by ID ($id): " . $e->getMessage());
            return false;
        }
    }
    public function getStudentByFacultyNumber(string $facultyNumber)
    {
        try {
            if (empty($facultyNumber)) {
                throw new InvalidArgumentException("Faculty number cannot be empty.");
            }
            $sql = "SELECT 
                s.*,
                sp.name as specialty_name,
                sp.code as specialty_code
            FROM {$this->table} s
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            WHERE s.faculty_number = ?";
            $student = $this->db->selectOne($sql, [$facultyNumber]);
            if ($student) {
                $student['id'] = (int)$student['id'];
                $student['specialty_id'] = (int)$student['specialty_id'];
                $student['course'] = (int)$student['course'];
                return $student;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error getting student by faculty number ($facultyNumber): " . $e->getMessage());
            return false;
        }
    }
    public function updateStudent(int $id, array $data): bool
    {
        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Student ID must be a positive integer.");
            }
            if (empty($data)) {
                throw new InvalidArgumentException("Update data cannot be empty.");
            }
            if (!$this->getStudentById($id)) {
                throw new InvalidArgumentException("Student with ID $id not found.");
            }
            if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new InvalidArgumentException("Invalid email format.");
            }
            if (isset($data['course']) && ($data['course'] < 1 || $data['course'] > 6)) {
                throw new InvalidArgumentException("Course must be between 1 and 6.");
            }
            $allowedFields = [
                'faculty_number', 'first_name', 'middle_name', 'last_name', 
                'specialty_id', 'course', 'email', 'address', 'phone', 
                'birth_date', 'enrollment_date', 'status'
            ];
            $updateFields = [];
            $params = [];
            foreach ($data as $field => $value) {
                if (in_array($field, $allowedFields)) {
                    $updateFields[] = "$field = ?";
                    $params[] = $value;
                }
            }
            if (empty($updateFields)) {
                throw new InvalidArgumentException("No valid fields provided for update.");
            }
            $params[] = $id;
            $sql = "UPDATE {$this->table} SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $affectedRows = $this->db->update($sql, $params);
            if ($affectedRows > 0) {
                error_log("Student updated successfully. ID: $id, affected rows: $affectedRows");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating student (ID: $id): " . $e->getMessage());
            return false;
        }
    }
    public function deleteStudent(int $id): bool
    {
        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Student ID must be a positive integer.");
            }
            if (!$this->getStudentById($id)) {
                throw new InvalidArgumentException("Student with ID $id not found.");
            }
            $sql = "UPDATE {$this->table} SET status = 'inactive' WHERE id = ?";
            $affectedRows = $this->db->update($sql, [$id]);
            if ($affectedRows > 0) {
                error_log("Student soft deleted successfully. ID: $id");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting student (ID: $id): " . $e->getMessage());
            return false;
        }
    }
    public function permanentDeleteStudent(int $id): bool
    {
        try {
            if ($id <= 0) {
                throw new InvalidArgumentException("Student ID must be a positive integer.");
            }
            if (!$this->getStudentById($id)) {
                throw new InvalidArgumentException("Student with ID $id not found.");
            }
            $sql = "DELETE FROM {$this->table} WHERE id = ?";
            $affectedRows = $this->db->delete($sql, [$id]);
            if ($affectedRows > 0) {
                error_log("Student permanently deleted. ID: $id");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error permanently deleting student (ID: $id): " . $e->getMessage());
            return false;
        }
    }
    public function getAllStudents(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        try {
            $sql = "SELECT 
                s.*,
                sp.name as specialty_name,
                sp.code as specialty_code
            FROM {$this->table} s
            LEFT JOIN specialties sp ON s.specialty_id = sp.id";
            $whereConditions = [];
            $params = [];
            if (!empty($filters['specialty_id'])) {
                $whereConditions[] = "s.specialty_id = ?";
                $params[] = $filters['specialty_id'];
            }
            if (!empty($filters['course'])) {
                $whereConditions[] = "s.course = ?";
                $params[] = $filters['course'];
            }
            if (!empty($filters['status'])) {
                $whereConditions[] = "s.status = ?";
                $params[] = $filters['status'];
            } else {
                $whereConditions[] = "s.status != 'inactive'";
            }
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $whereConditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.faculty_number LIKE ? OR s.email LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            $sql .= " ORDER BY s.last_name, s.first_name";
            if ($limit > 0) {
                $sql .= " LIMIT ?";
                $params[] = $limit;
                if ($offset > 0) {
                    $sql .= " OFFSET ?";
                    $params[] = $offset;
                }
            }
            $students = $this->db->select($sql, $params);
            foreach ($students as &$student) {
                $student['id'] = (int)$student['id'];
                $student['specialty_id'] = (int)$student['specialty_id'];
                $student['course'] = (int)$student['course'];
            }
            return $students;
        } catch (Exception $e) {
            error_log("Error getting all students: " . $e->getMessage());
            return [];
        }
    }
    public function getStudentsBySpecialtyAndCourse(int $specialtyId, int $course): array
    {
        return $this->getAllStudents([
            'specialty_id' => $specialtyId,
            'course' => $course
        ]);
    }
    public function getStudentCount(array $filters = []): int
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table} s";
            $whereConditions = [];
            $params = [];
            if (!empty($filters['specialty_id'])) {
                $whereConditions[] = "s.specialty_id = ?";
                $params[] = $filters['specialty_id'];
            }
            if (!empty($filters['course'])) {
                $whereConditions[] = "s.course = ?";
                $params[] = $filters['course'];
            }
            if (!empty($filters['status'])) {
                $whereConditions[] = "s.status = ?";
                $params[] = $filters['status'];
            } else {
                $whereConditions[] = "s.status != 'inactive'";
            }
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $whereConditions[] = "(s.first_name LIKE ? OR s.last_name LIKE ? OR s.faculty_number LIKE ? OR s.email LIKE ?)";
                $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            if (!empty($whereConditions)) {
                $sql .= " WHERE " . implode(" AND ", $whereConditions);
            }
            $result = $this->db->selectOne($sql, $params);
            return (int)$result['total'];
        } catch (Exception $e) {
            error_log("Error getting student count: " . $e->getMessage());
            return 0;
        }
    }
    public function facultyNumberExists(string $facultyNumber, int $excludeId = 0): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE faculty_number = ?";
            $params = [$facultyNumber];
            if ($excludeId > 0) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            $result = $this->db->selectOne($sql, $params);
            return (int)$result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking faculty number existence: " . $e->getMessage());
            return false;
        }
    }
    public function emailExists(string $email, int $excludeId = 0): bool
    {
        try {
            $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?";
            $params = [$email];
            if ($excludeId > 0) {
                $sql .= " AND id != ?";
                $params[] = $excludeId;
            }
            $result = $this->db->selectOne($sql, $params);
            return (int)$result['count'] > 0;
        } catch (Exception $e) {
            error_log("Error checking email existence: " . $e->getMessage());
            return false;
        }
    }
    public function getStudentStatistics(): array
    {
        try {
            $stats = [];
            $stats['total_students'] = $this->getStudentCount();
            $stats['active_students'] = $this->getStudentCount(['status' => 'active']);
            $courseStats = $this->db->select(
                "SELECT course, COUNT(*) as count FROM {$this->table} WHERE status = 'active' GROUP BY course ORDER BY course"
            );
            $stats['by_course'] = $courseStats;
            $specialtyStats = $this->db->select(
                "SELECT sp.name, COUNT(s.id) as count 
                FROM {$this->table} s 
                JOIN specialties sp ON s.specialty_id = sp.id 
                WHERE s.status = 'active' 
                GROUP BY sp.id, sp.name 
                ORDER BY count DESC"
            );
            $stats['by_specialty'] = $specialtyStats;
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting student statistics: " . $e->getMessage());
            return [];
        }
    }
}
if (basename($_SERVER['PHP_SELF']) === 'Student.php') {
    echo "=== Student Model Test ===\n";
    try {
        $student = new Student();
        echo "\n--- Testing getAllStudents() ---\n";
        $students = $student->getAllStudents(['status' => 'active'], 5);
        echo "Found " . count($students) . " active students (limited to 5):\n";
        foreach ($students as $s) {
            echo "- {$s['faculty_number']}: {$s['first_name']} {$s['last_name']} ({$s['specialty_name']}, Course {$s['course']})\n";
        }
        if (!empty($students)) {
            $firstStudent = $students[0];
            echo "\n--- Testing getStudentById({$firstStudent['id']}) ---\n";
            $studentById = $student->getStudentById($firstStudent['id']);
            if ($studentById) {
                echo "✅ Found student: {$studentById['first_name']} {$studentById['last_name']}\n";
            } else {
                echo "❌ Student not found\n";
            }
        }
        echo "\n--- Testing getStudentStatistics() ---\n";
        $stats = $student->getStudentStatistics();
        echo "📊 Total students: " . $stats['total_students'] . "\n";
        echo "📊 Active students: " . $stats['active_students'] . "\n";
        echo "📊 Students by course:\n";
        foreach ($stats['by_course'] as $courseStat) {
            echo "   Course {$courseStat['course']}: {$courseStat['count']} students\n";
        }
        echo "📊 Students by specialty:\n";
        foreach ($stats['by_specialty'] as $specialtyStat) {
            echo "   {$specialtyStat['name']}: {$specialtyStat['count']} students\n";
        }
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n";
    }
    echo "\n=== Test Complete ===\n";
}
?>
