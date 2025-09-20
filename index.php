<?php
require_once 'Auth.php';
require_once 'Database.php';
require_once 'models/Student.php';

// Redirect to login if not authenticated
Auth::requireLogin('/login.php');

// Get current user
$user = Auth::getUser();
$userRole = Auth::getRole();

// Initialize variables
$stats = [];
$recentStudents = [];
$errors = [];

try {
    $db = Database::getInstance();
    $studentModel = new Student();
    
    // Test database connection
    $connectionTest = $db->testConnection();
    
    // Get student statistics (only for admin and teacher)
    if (in_array($userRole, ['admin', 'teacher'])) {
        $stats = $studentModel->getStudentStatistics();
        
        // Get teachers count
        $teachersCount = $db->select("SELECT COUNT(*) as count FROM teachers WHERE status = 'active'");
        $stats['teachers_count'] = $teachersCount[0]['count'] ?? 0;
        
        // Get recent students (last 5) - only for admin and teacher
        $recentStudents = $studentModel->getAllStudents(['status' => 'active'], 5);
    }
    
} catch (Exception $e) {
    $errors[] = "Грешка при зареждане на данни: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?> - Университетска система</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border: none;
            border-radius: 15px;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .stat-card {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 15px;
            border: none;
        }
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.8;
        }
        .welcome-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .nav-card {
            background: white;
            border-radius: 15px;
            border: none;
            transition: all 0.3s ease;
        }
        .nav-card:hover {
            background: #f8f9fa;
            transform: scale(1.02);
        }
        .recent-students-card {
            background: white;
            border-radius: 15px;
            border: none;
        }
        .student-item {
            transition: background-color 0.2s;
        }
        .student-item:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-5">
        <!-- User Info and Navigation -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0">
                            <i class="bi bi-house me-2"></i>
                            Добре дошли, <?php
                            $roleNames = [
                                'admin' => 'Администратор',
                                'teacher' => 'Преподавател', 
                                'student' => 'Студент'
                            ];
                            echo $roleNames[$userRole] ?? ucfirst($userRole);
                            ?>!
                        </h1>
                    </div>
                    <a href="/logout.php" class="btn btn-warning btn-lg">
                        <i class="bi bi-box-arrow-right me-2"></i>
                        Изход
                    </a>
                </div>
            </div>
        </div>

        <!-- Welcome Section -->
        <div class="row justify-content-center mb-5">
            <div class="col-12 col-lg-10">
                <div class="card welcome-card shadow-lg">
                    <div class="card-body text-center py-5">
                        <h1 class="display-4 mb-3">
                            <i class="bi bi-mortarboard text-primary me-3"></i>
                            Университетска система
                        </h1>
                        <p class="lead text-muted mb-4">
                            Добре дошли в системата за управление на студенти и учебни данни
                        </p>
                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-warning">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                <?php foreach ($errors as $error): ?>
                                    <div><?php echo htmlspecialchars($error); ?></div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        <div class="row g-3 justify-content-center">
                            <?php if (Auth::hasPermission('manage_students')): ?>
                                <div class="col-auto">
                                    <a href="views/students_list.php" class="btn btn-primary btn-lg">
                                        <i class="bi bi-people me-2"></i>
                                        Студенти
                                    </a>
                                </div>
                                <?php if (Auth::hasPermission('create_student')): ?>
                                    <div class="col-auto">
                                        <a href="views/student_form.php?mode=create" class="btn btn-outline-primary btn-lg">
                                            <i class="bi bi-person-plus me-2"></i>
                                            Добави студент
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (Auth::hasPermission('view_my_profile')): ?>
                                <div class="col-auto">
                                    <a href="views/student_profile.php" class="btn btn-success btn-lg">
                                        <i class="bi bi-person me-2"></i>
                                        Моят профил
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (Auth::hasPermission('manage_teachers')): ?>
                                <div class="col-auto">
                                    <a href="views/teacher_form.php?mode=create" class="btn btn-success btn-lg">
                                        <i class="bi bi-person-plus me-2"></i>
                                        Добави преподавател
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (Auth::hasPermission('view_disciplines')): ?>
                                <div class="col-auto">
                                    <a href="views/teacher_disciplines.php" class="btn btn-info btn-lg">
                                        <i class="bi bi-book me-2"></i>
                                        Дисциплини
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($userRole === 'teacher'): ?>
                                <div class="col-auto">
                                    <a href="views/teacher_grades.php" class="btn btn-warning btn-lg">
                                        <i class="bi bi-plus-circle me-2"></i>
                                        Въведи оценки
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (in_array($userRole, ['admin', 'teacher'])): ?>
                                <div class="col-auto">
                                    <a href="views/reports.php" class="btn btn-primary btn-lg" style="background-color: #6f42c1; border-color: #6f42c1;">
                                        <i class="bi bi-file-text me-2"></i>
                                        Справки
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if ($userRole === 'teacher'): ?>
                                <div class="col-auto">
                                    <a href="views/teacher_profile.php" class="btn btn-success btn-lg">
                                        <i class="bi bi-person me-2"></i>
                                        Моят профил
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if (Auth::hasPermission('view_my_grades') && !Auth::hasPermission('create_grade')): ?>
                                <div class="col-auto">
                                    <a href="views/student_grades.php" class="btn btn-info btn-lg">
                                        <i class="bi bi-star me-2"></i>
                                        Мои оценки
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <?php if (!empty($stats)): ?>
            <div class="row g-4 mb-5">
                <div class="col-md-3">
                    <div class="card stat-card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-people stat-icon mb-3"></i>
                            <h2 class="display-6 fw-bold"><?php echo $stats['total_students']; ?></h2>
                            <p class="mb-0">Общо студенти</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-person-check stat-icon mb-3"></i>
                            <h2 class="display-6 fw-bold"><?php echo $stats['active_students']; ?></h2>
                            <p class="mb-0">Активни студенти</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-book stat-icon mb-3"></i>
                            <h2 class="display-6 fw-bold"><?php echo count($stats['by_specialty']); ?></h2>
                            <p class="mb-0">Специалности</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card dashboard-card h-100">
                        <div class="card-body text-center">
                            <i class="bi bi-person-workspace stat-icon mb-3"></i>
                            <h2 class="display-6 fw-bold"><?php echo $stats['teachers_count']; ?></h2>
                            <p class="mb-0">Преподаватели</p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
