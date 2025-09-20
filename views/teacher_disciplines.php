<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
Auth::requireLogin('/login.php');
Auth::requireRole('teacher');
$user = Auth::getUser();
$disciplines = [];
$error = '';
try {
    $db = Database::getInstance();
    $sql = "SELECT 
        d.*,
        t.name as teacher_name,
        t.title as teacher_title,
        dep.name as department_name,
        COUNT(g.id) as total_grades,
        AVG(g.grade) as average_grade
    FROM disciplines d
    LEFT JOIN teachers t ON d.teacher_id = t.id
    LEFT JOIN departments dep ON t.department_id = dep.id
    LEFT JOIN grades g ON d.id = g.discipline_id
    WHERE d.teacher_id = ?
    GROUP BY d.id
    ORDER BY d.semester, d.name";
    $disciplines = $db->select($sql, [$user['teacher_id']]);
    foreach ($disciplines as &$discipline) {
        $discipline['id'] = (int)$discipline['id'];
        $discipline['semester'] = (int)$discipline['semester'];
        $discipline['credits'] = (int)$discipline['credits'];
        $discipline['hours_per_week'] = (int)$discipline['hours_per_week'];
        $discipline['total_grades'] = (int)$discipline['total_grades'];
        $discipline['average_grade'] = $discipline['average_grade'] ? round($discipline['average_grade'], 2) : null;
    }
} catch (Exception $e) {
    $error = "Грешка при зареждане на дисциплини: " . $e->getMessage();
}
$stats = [
    'total_disciplines' => count($disciplines),
    'total_credits' => array_sum(array_column($disciplines, 'credits')),
    'semesters' => array_unique(array_column($disciplines, 'semester'))
];
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои дисциплини - Университетска система</title>
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
        .discipline-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            border: none;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }
        .discipline-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .discipline-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px 15px 0 0;
            padding: 1.5rem;
        }
        .discipline-body {
            padding: 1.5rem;
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
            color: #667eea;
        }
        .stats-label {
            color: #6c757d;
            font-size: 0.9rem;
            margin-top: 0.5rem;
        }
        .info-item {
            display: flex;
            align-items: center;
            margin-bottom: 0.75rem;
        }
        .info-item i {
            width: 20px;
            margin-right: 10px;
            color: #667eea;
        }
        .semester-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .credits-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .type-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
        .type-mandatory {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .type-elective {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        .type-optional {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
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
                            <i class="bi bi-book me-2"></i>
                            Мои дисциплини
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
                    <div class="stats-label">Общо дисциплини</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo $stats['total_credits']; ?></div>
                    <div class="stats-label">Общо кредити</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-number"><?php echo count($stats['semesters']); ?></div>
                    <div class="stats-label">Семестъра</div>
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
        <?php if (empty($disciplines)): ?>
            <div class="card discipline-card">
                <div class="card-body text-center py-5">
                    <i class="bi bi-book display-1 text-muted mb-3"></i>
                    <h4 class="text-muted">Няма назначени дисциплини</h4>
                    <p class="text-muted">В момента няма дисциплини, които да преподавате.</p>
                </div>
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($disciplines as $discipline): ?>
                    <div class="col-lg-6">
                        <div class="card discipline-card">
                            <div class="discipline-header">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title mb-0"><?php echo htmlspecialchars($discipline['name']); ?></h5>
                                    <span class="semester-badge">
                                        <?php echo $discipline['semester']; ?> семестър
                                    </span>
                                </div>
                                <p class="mb-0 opacity-75">
                                    Код: <?php echo htmlspecialchars($discipline['code']); ?>
                                </p>
                            </div>
                            <div class="discipline-body">
                                <div class="info-item">
                                    <i class="bi bi-credit-card"></i>
                                    <strong>Кредити:</strong>
                                    <span class="credits-badge ms-2"><?php echo $discipline['credits']; ?> кр.</span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-clock"></i>
                                    <strong>Часове седмично:</strong>
                                    <span class="ms-2"><?php echo $discipline['hours_per_week']; ?> ч.</span>
                                </div>
                                <div class="info-item">
                                    <i class="bi bi-tag"></i>
                                    <strong>Тип:</strong>
                                    <span class="type-badge type-<?php echo $discipline['type']; ?> ms-2">
                                        <?php
                                        $typeNames = [
                                            'mandatory' => 'Задължителна',
                                            'elective' => 'Избираема',
                                            'optional' => 'По избор'
                                        ];
                                        echo $typeNames[$discipline['type']] ?? ucfirst($discipline['type']);
                                        ?>
                                    </span>
                                </div>
                                <?php if (!empty($discipline['description'])): ?>
                                    <div class="info-item">
                                        <i class="bi bi-info-circle"></i>
                                        <strong>Описание:</strong>
                                        <p class="ms-2 mb-0 text-muted"><?php echo htmlspecialchars($discipline['description']); ?></p>
                                    </div>
                                <?php endif; ?>
                                <div class="row mt-3">
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h5 mb-1 text-primary"><?php echo $discipline['total_grades']; ?></div>
                                            <div class="small text-muted">Общо оценки</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="text-center">
                                            <div class="h5 mb-1 text-success">
                                                <?php echo $discipline['average_grade'] ? $discipline['average_grade'] : '-'; ?>
                                            </div>
                                            <div class="small text-muted">Средна оценка</div>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-3 pt-3 border-top">
                                    <div class="d-grid">
                                        <a href="teacher_grades.php?discipline_id=<?php echo $discipline['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-star me-1"></i>
                                            Оценки
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
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
