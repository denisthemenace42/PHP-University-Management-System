<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
Auth::requireLogin('/login.php');
if (!in_array(Auth::getRole(), ['admin', 'teacher'])) {
    header('Location: /unauthorized.php');
    exit;
}
$user = Auth::getUser();
$results = [];
$error = '';
$success = '';
try {
    $db = Database::getInstance();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $reportType = $_POST['report_type'] ?? '';
        switch ($reportType) {
            case 'search_grades_student':
                $studentId = (int)($_POST['student_id'] ?? 0);
                if ($studentId > 0) {
                    $results = searchGradesByStudent($db, $studentId);
                }
                break;
            case 'search_grades_discipline':
                $disciplineId = (int)($_POST['discipline_id'] ?? 0);
                if ($disciplineId > 0) {
                    $results = searchGradesByDiscipline($db, $disciplineId);
                }
                break;
            case 'student_academic_report':
                $studentId = (int)($_POST['student_id'] ?? 0);
                if ($studentId > 0) {
                    $results = getStudentAcademicReport($db, $studentId);
                }
                break;
            case 'teachers_discipline':
                $disciplineId = (int)($_POST['discipline_id'] ?? 0);
                if ($disciplineId > 0) {
                    $results = getTeachersByDiscipline($db, $disciplineId);
                }
                break;
            case 'average_success_specialty':
                $specialtyId = (int)($_POST['specialty_id'] ?? 0);
                $course = (int)($_POST['course'] ?? 0);
                if ($specialtyId > 0 && $course > 0) {
                    $results = getAverageSuccessBySpecialtyAndCourse($db, $specialtyId, $course);
                }
                break;
            case 'discipline_average':
                $disciplineId = (int)($_POST['discipline_id'] ?? 0);
                if ($disciplineId > 0) {
                    $results = getDisciplineAverage($db, $disciplineId);
                }
                break;
            case 'top_students_discipline':
                $disciplineId = (int)($_POST['discipline_id'] ?? 0);
                if ($disciplineId > 0) {
                    $results = getTopStudentsByDiscipline($db, $disciplineId);
                }
                break;
            case 'diploma_eligible':
                $specialtyId = (int)($_POST['specialty_id'] ?? 0);
                if ($specialtyId > 0) {
                    $results = getDiplomaEligibleStudents($db, $specialtyId);
                }
                break;
        }
    }
    $students = $db->select("SELECT id, CONCAT(first_name, ' ', last_name) as name, faculty_number FROM students WHERE status = 'active' ORDER BY first_name, last_name");
    $disciplines = $db->select("SELECT id, name FROM disciplines ORDER BY name");
    $specialties = $db->select("SELECT id, name FROM specialties ORDER BY name");
} catch (Exception $e) {
    $error = "Грешка при зареждане на данни: " . $e->getMessage();
}
function searchGradesByStudent($db, $studentId) {
    $sql = "SELECT 
                g.id,
                g.grade,
                g.notes,
                g.created_at,
                d.name as discipline_name,
                d.code as discipline_code,
                t.name as teacher_name,
                t.title as teacher_title
            FROM grades g
            JOIN disciplines d ON g.discipline_id = d.id
            JOIN teachers t ON d.teacher_id = t.id
            WHERE g.student_id = ?
            ORDER BY g.created_at DESC";
    return $db->select($sql, [$studentId]);
}
function searchGradesByDiscipline($db, $disciplineId) {
    $sql = "SELECT 
                g.id,
                g.grade,
                g.notes,
                g.created_at,
                s.first_name,
                s.last_name,
                s.faculty_number,
                t.name as teacher_name,
                t.title as teacher_title
            FROM grades g
            JOIN students s ON g.student_id = s.id
            JOIN disciplines d ON g.discipline_id = d.id
            JOIN teachers t ON d.teacher_id = t.id
            WHERE g.discipline_id = ?
            ORDER BY g.grade DESC, s.last_name, s.first_name";
    return $db->select($sql, [$disciplineId]);
}
function getStudentAcademicReport($db, $studentId) {
    $sql = "SELECT 
                s.*,
                sp.name as specialty_name,
                COUNT(g.id) as total_grades,
                AVG(g.grade) as average_grade,
                MIN(g.grade) as min_grade,
                MAX(g.grade) as max_grade
            FROM students s
            JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN grades g ON s.id = g.student_id
            WHERE s.id = ?
            GROUP BY s.id";
    $student = $db->select($sql, [$studentId]);
    if (!empty($student)) {
        $gradesSql = "SELECT 
                        g.grade,
                        g.notes,
                        g.created_at,
                        d.name as discipline_name,
                        d.code as discipline_code,
                        d.semester,
                        d.credits,
                        t.name as teacher_name,
                        t.title as teacher_title
                    FROM grades g
                    JOIN disciplines d ON g.discipline_id = d.id
                    JOIN teachers t ON d.teacher_id = t.id
                    WHERE g.student_id = ?
                    ORDER BY d.semester, d.name";
        $student[0]['detailed_grades'] = $db->select($gradesSql, [$studentId]);
    }
    return $student;
}
function getTeachersByDiscipline($db, $disciplineId) {
    $sql = "SELECT 
                t.*,
                d.name as discipline_name,
                d.code as discipline_code,
                d.semester,
                d.credits,
                dep.name as department_name,
                COUNT(DISTINCT g.student_id) as students_count,
                AVG(g.grade) as average_grade
            FROM teachers t
            JOIN disciplines d ON t.id = d.teacher_id
            LEFT JOIN departments dep ON t.department_id = dep.id
            LEFT JOIN grades g ON d.id = g.discipline_id
            WHERE d.id = ?
            GROUP BY t.id, d.id";
    return $db->select($sql, [$disciplineId]);
}
function getAverageSuccessBySpecialtyAndCourse($db, $specialtyId, $course) {
    $sql = "SELECT 
                s.id,
                s.first_name,
                s.last_name,
                s.faculty_number,
                sp.name as specialty_name,
                s.course,
                COUNT(g.id) as total_grades,
                AVG(g.grade) as average_grade
            FROM students s
            JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN grades g ON s.id = g.student_id
            WHERE s.specialty_id = ? AND s.course = ? AND s.status = 'active'
            GROUP BY s.id
            HAVING COUNT(g.id) > 0
            ORDER BY average_grade DESC";
    return $db->select($sql, [$specialtyId, $course]);
}
function getDisciplineAverage($db, $disciplineId) {
    $sql = "SELECT 
                d.name as discipline_name,
                d.code as discipline_code,
                d.semester,
                COUNT(g.id) as total_grades,
                COUNT(DISTINCT g.student_id) as students_count,
                AVG(g.grade) as average_grade,
                MIN(g.grade) as min_grade,
                MAX(g.grade) as max_grade,
                t.name as teacher_name,
                t.title as teacher_title
            FROM disciplines d
            LEFT JOIN grades g ON d.id = g.discipline_id
            LEFT JOIN teachers t ON d.teacher_id = t.id
            WHERE d.id = ?
            GROUP BY d.id";
    return $db->select($sql, [$disciplineId]);
}
function getTopStudentsByDiscipline($db, $disciplineId) {
    $sql = "SELECT 
                s.first_name,
                s.last_name,
                s.faculty_number,
                g.grade,
                g.created_at,
                sp.name as specialty_name,
                s.course
            FROM grades g
            JOIN students s ON g.student_id = s.id
            JOIN specialties sp ON s.specialty_id = sp.id
            WHERE g.discipline_id = ?
            ORDER BY g.grade DESC, g.created_at ASC
            LIMIT 3";
    return $db->select($sql, [$disciplineId]);
}
function getDiplomaEligibleStudents($db, $specialtyId) {
    $sql = "SELECT 
                s.id,
                s.first_name,
                s.last_name,
                s.faculty_number,
                s.course,
                sp.name as specialty_name,
                COUNT(g.id) as total_grades,
                AVG(g.grade) as average_grade
            FROM students s
            JOIN specialties sp ON s.specialty_id = sp.id
            LEFT JOIN grades g ON s.id = g.student_id
            WHERE s.specialty_id = ? AND s.status = 'active'
            GROUP BY s.id
            HAVING COUNT(g.id) > 0 AND AVG(g.grade) > 5.00
            ORDER BY average_grade DESC";
    return $db->select($sql, [$specialtyId]);
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Справки - Университетска система</title>
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
        .report-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        .report-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .report-header {
            background: linear-gradient(45deg, #6f42c1, #8e44ad);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        .report-body {
            padding: 1.5rem;
        }
        .results-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .btn-purple {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        .btn-purple:hover {
            background-color: #5a32a3;
            border-color: #5a32a3;
            color: white;
        }
        .form-control:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        .accordion-button {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            color: #6f42c1;
            font-weight: 600;
            padding: 0.75rem 1rem;
        }
        .accordion-button:not(.collapsed) {
            background-color: #6f42c1;
            color: white;
            border-color: #6f42c1;
        }
        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(111, 66, 193, 0.25);
            border-color: #6f42c1;
        }
        .accordion-button:hover {
            background-color: #5a32a3;
            color: white;
        }
        .accordion-button:not(.collapsed):hover {
            background-color: #5a32a3;
            color: white;
        }
        .accordion-item {
            border: 1px solid #e9ecef;
            margin-bottom: 0.5rem;
            border-radius: 8px;
            overflow: hidden;
        }
        .accordion-body {
            padding: 1rem;
            background-color: #f8f9fa;
        }
        .badge-excellent {
            background: linear-gradient(45deg, #28a745, #20c997);
        }
        .badge-good {
            background: linear-gradient(45deg, #17a2b8, #007bff);
        }
        .badge-average {
            background: linear-gradient(45deg, #ffc107, #fd7e14);
        }
        
        /* Additional styling improvements */
        .form-select:focus {
            border-color: #6f42c1;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        
        .btn-outline-primary {
            border-color: #6f42c1;
            color: #6f42c1;
        }
        
        .btn-outline-primary:hover {
            background-color: #6f42c1;
            border-color: #6f42c1;
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        .searchable-dropdown {
            position: relative;
            z-index: 1;
        }
        .searchable-dropdown.active {
            z-index: 100000;
        }
        .searchable-dropdown.active .dropdown-list {
            z-index: 100001 !important;
        }
        .accordion-body {
            overflow: visible !important;
        }
        .accordion-item {
            overflow: visible !important;
        }
        .searchable-dropdown .search-input {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .searchable-dropdown .search-input:focus {
            border-color: #6f42c1;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(111, 66, 193, 0.25);
        }
        .searchable-dropdown .dropdown-list {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            background: white;
            border: 1px solid 
            border-top: none;
            border-radius: 0 0 6px 6px;
            max-height: 200px;
            overflow-y: auto;
            z-index: 99999;
            display: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .searchable-dropdown .dropdown-list.show {
            display: block;
        }
        .searchable-dropdown .dropdown-item {
            padding: 0.5rem 0.75rem;
            cursor: pointer;
            border-bottom: 1px solid #f8f9fa;
            font-size: 0.875rem;
            transition: background-color 0.15s ease-in-out;
        }
        .searchable-dropdown .dropdown-item:hover {
            background-color: #f8f9fa;
        }
        .searchable-dropdown .dropdown-item.selected {
            background-color: #6f42c1;
            color: white;
        }
        .searchable-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }
        .searchable-dropdown .no-results {
            padding: 0.5rem 0.75rem;
            color: #6c757d;
            font-style: italic;
            font-size: 0.875rem;
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
                            <i class="bi bi-file-text me-2"></i>
                            Справки
                        </h1>
                        <p class="text-muted mb-0">
                            Потребител: <?php 
                            $userDisplay = '';
                            if (!empty($user['teacher_title'])) {
                                $userDisplay = htmlspecialchars($user['teacher_title']) . ' ';
                            }
                            $userDisplay .= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']);
                            echo $userDisplay;
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
        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-4">
                <div class="card report-card">
                    <div class="report-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-list-ul me-2"></i>
                            Доступни справки
                        </h5>
                    </div>
                    <div class="report-body">
                        <div class="mb-3">
                            <div class="accordion" id="searchAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#searchGrades" aria-expanded="false" aria-controls="searchGrades">
                                            <i class="bi bi-search me-2"></i>
                                            Търсене на оценки
                                        </button>
                                    </h2>
                                    <div id="searchGrades" class="accordion-collapse collapse" data-bs-parent="#searchAccordion">
                                        <div class="accordion-body">
                                            <form method="POST" class="mb-3">
                                                <input type="hidden" name="report_type" value="search_grades_student">
                                                <label class="form-label small">По студент:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="student_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете студент...</option>
                                                            <?php foreach ($students as $student): ?>
                                                                <option value="<?php echo $student['id']; ?>">
                                                                    <?php echo htmlspecialchars($student['name'] . ' (Ф№: ' . $student['faculty_number'] . ')'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="search_grades_discipline">
                                                <label class="form-label small">По дисциплина:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="discipline_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете дисциплина...</option>
                                                            <?php foreach ($disciplines as $discipline): ?>
                                                                <option value="<?php echo $discipline['id']; ?>">
                                                                    <?php echo htmlspecialchars($discipline['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="accordion" id="studentAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#studentReports" aria-expanded="false" aria-controls="studentReports">
                                            <i class="bi bi-person-badge me-2"></i>
                                            Справки за студенти
                                        </button>
                                    </h2>
                                    <div id="studentReports" class="accordion-collapse collapse" data-bs-parent="#studentAccordion">
                                        <div class="accordion-body">
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="student_academic_report">
                                                <label class="form-label small">Академична справка:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="student_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете студент...</option>
                                                            <?php foreach ($students as $student): ?>
                                                                <option value="<?php echo $student['id']; ?>">
                                                                    <?php echo htmlspecialchars($student['name'] . ' (Ф№: ' . $student['faculty_number'] . ')'); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-file-text"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="accordion" id="teacherAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#teacherReports" aria-expanded="false" aria-controls="teacherReports">
                                            <i class="bi bi-person-workspace me-2"></i>
                                            Справки за преподаватели
                                        </button>
                                    </h2>
                                    <div id="teacherReports" class="accordion-collapse collapse" data-bs-parent="#teacherAccordion">
                                        <div class="accordion-body">
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="teachers_discipline">
                                                <label class="form-label small">Преподаватели по дисциплина:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="discipline_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете дисциплина...</option>
                                                            <?php foreach ($disciplines as $discipline): ?>
                                                                <option value="<?php echo $discipline['id']; ?>">
                                                                    <?php echo htmlspecialchars($discipline['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-search"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="accordion" id="statsAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#statisticsReports" aria-expanded="false" aria-controls="statisticsReports">
                                            <i class="bi bi-graph-up me-2"></i>
                                            Статистически справки
                                        </button>
                                    </h2>
                                    <div id="statisticsReports" class="accordion-collapse collapse" data-bs-parent="#statsAccordion">
                                        <div class="accordion-body">
                                            <form method="POST" class="mb-3">
                                                <input type="hidden" name="report_type" value="average_success_specialty">
                                                <label class="form-label small">Среден успех по специалност:</label>
                                                <div class="mb-2">
                                                    <select name="specialty_id" class="form-select form-select-sm mb-2" required>
                                                        <option value="">Изберете специалност...</option>
                                                        <?php foreach ($specialties as $specialty): ?>
                                                            <option value="<?php echo $specialty['id']; ?>">
                                                                <?php echo htmlspecialchars($specialty['name']); ?>
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <select name="course" class="form-select form-select-sm" required>
                                                        <option value="">Изберете курс...</option>
                                                        <option value="1">1 курс</option>
                                                        <option value="2">2 курс</option>
                                                        <option value="3">3 курс</option>
                                                        <option value="4">4 курс</option>
                                                    </select>
                                                </div>
                                                <button type="submit" class="btn btn-purple btn-sm w-100">
                                                    <i class="bi bi-graph-up"></i> Генерирай
                                                </button>
                                            </form>
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="discipline_average">
                                                <label class="form-label small">Среден успех по дисциплина:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="discipline_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете дисциплина...</option>
                                                            <?php foreach ($disciplines as $discipline): ?>
                                                                <option value="<?php echo $discipline['id']; ?>">
                                                                    <?php echo htmlspecialchars($discipline['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-bar-chart"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="accordion" id="rankingAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#rankingReports" aria-expanded="false" aria-controls="rankingReports">
                                            <i class="bi bi-trophy me-2"></i>
                                            Класации
                                        </button>
                                    </h2>
                                    <div id="rankingReports" class="accordion-collapse collapse" data-bs-parent="#rankingAccordion">
                                        <div class="accordion-body">
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="top_students_discipline">
                                                <label class="form-label small">Топ 3 по дисциплина:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="discipline_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете дисциплина...</option>
                                                            <?php foreach ($disciplines as $discipline): ?>
                                                                <option value="<?php echo $discipline['id']; ?>">
                                                                    <?php echo htmlspecialchars($discipline['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-trophy"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <div class="accordion" id="diplomaAccordion">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#diplomaReports" aria-expanded="false" aria-controls="diplomaReports">
                                            <i class="bi bi-mortarboard me-2"></i>
                                            Дипломни работи
                                        </button>
                                    </h2>
                                    <div id="diplomaReports" class="accordion-collapse collapse" data-bs-parent="#diplomaAccordion">
                                        <div class="accordion-body">
                                            <form method="POST">
                                                <input type="hidden" name="report_type" value="diploma_eligible">
                                                <label class="form-label small">Студенти за дипломна работа:</label>
                                                <div class="row g-2">
                                                    <div class="col-8">
                                                        <select name="specialty_id" class="form-select form-select-sm" required>
                                                            <option value="">Изберете специалност...</option>
                                                            <?php foreach ($specialties as $specialty): ?>
                                                                <option value="<?php echo $specialty['id']; ?>">
                                                                    <?php echo htmlspecialchars($specialty['name']); ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-4">
                                                        <button type="submit" class="btn btn-purple btn-sm w-100">
                                                            <i class="bi bi-mortarboard"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <?php if (!empty($results)): ?>
                    <div class="card report-card">
                        <div class="report-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-table me-2"></i>
                                Резултати
                            </h5>
                        </div>
                        <div class="report-body">
                            <div class="table-responsive results-table">
                                <?php 
                                $reportType = $_POST['report_type'] ?? '';
                                include 'report_results.php';
                                ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card report-card">
                        <div class="report-body text-center py-5">
                            <i class="bi bi-file-text display-1 text-muted mb-3"></i>
                            <h4 class="text-muted">Изберете справка</h4>
                            <p class="text-muted">Моля изберете справка от лявата страна за да видите резултатите тук.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        class SearchableDropdown {
            static activeDropdown = null;
            static baseZIndex = 99999;
            constructor(selectElement) {
                this.originalSelect = selectElement;
                this.selectedValue = '';
                this.selectedText = '';
                this.options = [];
                this.filteredOptions = [];
                this.init();
            }
            init() {
                const selectOptions = this.originalSelect.querySelectorAll('option');
                this.options = Array.from(selectOptions).map(option => ({
                    value: option.value,
                    text: option.textContent.trim(),
                    selected: option.selected
                }));
                const selectedOption = this.options.find(opt => opt.selected);
                if (selectedOption && selectedOption.value) {
                    this.selectedValue = selectedOption.value;
                    this.selectedText = selectedOption.text;
                }
                this.filteredOptions = [...this.options];
                this.createDropdownHTML();
                this.originalSelect.style.display = 'none';
                this.addEventListeners();
            }
            
            createDropdownHTML() {
                const wrapper = document.createElement('div');
                wrapper.className = 'searchable-dropdown';
                
                const searchInput = document.createElement('input');
                searchInput.type = 'text';
                searchInput.className = 'form-control form-control-sm search-input';
                searchInput.placeholder = this.getPlaceholder();
                searchInput.value = this.selectedText;
                searchInput.autocomplete = 'off';
                
                const dropdownList = document.createElement('div');
                dropdownList.className = 'dropdown-list';
                
                wrapper.appendChild(searchInput);
                wrapper.appendChild(dropdownList);
                
                // Insert after original select
                this.originalSelect.parentNode.insertBefore(wrapper, this.originalSelect.nextSibling);
                
                this.wrapper = wrapper;
                this.searchInput = searchInput;
                this.dropdownList = dropdownList;
                
                this.updateDropdownList();
            }
            
            getPlaceholder() {
                const firstOption = this.options[0];
                if (firstOption && !firstOption.value) {
                    return firstOption.text;
                }
                return 'Търсене...';
            }
            
            updateDropdownList() {
                this.dropdownList.innerHTML = '';
                
                if (this.filteredOptions.length === 0) {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-results';
                    noResults.textContent = 'Няма намерени резултати';
                    this.dropdownList.appendChild(noResults);
                    return;
                }
                
                this.filteredOptions.forEach(option => {
                    if (!option.value) return; // Skip empty placeholder options
                    
                    const item = document.createElement('div');
                    item.className = 'dropdown-item';
                    item.textContent = option.text;
                    item.dataset.value = option.value;
                    
                    if (option.value === this.selectedValue) {
                        item.classList.add('selected');
                    }
                    
                    item.addEventListener('click', () => this.selectOption(option));
                    this.dropdownList.appendChild(item);
                });
            }
            
            filterOptions(searchTerm) {
                const term = searchTerm.toLowerCase();
                this.filteredOptions = this.options.filter(option => 
                    option.text.toLowerCase().includes(term)
                );
                this.updateDropdownList();
            }
            
            selectOption(option) {
                this.selectedValue = option.value;
                this.selectedText = option.text;
                this.searchInput.value = option.text;
                
                // Update original select
                this.originalSelect.value = option.value;
                
                // Trigger change event on original select
                const event = new Event('change', { bubbles: true });
                this.originalSelect.dispatchEvent(event);
                
                this.hideDropdown();
            }
            
            showDropdown() {
                // Close any previously active dropdown
                if (SearchableDropdown.activeDropdown && SearchableDropdown.activeDropdown !== this) {
                    SearchableDropdown.activeDropdown.hideDropdown();
                }
                
                // Set this as the active dropdown with highest z-index
                SearchableDropdown.activeDropdown = this;
                
                // Add active class to wrapper for higher z-index
                this.wrapper.classList.add('active');
                this.dropdownList.style.zIndex = SearchableDropdown.baseZIndex + 1;
                
                this.dropdownList.classList.add('show');
                this.updateDropdownList();
                
                // Adjust positioning if dropdown would be clipped
                this.adjustDropdownPosition();
            }
            
            adjustDropdownPosition() {
                const rect = this.searchInput.getBoundingClientRect();
                const dropdownRect = this.dropdownList.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const spaceBelow = viewportHeight - rect.bottom;
                const spaceAbove = rect.top;
                
                // If there's not enough space below and more space above, show dropdown above
                if (spaceBelow < dropdownRect.height && spaceAbove > spaceBelow) {
                    this.dropdownList.style.top = 'auto';
                    this.dropdownList.style.bottom = '100%';
                    this.dropdownList.style.borderRadius = '6px 6px 0 0';
                    this.dropdownList.style.borderTop = '1px solid #ced4da';
                    this.dropdownList.style.borderBottom = 'none';
                } else {
                    // Default positioning (below input)
                    this.dropdownList.style.top = '100%';
                    this.dropdownList.style.bottom = 'auto';
                    this.dropdownList.style.borderRadius = '0 0 6px 6px';
                    this.dropdownList.style.borderTop = 'none';
                    this.dropdownList.style.borderBottom = '1px solid #ced4da';
                }
            }
            
            hideDropdown() {
                this.dropdownList.classList.remove('show');
                
                // Remove active class from wrapper
                this.wrapper.classList.remove('active');
                
                // Clear active dropdown reference if this was the active one
                if (SearchableDropdown.activeDropdown === this) {
                    SearchableDropdown.activeDropdown = null;
                }
                
                // Reset z-index to base level
                this.dropdownList.style.zIndex = SearchableDropdown.baseZIndex;
            }
            
            addEventListeners() {
                // Search input events
                this.searchInput.addEventListener('input', (e) => {
                    const searchTerm = e.target.value;
                    this.filterOptions(searchTerm);
                    this.showDropdown();
                });
                
                this.searchInput.addEventListener('focus', () => {
                    this.showDropdown();
                });
                
                this.searchInput.addEventListener('click', () => {
                    this.showDropdown();
                });
                
                this.searchInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        this.hideDropdown();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        const firstVisible = this.dropdownList.querySelector('.dropdown-item:not(.selected)');
                        if (firstVisible) {
                            firstVisible.click();
                        }
                    }
                });
                
                // Click outside to close
                document.addEventListener('click', (e) => {
                    if (!this.wrapper.contains(e.target)) {
                        this.hideDropdown();
                    }
                });
                
                // Adjust position on window resize
                window.addEventListener('resize', () => {
                    if (this.dropdownList.classList.contains('show')) {
                        this.adjustDropdownPosition();
                    }
                });
            }
        }
        
        // Initialize searchable dropdowns when page loads
        document.addEventListener('DOMContentLoaded', function() {
            const selectElements = document.querySelectorAll('select[name="student_id"], select[name="discipline_id"], select[name="specialty_id"]');
            selectElements.forEach(select => {
                new SearchableDropdown(select);
            });
        });
    </script>
</body>
</html>
