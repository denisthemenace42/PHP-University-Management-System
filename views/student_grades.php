<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Student.php';
Auth::requireLogin('/login.php');
Auth::requireRole('student');
$user = Auth::getUser();
$grades = [];
$studentData = [];
$statistics = [];
$error = '';
try {
    $db = Database::getInstance();
    $studentModel = new Student();
    $studentData = $studentModel->getStudentById($user['student_id']);
    if (!$studentData) {
        throw new Exception("Студентски данни не са намерени.");
    }
    $sql = "
        SELECT 
            g.id,
            g.grade,
            g.date,
            g.exam_type,
            g.notes,
            g.created_at,
            d.name as discipline_name,
            d.code as discipline_code,
            d.semester,
            d.credits,
            t.name as teacher_name,
            t.title as teacher_title
        FROM grades g
        LEFT JOIN disciplines d ON g.discipline_id = d.id
        LEFT JOIN teachers t ON d.teacher_id = t.id
        WHERE g.student_id = ?
        ORDER BY g.date DESC, d.name ASC
    ";
    $grades = $db->select($sql, [$user['student_id']]);
    if (!empty($grades)) {
        $totalGrades = count($grades);
        $sumGrades = array_sum(array_column($grades, 'grade'));
        $averageGrade = round($sumGrades / $totalGrades, 2);
        $excellentGrades = count(array_filter($grades, function($grade) {
            return $grade['grade'] >= 5.50;
        }));
        $goodGrades = count(array_filter($grades, function($grade) {
            return $grade['grade'] >= 4.50 && $grade['grade'] < 5.50;
        }));
        $satisfactoryGrades = count(array_filter($grades, function($grade) {
            return $grade['grade'] >= 3.50 && $grade['grade'] < 4.50;
        }));
        $poorGrades = count(array_filter($grades, function($grade) {
            return $grade['grade'] < 3.50;
        }));
        $statistics = [
            'total_grades' => $totalGrades,
            'average_grade' => $averageGrade,
            'excellent' => $excellentGrades,
            'good' => $goodGrades,
            'satisfactory' => $satisfactoryGrades,
            'poor' => $poorGrades
        ];
    }
} catch (Exception $e) {
    $error = "Грешка при зареждане на данни: " . $e->getMessage();
}
function getGradeClass($grade) {
    if ($grade >= 5.50) return 'success';
    if ($grade >= 4.50) return 'primary';
    if ($grade >= 3.50) return 'warning';
    return 'danger';
}
function getGradeText($grade) {
    if ($grade >= 5.50) return 'Отличен';
    if ($grade >= 4.50) return 'Много добър';
    if ($grade >= 3.50) return 'Добър';
    return 'Слаб';
}
function formatDate($date) {
    return date('d.m.Y', strtotime($date));
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои оценки - <?php echo htmlspecialchars($studentData['first_name'] . ' ' . $studentData['last_name']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .content-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .header-section {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .content-body {
            padding: 2rem;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 1.5rem;
            text-align: center;
            margin-bottom: 1rem;
        }
        .stats-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        .stats-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }
        .grade-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            border-left: 5px solid 
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease;
        }
        .grade-card:hover {
            transform: translateY(-5px);
        }
        .grade-badge {
            font-size: 1.5rem;
            font-weight: bold;
            padding: 0.5rem 1rem;
            border-radius: 10px;
        }
        .grade-excellent { background: linear-gradient(45deg, #667eea, #764ba2);
        .grade-good { background: linear-gradient(45deg, #667eea, #764ba2);
        .grade-satisfactory { background: linear-gradient(45deg, #667eea, #764ba2);
        .grade-poor { background: linear-gradient(45deg, #667eea, #764ba2);
        .discipline-info {
            border-left: 3px solid 
            padding-left: 1rem;
            margin-top: 1rem;
        }
        .teacher-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: 
        }
        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .btn-secondary {
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card content-card">
                    <div class="header-section">
                        <i class="bi bi-award display-4 mb-3"></i>
                        <h2 class="h4 mb-0">Мои оценки</h2>
                        <p class="mb-0 opacity-75">
                            <?php echo htmlspecialchars($studentData['first_name'] . ' ' . $studentData['last_name']); ?> - 
                            Ф№: <?php echo htmlspecialchars($studentData['faculty_number']); ?>
                        </p>
                    </div>
                    <div class="content-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <?php echo htmlspecialchars($error); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($grades)): ?>
                            <div class="row mb-4">
                                <div class="col-md-2">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $statistics['total_grades']; ?></div>
                                        <div class="stats-label">Общо оценки</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $statistics['average_grade']; ?></div>
                                        <div class="stats-label">Среден успех</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $statistics['excellent']; ?></div>
                                        <div class="stats-label">Отлични</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $statistics['good']; ?></div>
                                        <div class="stats-label">Много добри</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $statistics['satisfactory']; ?></div>
                                        <div class="stats-label">Добри</div>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="stats-card">
                                        <div class="stats-number"><?php echo $statistics['poor']; ?></div>
                                        <div class="stats-label">Слаби</div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <?php foreach ($grades as $grade): ?>
                                    <div class="col-md-6 mb-3">
                                        <div class="grade-card">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div>
                                                    <h5 class="mb-1">
                                                        <i class="bi bi-book me-2"></i>
                                                        <?php echo htmlspecialchars($grade['discipline_name']); ?>
                                                    </h5>
                                                    <p class="text-muted mb-0">
                                                        <i class="bi bi-code me-1"></i>
                                                        <?php echo htmlspecialchars($grade['discipline_code']); ?>
                                                    </p>
                                                </div>
                                                <div class="text-end">
                                                    <span class="grade-badge grade-<?php echo getGradeClass($grade['grade']); ?>">
                                                        <?php echo number_format($grade['grade'], 2); ?>
                                                    </span>
                                                    <div class="small mt-1">
                                                        <?php echo getGradeText($grade['grade']); ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="discipline-info">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar me-1"></i>
                                                            Семестър: <?php echo $grade['semester']; ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="bi bi-award me-1"></i>
                                                            Кредити: <?php echo $grade['credits']; ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="teacher-info">
                                                <div class="d-flex align-items-center">
                                                    <i class="bi bi-person-circle me-2"></i>
                                                    <div>
                                                        <div class="fw-bold">
                                                            <?php echo htmlspecialchars($grade['teacher_name']); ?>
                                                        </div>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($grade['teacher_title'] ?? ''); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="mt-3 pt-3 border-top">
                                                <div class="row">
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="bi bi-calendar-event me-1"></i>
                                                            Дата: <?php echo formatDate($grade['date']); ?>
                                                        </small>
                                                    </div>
                                                    <div class="col-6">
                                                        <small class="text-muted">
                                                            <i class="bi bi-pencil me-1"></i>
                                                            Тип: <?php echo ucfirst($grade['exam_type']); ?>
                                                        </small>
                                                    </div>
                                                </div>
                                                <?php if (!empty($grade['notes'])): ?>
                                                    <div class="mt-2">
                                                        <small class="text-muted">
                                                            <i class="bi bi-chat-text me-1"></i>
                                                            Забележка: <?php echo htmlspecialchars($grade['notes']); ?>
                                                        </small>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="empty-state">
                                <i class="bi bi-inbox"></i>
                                <h4>Няма въведени оценки</h4>
                                <p class="text-muted">
                                    Все още нямате оценки в системата.<br>
                                    Оценките ще се появят тук, когато преподавателите ги въведат.
                                </p>
                            </div>
                        <?php endif; ?>
                        <div class="text-center mt-4">
                            <a href="/index.php" class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2"></i>
                                Назад към началото
                            </a>
                            <a href="/views/student_profile.php" class="btn btn-secondary ms-2">
                                <i class="bi bi-person me-2"></i>
                                Моят профил
                            </a>
                        </div>
                    </div>
                </div>
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
    </script>
</body>
</html>
