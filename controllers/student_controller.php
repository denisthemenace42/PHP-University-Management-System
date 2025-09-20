<?php
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Student.php';
class StudentController
{
    private $studentModel;
    private $errors = [];
    private $success = '';
    public function __construct()
    {
        $this->studentModel = new Student();
    }
    public function handleRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithError('Invalid request method. Only POST requests are allowed.');
            return;
        }
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'create':
                $this->createStudent();
                break;
            case 'update':
                $this->updateStudent();
                break;
            case 'delete':
                $this->deleteStudent();
                break;
            default:
                $this->redirectWithError('Invalid action specified.');
                break;
        }
    }
    private function createStudent()
    {
        try {
            $requiredFields = ['faculty_number', 'first_name', 'last_name', 'specialty_id', 'course', 'email', 'enrollment_date'];
            foreach ($requiredFields as $field) {
                if (empty($_POST[$field])) {
                    $this->errors[] = "Полето '{$this->getFieldLabel($field)}' е задължително.";
                }
            }
            $this->validateStudentData($_POST);
            if (!empty($this->errors)) {
                $this->redirectWithError(implode(' ', $this->errors));
                return;
            }
            if ($this->studentModel->facultyNumberExists($_POST['faculty_number'])) {
                $this->redirectWithError('Студент с този факултетен номер вече съществува.');
                return;
            }
            if ($this->studentModel->emailExists($_POST['email'])) {
                $this->redirectWithError('Студент с този email адрес вече съществува.');
                return;
            }
            $studentData = $this->prepareStudentData($_POST);
            $studentId = $this->studentModel->createStudent($studentData);
            if ($studentId) {
                $this->redirectWithSuccess('created', "Студентът е създаден успешно с ID: $studentId");
            } else {
                $this->redirectWithError('Грешка при създаване на студента. Моля опитайте отново.');
            }
        } catch (Exception $e) {
            error_log("Error creating student: " . $e->getMessage());
            $this->redirectWithError('Възникна системна грешка. Моля опитайте отново.');
        }
    }
    private function updateStudent()
    {
        try {
            $studentId = (int)($_POST['id'] ?? 0);
            if ($studentId <= 0) {
                $this->redirectWithError('Невалиден студентски ID.');
                return;
            }
            $existingStudent = $this->studentModel->getStudentById($studentId);
            if (!$existingStudent) {
                $this->redirectWithError('Студентът не е намерен.');
                return;
            }
            $this->validateStudentData($_POST, $studentId);
            if (!empty($this->errors)) {
                $this->redirectWithError(implode(' ', $this->errors));
                return;
            }
            if (!empty($_POST['faculty_number']) && 
                $this->studentModel->facultyNumberExists($_POST['faculty_number'], $studentId)) {
                $this->redirectWithError('Студент с този факултетен номер вече съществува.');
                return;
            }
            if (!empty($_POST['email']) && 
                $this->studentModel->emailExists($_POST['email'], $studentId)) {
                $this->redirectWithError('Студент с този email адрес вече съществува.');
                return;
            }
            $updateData = $this->prepareStudentData($_POST, true);
            $updated = $this->studentModel->updateStudent($studentId, $updateData);
            if ($updated) {
                $this->redirectWithSuccess('updated', 'Данните на студента са обновени успешно.', $studentId);
            } else {
                $this->redirectWithError('Няма промени за запазване или възникна грешка.');
            }
        } catch (Exception $e) {
            error_log("Error updating student: " . $e->getMessage());
            $this->redirectWithError('Възникна системна грешка при обновяването.');
        }
    }
    private function deleteStudent()
    {
        try {
            $studentId = (int)($_POST['id'] ?? 0);
            if ($studentId <= 0) {
                $this->redirectWithError('Невалиден студентски ID.');
                return;
            }
            $existingStudent = $this->studentModel->getStudentById($studentId);
            if (!$existingStudent) {
                $this->redirectWithError('Студентът не е намерен.');
                return;
            }
            if ($existingStudent['status'] === 'inactive') {
                $this->redirectWithError('Студентът вече е деактивиран.');
                return;
            }
            $deleted = $this->studentModel->deleteStudent($studentId);
            if ($deleted) {
                $studentName = $existingStudent['first_name'] . ' ' . $existingStudent['last_name'];
                $this->redirectWithSuccess('deleted', "Студентът {$studentName} е изтрит успешно.");
            } else {
                $this->redirectWithError('Грешка при изтриване на студента.');
            }
        } catch (Exception $e) {
            error_log("Error deleting student: " . $e->getMessage());
            $this->redirectWithError('Възникна системна грешка при изтриването.');
        }
    }
    private function validateStudentData(array $data, int $excludeId = 0)
    {
        if (!empty($data['faculty_number'])) {
            if (!preg_match('/^[0-9]{6,12}$/', $data['faculty_number'])) {
                $this->errors[] = 'Факултетният номер трябва да съдържа между 6 и 12 цифри.';
            }
        }
        if (!empty($data['email'])) {
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                $this->errors[] = 'Невалиден email адрес.';
            }
            if (strlen($data['email']) > 100) {
                $this->errors[] = 'Email адресът е твърде дълъг (максимум 100 символа).';
            }
        }
        if (!empty($data['first_name'])) {
            if (!preg_match('/^[А-Яа-яA-Za-z\s]+$/u', $data['first_name'])) {
                $this->errors[] = 'Името трябва да съдържа само букви.';
            }
            if (strlen($data['first_name']) > 50) {
                $this->errors[] = 'Името е твърде дълго (максимум 50 символа).';
            }
        }
        if (!empty($data['middle_name'])) {
            if (!preg_match('/^[А-Яа-яA-Za-z\s]*$/u', $data['middle_name'])) {
                $this->errors[] = 'Презимето трябва да съдържа само букви.';
            }
            if (strlen($data['middle_name']) > 50) {
                $this->errors[] = 'Презимето е твърде дълго (максимум 50 символа).';
            }
        }
        if (!empty($data['last_name'])) {
            if (!preg_match('/^[А-Яа-яA-Za-z\s]+$/u', $data['last_name'])) {
                $this->errors[] = 'Фамилията трябва да съдържа само букви.';
            }
            if (strlen($data['last_name']) > 50) {
                $this->errors[] = 'Фамилията е твърде дълга (максимум 50 символа).';
            }
        }
        if (!empty($data['course'])) {
            $course = (int)$data['course'];
            if ($course < 1 || $course > 6) {
                $this->errors[] = 'Курсът трябва да бъде между 1 и 6.';
            }
        }
        if (!empty($data['specialty_id'])) {
            $specialtyId = (int)$data['specialty_id'];
            if ($specialtyId <= 0) {
                $this->errors[] = 'Моля изберете валидна специалност.';
            }
        }
        if (!empty($data['phone'])) {
            if (!preg_match('/^[\+]?[0-9\s\-\(\)]+$/', $data['phone'])) {
                $this->errors[] = 'Невалиден телефонен номер.';
            }
            if (strlen($data['phone']) > 20) {
                $this->errors[] = 'Телефонният номер е твърде дълъг (максимум 20 символа).';
            }
        }
        if (!empty($data['birth_date'])) {
            $birthDate = DateTime::createFromFormat('Y-m-d', $data['birth_date']);
            if (!$birthDate) {
                $this->errors[] = 'Невалидна дата на раждане.';
            } else {
                $now = new DateTime();
                $age = $now->diff($birthDate)->y;
                if ($age < 16 || $age > 100) {
                    $this->errors[] = 'Възрастта трябва да бъде между 16 и 100 години.';
                }
            }
        }
        if (!empty($data['enrollment_date'])) {
            $enrollmentDate = DateTime::createFromFormat('Y-m-d', $data['enrollment_date']);
            if (!$enrollmentDate) {
                $this->errors[] = 'Невалидна дата на записване.';
            } else {
                $now = new DateTime();
                if ($enrollmentDate > $now) {
                    $this->errors[] = 'Датата на записване не може да бъде в бъдещето.';
                }
            }
        }
        if (!empty($data['address']) && strlen($data['address']) > 500) {
            $this->errors[] = 'Адресът е твърде дълъг (максимум 500 символа).';
        }
        if (!empty($data['status'])) {
            $validStatuses = ['active', 'inactive', 'graduated', 'expelled'];
            if (!in_array($data['status'], $validStatuses)) {
                $this->errors[] = 'Невалиден статус.';
            }
        }
    }
    private function prepareStudentData(array $data, bool $isUpdate = false): array
    {
        $preparedData = [];
        $allowedFields = [
            'faculty_number', 'first_name', 'middle_name', 'last_name',
            'specialty_id', 'course', 'email', 'address', 'phone',
            'birth_date', 'enrollment_date', 'status'
        ];
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $value = trim($data[$field]);
                if ($value === '' && in_array($field, ['middle_name', 'address', 'phone', 'birth_date'])) {
                    $value = null;
                }
                if (in_array($field, ['specialty_id', 'course']) && $value !== null) {
                    $value = (int)$value;
                }
                if (!$isUpdate || $value !== '' || $value === null) {
                    $preparedData[$field] = $value;
                }
            }
        }
        if (!$isUpdate && !isset($preparedData['status'])) {
            $preparedData['status'] = 'active';
        }
        return $preparedData;
    }
    private function getFieldLabel(string $field): string
    {
        $labels = [
            'faculty_number' => 'Факултетен номер',
            'first_name' => 'Име',
            'middle_name' => 'Презиме',
            'last_name' => 'Фамилия',
            'specialty_id' => 'Специалност',
            'course' => 'Курс',
            'email' => 'Email',
            'address' => 'Адрес',
            'phone' => 'Телефон',
            'birth_date' => 'Дата на раждане',
            'enrollment_date' => 'Дата на записване',
            'status' => 'Статус'
        ];
        return $labels[$field] ?? $field;
    }
    private function redirectWithSuccess(string $type, string $message = '', int $studentId = 0)
    {
        $url = '../views/student_form.php?success=' . urlencode($type);
        if (!empty($message)) {
            error_log("Student operation success: $message");
        }
        if ($studentId > 0 && $type !== 'deleted') {
            $url .= '&mode=view&id=' . $studentId;
        }
        header('Location: ' . $url);
        exit;
    }
    private function redirectWithError(string $message, int $studentId = 0)
    {
        error_log("Student operation error: $message");
        $url = '../views/student_form.php?error=' . urlencode($message);
        if ($studentId > 0) {
            $url .= '&mode=edit&id=' . $studentId;
        }
        header('Location: ' . $url);
        exit;
    }
    public function getAllStudents(array $filters = [], int $limit = 0, int $offset = 0): array
    {
        try {
            return $this->studentModel->getAllStudents($filters, $limit, $offset);
        } catch (Exception $e) {
            error_log("Error getting students: " . $e->getMessage());
            return [];
        }
    }
    public function getStudentById(int $id)
    {
        try {
            return $this->studentModel->getStudentById($id);
        } catch (Exception $e) {
            error_log("Error getting student by ID: " . $e->getMessage());
            return false;
        }
    }
    public function getStudentStatistics(): array
    {
        try {
            return $this->studentModel->getStudentStatistics();
        } catch (Exception $e) {
            error_log("Error getting student statistics: " . $e->getMessage());
            return [];
        }
    }
}
if (basename($_SERVER['PHP_SELF']) === 'student_controller.php') {
    try {
        $controller = new StudentController();
        $controller->handleRequest();
    } catch (Exception $e) {
        error_log("Fatal error in student controller: " . $e->getMessage());
        header('Location: ../views/student_form.php?error=' . urlencode('Възникна системна грешка. Моля опитайте отново.'));
        exit;
    }
}
?>
