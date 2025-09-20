<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Teacher.php';
Auth::requireLogin('/login.php');
Auth::requireRole('teacher');
$user = Auth::getUser();
$teacherModel = new Teacher();
$disciplines = [];
$students = [];
$grades = [];
$selectedDiscipline = null;
$success = '';
$error = '';
try {
    $disciplines = $teacherModel->getTeacherDisciplines($user['teacher_id']);
    $disciplineId = isset($_GET['discipline_id']) ? (int)$_GET['discipline_id'] : null;
    if ($disciplineId) {
        foreach ($disciplines as $discipline) {
            if ($discipline['id'] == $disciplineId) {
                $selectedDiscipline = $discipline;
                break;
            }
        }
        if ($selectedDiscipline) {
            $students = $teacherModel->getStudentsForDiscipline($disciplineId);
            $grades = $teacherModel->getGradesForDiscipline($disciplineId);
        }
    }
} catch (Exception $e) {
    $error = "Грешка при зареждане на данни: " . $e->getMessage();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $action = $_POST['action'] ?? '';
        switch ($action) {
            case 'add_grade':
                $gradeData = [
                    'student_id' => (int)$_POST['student_id'],
                    'discipline_id' => (int)$_POST['discipline_id'],
                    'grade' => (float)$_POST['grade'],
                    'notes' => trim($_POST['notes'] ?? '')
                ];
                $gradeId = $teacherModel->addGrade($gradeData);
                if ($gradeId) {
                    $success = "Оценката е добавена успешно.";
                    $students = $teacherModel->getStudentsForDiscipline($gradeData['discipline_id']);
                    $grades = $teacherModel->getGradesForDiscipline($gradeData['discipline_id']);
                }
                break;
            case 'update_grade':
                $gradeId = (int)$_POST['grade_id'];
                $gradeData = [
                    'grade' => (float)$_POST['grade'],
                    'notes' => trim($_POST['notes'] ?? '')
                ];
                if ($teacherModel->updateGrade($gradeId, $gradeData)) {
                    $success = "Оценката е обновена успешно.";
                    $students = $teacherModel->getStudentsForDiscipline($disciplineId);
                    $grades = $teacherModel->getGradesForDiscipline($disciplineId);
                }
                break;
            case 'delete_grade':
                $gradeId = (int)$_POST['grade_id'];
                if ($teacherModel->deleteGrade($gradeId)) {
                    $success = "Оценката е изтрита успешно.";
                    $students = $teacherModel->getStudentsForDiscipline($disciplineId);
                    $grades = $teacherModel->getGradesForDiscipline($disciplineId);
                }
                break;
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
$stats = $teacherModel->getTeacherStatistics($user['teacher_id']);
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Въведи оценки - Университетска система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .page-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .stats-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            color: 
        }
        .stats-label {
            color: 
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .discipline-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1rem;
            cursor: pointer;
        }
        .discipline-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        .discipline-card.selected {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .student-row {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 0.5rem;
            border-left: 4px solid 
        }
        .grade-input {
            max-width: 100px;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-success {
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .btn-danger {
            border-radius: 10px;
            padding: 8px 20px;
            font-weight: 600;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid 
            padding: 10px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: 
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card page-header">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h1 class="h3 mb-2">
                            <i class="bi bi-star me-2"></i>
                            Въведи оценки
                        </h1>
                        <p class="text-muted mb-0">
                            Преподавател: <?php 
                            $teacherDisplay = '';
                            if (!empty($user['teacher_title'])) {
                                $teacherDisplay = htmlspecialchars($user['teacher_title']) . ' ';
                            }
                            $teacherDisplay .= htmlspecialchars($user['teacher_name']);
                            echo $teacherDisplay;
                            ?>
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <a href="/index.php" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-left me-2"></i>
                            Назад
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_disciplines']; ?></div>
                    <div class="stats-label">Дисциплини</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_grades']; ?></div>
                    <div class="stats-label">Въведени оценки</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['average_grade']; ?></div>
                    <div class="stats-label">Средна оценка</div>
                </div>
            </div>
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="card content-card">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="bi bi-book me-2"></i>
                            Избери дисциплина
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($disciplines)): ?>
                            <p class="text-muted text-center">Няма назначени дисциплини</p>
                        <?php else: ?>
                            <?php foreach ($disciplines as $discipline): ?>
                                <div class="discipline-card <?php echo ($selectedDiscipline && $discipline['id'] == $selectedDiscipline['id']) ? 'selected' : ''; ?>" 
                                     onclick="selectDiscipline(<?php echo $discipline['id']; ?>)">
                                    <div class="card-body">
                                        <h6 class="card-title"><?php echo htmlspecialchars($discipline['name']); ?></h6>
                                        <p class="card-text small mb-1">
                                            <?php echo $discipline['semester']; ?> семестър • <?php echo $discipline['credits']; ?> кредита
                                        </p>
                                        <p class="card-text small mb-0">
                                            Оценки: <?php echo $discipline['total_grades']; ?>
                                            <?php if ($discipline['average_grade']): ?>
                                                • Средно: <?php echo $discipline['average_grade']; ?>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <?php if ($selectedDiscipline): ?>
                    <div class="card content-card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-people me-2"></i>
                                <?php echo htmlspecialchars($selectedDiscipline['name']); ?>
                            </h5>
                            <small class="text-muted">
                                <?php echo $selectedDiscipline['semester']; ?> семестър • 
                                <?php echo count($students); ?> студенти • 
                                <?php echo count($grades); ?> оценки
                            </small>
                        </div>
                        <div class="card-body">
                            <?php if (empty($students)): ?>
                                <p class="text-muted text-center">Няма студенти за тази дисциплина</p>
                            <?php else: ?>
                                <?php foreach ($students as $student): ?>
                                    <div class="student-row">
                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                                                <small class="text-muted">
                                                    Ф№: <?php echo htmlspecialchars($student['faculty_number']); ?> • 
                                                    Курс: <?php echo $student['course']; ?>
                                                </small>
                                            </div>
                                            <div class="col-md-3">
                                                <?php if ($student['grade_id']): ?>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge bg-success me-2"><?php echo $student['grade']; ?></span>
                                                        <small class="text-muted">
                                                            <?php echo date('d.m.Y', strtotime($student['grade_date'])); ?>
                                                        </small>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-muted">Няма оценка</span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="col-md-5">
                                                <?php if ($student['grade_id']): ?>
                                                    <button class="btn btn-sm btn-primary me-2" 
                                                            onclick="editGrade(<?php echo $student['grade_id']; ?>, <?php echo $student['grade']; ?>, '<?php echo htmlspecialchars($student['notes'] ?? ''); ?>')">
                                                        <i class="bi bi-pencil"></i> Редактирай
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" 
                                                            onclick="deleteGrade(<?php echo $student['grade_id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')">
                                                        <i class="bi bi-trash"></i> Изтрий
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-success" 
                                                            onclick="addGrade(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>')">
                                                        <i class="bi bi-plus"></i> Добави оценка
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card content-card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-book display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Изберете дисциплина</h4>
                            <p class="text-muted">Моля изберете дисциплина от лявата страна за да видите студентите и да въведете оценки.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="modal fade" id="addGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Добави оценка</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add_grade">
                        <input type="hidden" name="discipline_id" value="<?php echo $selectedDiscipline['id'] ?? ''; ?>">
                        <input type="hidden" name="student_id" id="add_student_id">
                        <div class="mb-3">
                            <label class="form-label">Студент</label>
                            <input type="text" class="form-control" id="add_student_name" readonly>
                        </div>
                        <div class="mb-3">
                            <label for="add_grade" class="form-label">Оценка *</label>
                            <input type="number" class="form-control" id="add_grade" name="grade" 
                                   min="2" max="6" step="0.01" required>
                            <div class="form-text">Оценката трябва да бъде между 2.00 и 6.00</div>
                        </div>
                        <div class="mb-3">
                            <label for="add_notes" class="form-label">Бележки</label>
                            <textarea class="form-control" id="add_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                        <button type="submit" class="btn btn-success">Добави оценка</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="editGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Редактирай оценка</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_grade">
                        <input type="hidden" name="grade_id" id="edit_grade_id">
                        <div class="mb-3">
                            <label for="edit_grade" class="form-label">Оценка *</label>
                            <input type="number" class="form-control" id="edit_grade" name="grade" 
                                   min="2" max="6" step="0.01" required>
                            <div class="form-text">Оценката трябва да бъде между 2.00 и 6.00</div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Бележки</label>
                            <textarea class="form-control" id="edit_notes" name="notes" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                        <button type="submit" class="btn btn-primary">Запази промените</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade" id="deleteGradeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Изтрий оценка</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="delete_grade">
                        <input type="hidden" name="grade_id" id="delete_grade_id">
                        <p>Сигурни ли сте, че искате да изтриете оценката за студент <strong id="delete_student_name"></strong>?</p>
                        <p class="text-danger"><strong>Това действие не може да бъде отменено!</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                        <button type="submit" class="btn btn-danger">Изтрий оценка</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectDiscipline(disciplineId) {
            window.location.href = '?discipline_id=' + disciplineId;
        }
        function addGrade(studentId, studentName) {
            document.getElementById('add_student_id').value = studentId;
            document.getElementById('add_student_name').value = studentName;
            document.getElementById('add_grade').value = '';
            document.getElementById('add_notes').value = '';
            const modal = new bootstrap.Modal(document.getElementById('addGradeModal'));
            modal.show();
        }
        function editGrade(gradeId, grade, notes) {
            document.getElementById('edit_grade_id').value = gradeId;
            document.getElementById('edit_grade').value = grade;
            document.getElementById('edit_notes').value = notes || '';
            const modal = new bootstrap.Modal(document.getElementById('editGradeModal'));
            modal.show();
        }
        function deleteGrade(gradeId, studentName) {
            document.getElementById('delete_grade_id').value = gradeId;
            document.getElementById('delete_student_name').textContent = studentName;
            const modal = new bootstrap.Modal(document.getElementById('deleteGradeModal'));
            modal.show();
        }
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
