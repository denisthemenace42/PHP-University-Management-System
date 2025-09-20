<?php
require_once __DIR__ . '/../Database.php';
class Teacher
{
    private $db;
    private $table = 'teachers';
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    public function getTeacherById(int $id)
    {
        try {
            $sql = "SELECT 
                t.*,
                d.name as department_name,
                d.description as department_description
            FROM {$this->table} t
            LEFT JOIN departments d ON t.department_id = d.id
            WHERE t.id = ?";
            $teacher = $this->db->selectOne($sql, [$id]);
            if ($teacher) {
                $teacher['id'] = (int)$teacher['id'];
                $teacher['department_id'] = (int)$teacher['department_id'];
                $teacher['salary'] = (float)$teacher['salary'];
                return $teacher;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error getting teacher by ID: " . $e->getMessage());
            return false;
        }
    }
    public function getTeacherDisciplines(int $teacherId): array
    {
        try {
            $sql = "SELECT 
                d.*,
                COUNT(g.id) as total_grades,
                AVG(g.grade) as average_grade
            FROM disciplines d
            LEFT JOIN grades g ON d.id = g.discipline_id
            WHERE d.teacher_id = ?
            GROUP BY d.id
            ORDER BY d.semester, d.name";
            $disciplines = $this->db->select($sql, [$teacherId]);
            foreach ($disciplines as &$discipline) {
                $discipline['id'] = (int)$discipline['id'];
                $discipline['semester'] = (int)$discipline['semester'];
                $discipline['credits'] = (int)$discipline['credits'];
                $discipline['hours_per_week'] = (int)$discipline['hours_per_week'];
                $discipline['total_grades'] = (int)$discipline['total_grades'];
                $discipline['average_grade'] = $discipline['average_grade'] ? round($discipline['average_grade'], 2) : null;
            }
            return $disciplines;
        } catch (Exception $e) {
            error_log("Error getting teacher disciplines: " . $e->getMessage());
            return [];
        }
    }
    public function getStudentsForDiscipline(int $disciplineId): array
    {
        try {
            $sql = "SELECT 
                s.*,
                sp.name as specialty_name,
                g.id as grade_id,
                g.grade,
                g.date as grade_date,
                g.notes
            FROM students s
            LEFT JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN grades g ON s.id = g.student_id AND g.discipline_id = ?
            WHERE s.status = 'active'
            ORDER BY s.faculty_number";
            $students = $this->db->select($sql, [$disciplineId]);
            foreach ($students as &$student) {
                $student['id'] = (int)$student['id'];
                $student['course'] = (int)$student['course'];
                $student['grade_id'] = $student['grade_id'] ? (int)$student['grade_id'] : null;
                $student['grade'] = $student['grade'] ? (float)$student['grade'] : null;
            }
            return $students;
        } catch (Exception $e) {
            error_log("Error getting students for discipline: " . $e->getMessage());
            return [];
        }
    }
    public function addGrade(array $data)
    {
        try {
            $requiredFields = ['student_id', 'discipline_id', 'grade'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    throw new InvalidArgumentException("Required field '$field' is missing.");
                }
            }
            $grade = (float)$data['grade'];
            if ($grade < 2.0 || $grade > 6.0) {
                throw new InvalidArgumentException("Оценката трябва да бъде между 2.00 и 6.00.");
            }
            $existingGrade = $this->db->selectOne(
                "SELECT id FROM grades WHERE student_id = ? AND discipline_id = ?",
                [$data['student_id'], $data['discipline_id']]
            );
            if ($existingGrade) {
                throw new InvalidArgumentException("Студентът вече има оценка за тази дисциплина.");
            }
            $sql = "INSERT INTO grades (student_id, discipline_id, grade, date, notes) VALUES (?, ?, ?, NOW(), ?)";
            $params = [
                $data['student_id'],
                $data['discipline_id'],
                $grade,
                $data['notes'] ?? null
            ];
            $gradeId = $this->db->insert($sql, $params);
            if ($gradeId) {
                error_log("Grade added successfully with ID: $gradeId");
                return (int)$gradeId;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error adding grade: " . $e->getMessage());
            throw $e;
        }
    }
    public function updateGrade(int $gradeId, array $data): bool
    {
        try {
            if ($gradeId <= 0) {
                throw new InvalidArgumentException("Invalid grade ID.");
            }
            if (isset($data['grade'])) {
                $grade = (float)$data['grade'];
                if ($grade < 2.0 || $grade > 6.0) {
                    throw new InvalidArgumentException("Оценката трябва да бъде между 2.00 и 6.00.");
                }
            }
            $allowedFields = ['grade', 'notes'];
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
            $params[] = $gradeId;
            $sql = "UPDATE grades SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $affectedRows = $this->db->update($sql, $params);
            if ($affectedRows > 0) {
                error_log("Grade updated successfully. ID: $gradeId");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error updating grade: " . $e->getMessage());
            throw $e;
        }
    }
    public function deleteGrade(int $gradeId): bool
    {
        try {
            if ($gradeId <= 0) {
                throw new InvalidArgumentException("Invalid grade ID.");
            }
            $sql = "DELETE FROM grades WHERE id = ?";
            $affectedRows = $this->db->delete($sql, [$gradeId]);
            if ($affectedRows > 0) {
                error_log("Grade deleted successfully. ID: $gradeId");
                return true;
            }
            return false;
        } catch (Exception $e) {
            error_log("Error deleting grade: " . $e->getMessage());
            throw $e;
        }
    }
    public function getGradesForDiscipline(int $disciplineId, array $filters = []): array
    {
        try {
            $sql = "SELECT 
                g.*,
                s.faculty_number,
                s.first_name,
                s.middle_name,
                s.last_name,
                d.name as discipline_name
            FROM grades g
            LEFT JOIN students s ON g.student_id = s.id
            LEFT JOIN disciplines d ON g.discipline_id = d.id
            WHERE g.discipline_id = ?";
            $params = [$disciplineId];
            if (!empty($filters['student_id'])) {
                $sql .= " AND g.student_id = ?";
                $params[] = $filters['student_id'];
            }
            if (!empty($filters['min_grade'])) {
                $sql .= " AND g.grade >= ?";
                $params[] = $filters['min_grade'];
            }
            if (!empty($filters['max_grade'])) {
                $sql .= " AND g.grade <= ?";
                $params[] = $filters['max_grade'];
            }
            $sql .= " ORDER BY s.faculty_number";
            $grades = $this->db->select($sql, $params);
            foreach ($grades as &$grade) {
                $grade['id'] = (int)$grade['id'];
                $grade['student_id'] = (int)$grade['student_id'];
                $grade['discipline_id'] = (int)$grade['discipline_id'];
                $grade['grade'] = (float)$grade['grade'];
            }
            return $grades;
        } catch (Exception $e) {
            error_log("Error getting grades for discipline: " . $e->getMessage());
            return [];
        }
    }
    public function getTeacherStatistics(int $teacherId): array
    {
        try {
            $stats = [];
            $totalDisciplines = $this->db->selectOne(
                "SELECT COUNT(*) as total FROM disciplines WHERE teacher_id = ?",
                [$teacherId]
            );
            $stats['total_disciplines'] = (int)$totalDisciplines['total'];
            $totalGrades = $this->db->selectOne(
                "SELECT COUNT(*) as total FROM grades g 
                 LEFT JOIN disciplines d ON g.discipline_id = d.id 
                 WHERE d.teacher_id = ?",
                [$teacherId]
            );
            $stats['total_grades'] = (int)$totalGrades['total'];
            $averageGrade = $this->db->selectOne(
                "SELECT AVG(g.grade) as average FROM grades g 
                 LEFT JOIN disciplines d ON g.discipline_id = d.id 
                 WHERE d.teacher_id = ?",
                [$teacherId]
            );
            $stats['average_grade'] = $averageGrade['average'] ? round($averageGrade['average'], 2) : 0;
            return $stats;
        } catch (Exception $e) {
            error_log("Error getting teacher statistics: " . $e->getMessage());
            return [];
        }
    }
}
?>
