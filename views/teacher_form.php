<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
Auth::requireLogin('/login.php');
Auth::requireRole('admin');
$teacherData = [];
$mode = $_GET['mode'] ?? 'create';
$teacherId = $_GET['id'] ?? null;
$success = '';
$error = '';
try {
    $db = Database::getInstance();
    $departments = $db->select("SELECT id, name FROM departments ORDER BY name");
    if ($mode === 'edit' && $teacherId) {
        $sql = "SELECT t.*, d.name as department_name 
                FROM teachers t 
                LEFT JOIN departments d ON t.department_id = d.id 
                WHERE t.id = ?";
        $teacherData = $db->selectOne($sql, [$teacherId]);
        if (!$teacherData) {
            throw new Exception("Преподавателят не е намерен.");
        }
    }
} catch (Exception $e) {
    $error = "Грешка при зареждане на данни: " . $e->getMessage();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $department_id = (int)($_POST['department_id'] ?? 0);
        if (empty($name)) {
            throw new InvalidArgumentException("Името е задължително.");
        }
        if (empty($email)) {
            throw new InvalidArgumentException("Имейлът е задължителен.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Невалиден имейл адрес.");
        }
        if ($department_id <= 0) {
            throw new InvalidArgumentException("Катедрата е задължителна.");
        }
        $checkSql = "SELECT id FROM teachers WHERE email = ?" . ($mode === 'edit' ? " AND id != ?" : "");
        $params = [$email];
        if ($mode === 'edit') {
            $params[] = $teacherId;
        }
        $existing = $db->selectOne($checkSql, $params);
        if ($existing) {
            throw new InvalidArgumentException("Преподавател с този имейл вече съществува.");
        }
        if ($mode === 'create') {
            $sql = "INSERT INTO teachers (name, title, phone, email, department_id, hire_date) 
                    VALUES (?, ?, ?, ?, ?, CURDATE())";
            $params = [$name, $title, $phone, $email, $department_id];
            $result = $db->insert($sql, $params);
            if ($result) {
                $success = "Преподавателят е създаден успешно.";
                $teacherData = [];
            } else {
                $error = "Неуспешно създаване на преподавател.";
            }
        } else {
            $sql = "UPDATE teachers SET 
                    name = ?,
                    title = ?,
                    phone = ?,
                    email = ?,
                    department_id = ?,
                    updated_at = CURRENT_TIMESTAMP
                    WHERE id = ?";
            $params = [$name, $title, $phone, $email, $department_id, $teacherId];
            $result = $db->update($sql, $params);
            if ($result) {
                $success = "Преподавателят е обновен успешно.";
                $sql = "SELECT t.*, d.name as department_name 
                        FROM teachers t 
                        LEFT JOIN departments d ON t.department_id = d.id 
                        WHERE t.id = ?";
                $teacherData = $db->selectOne($sql, [$teacherId]);
            } else {
                $error = "Неуспешно обновяване на преподавател.";
            }
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
$pageTitle = $mode === 'edit' ? 'Редактиране на преподавател' : 'Добавяне на преподавател';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Университетска система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .form-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .form-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .form-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid 
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: 
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
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
        .form-label {
            font-weight: 600;
            color: 
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="form-header">
                        <i class="bi bi-person-plus display-4 mb-3"></i>
                        <h2 class="h4 mb-0"><?php echo htmlspecialchars($pageTitle); ?></h2>
                        <p class="mb-0 opacity-75">Управление на преподаватели</p>
                    </div>
                    <div class="form-body">
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
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">
                                            <i class="bi bi-person me-2"></i>Име *
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="name" 
                                               name="name"
                                               value="<?php echo htmlspecialchars($teacherData['name'] ?? ''); ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Моля въведете име на преподавателя.
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="title" class="form-label">
                                            <i class="bi bi-award me-2"></i>Титла
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="title" 
                                               name="title"
                                               value="<?php echo htmlspecialchars($teacherData['title'] ?? ''); ?>"
                                               placeholder="напр. доц.д-р., проф.д-р.">
                                    </div>
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="bi bi-telephone me-2"></i>Телефон
                                        </label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone"
                                               value="<?php echo htmlspecialchars($teacherData['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">
                                            <i class="bi bi-envelope me-2"></i>Имейл *
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email"
                                               value="<?php echo htmlspecialchars($teacherData['email'] ?? ''); ?>"
                                               required>
                                        <div class="invalid-feedback">
                                            Моля въведете валиден имейл адрес.
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label for="department_id" class="form-label">
                                            <i class="bi bi-building me-2"></i>Катедра *
                                        </label>
                                        <select class="form-control" 
                                                id="department_id" 
                                                name="department_id" 
                                                required>
                                            <option value="">Изберете катедра...</option>
                                            <?php foreach ($departments as $dept): ?>
                                                <option value="<?php echo $dept['id']; ?>" 
                                                        <?php echo (isset($teacherData['department_id']) && $teacherData['department_id'] == $dept['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($dept['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Моля изберете катедра.
                                        </div>
                                    </div>
                                    <?php if ($mode === 'edit' && !empty($teacherData['hire_date'])): ?>
                                        <div class="mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-calendar me-2"></i>Дата на назначаване
                                            </label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   value="<?php echo date('d.m.Y', strtotime($teacherData['hire_date'])); ?>"
                                                   readonly>
                                            <small class="text-muted">Датата на назначаване не може да се променя.</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-2"></i>
                                    <?php echo $mode === 'edit' ? 'Запази промените' : 'Създай преподавател'; ?>
                                </button>
                                <a href="/index.php" class="btn btn-secondary ms-2">
                                    <i class="bi bi-arrow-left me-2"></i>
                                    Назад
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';
            window.addEventListener('load', function() {
                var forms = document.getElementsByClassName('needs-validation');
                var validation = Array.prototype.filter.call(forms, function(form) {
                    form.addEventListener('submit', function(event) {
                        if (form.checkValidity() === false) {
                            event.preventDefault();
                            event.stopPropagation();
                        }
                        form.classList.add('was-validated');
                    }, false);
                });
            }, false);
        })();
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
