<?php
require_once 'Auth.php';
$user = Auth::getUser();
$userRole = Auth::getRole();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Недостатъчни права - Университетска система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
        }
        .error-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .error-body {
            padding: 2rem;
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
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-weight: 600;
        }
        .badge-admin {
            background-color: #f8f9fa;
            color: white;
        }
        .badge-teacher {
            background-color: #f8f9fa;
            color: white;
        }
        .badge-student {
            background-color: #f8f9fa;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card error-card">
                    <div class="error-header">
                        <i class="bi bi-shield-exclamation display-4 mb-3"></i>
                        <h2 class="h4 mb-0">Недостатъчни права</h2>
                        <p class="mb-0 opacity-75">Нямате достъп до тази страница</p>
                    </div>
                    <div class="error-body text-center">
                        <div class="mb-4">
                            <h5 class="text-muted">Тази страница е достъпна само за администратори</h5>
                            <p class="text-muted">
                                За да получите достъп, се обърнете към системния администратор или влезте с администраторски профил.
                            </p>
                        </div>
                        <?php if ($user): ?>
                            <div class="alert alert-info">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-person-circle me-2"></i>
                                    <div>
                                        <strong>Влезли сте като:</strong> <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                        <span class="role-badge badge-<?php echo $userRole; ?> ms-2">
                                            <?php
                                            $roleNames = [
                                                'admin' => 'Администратор',
                                                'teacher' => 'Преподавател', 
                                                'student' => 'Студент'
                                            ];
                                            echo $roleNames[$userRole] ?? ucfirst($userRole);
                                            ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        <div class="d-grid gap-2">
                            <a href="/index.php" class="btn btn-primary">
                                <i class="bi bi-house me-2"></i>
                                Обратно към началната страница
                            </a>
                            <?php if ($userRole === 'teacher'): ?>
                                <a href="/views/teacher_students.php" class="btn btn-outline-primary">
                                    <i class="bi bi-people me-2"></i>
                                    Преглед на студенти (само за четене)
                                </a>
                            <?php endif; ?>
                            <a href="/logout.php" class="btn btn-outline-secondary">
                                <i class="bi bi-box-arrow-right me-2"></i>
                                Изход
                            </a>
                        </div>
                        <div class="mt-4">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                Система за сигурност
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
