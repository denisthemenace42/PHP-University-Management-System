<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Student.php';
Auth::requireLogin('/login.php');
Auth::requireRole('student');
$user = Auth::getUser();
$studentData = [];
$success = '';
$error = '';
try {
    $studentModel = new Student();
    $studentData = $studentModel->getStudentById($user['student_id']);
    if (!$studentData) {
        throw new Exception("Студентски данни не са намерени.");
    }
} catch (Exception $e) {
    $error = "Грешка при зареждане на данни: " . $e->getMessage();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    try {
        $first_name = trim($_POST['first_name'] ?? '');
        $middle_name = trim($_POST['middle_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $address = trim($_POST['address'] ?? '');
        if (empty($first_name) || empty($last_name)) {
            throw new InvalidArgumentException("Името и фамилията са задължителни.");
        }
        if (empty($email)) {
            throw new InvalidArgumentException("Имейлът е задължителен.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Невалиден имейл адрес.");
        }
        $updateData = [
            'first_name' => $first_name,
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'email' => $email,
            'phone' => $phone,
            'address' => $address
        ];
        $result = $studentModel->updateStudent($user['student_id'], $updateData);
        if ($result) {
            $db = Database::getInstance();
            $updateUserSql = "UPDATE users SET 
                first_name = ?,
                last_name = ?,
                email = ?,
                phone = ?,
                updated_at = CURRENT_TIMESTAMP
            WHERE id = ?";
            $db->update($updateUserSql, [$first_name, $last_name, $email, $phone, $user['id']]);
            $success = "Профилът е обновен успешно.";
            $studentData = $studentModel->getStudentById($user['student_id']);
        } else {
            $error = "Неуспешно обновяване на профила.";
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Моят профил - Университетска система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
        }
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .profile-body {
            padding: 2rem;
        }
        .info-row {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            border-left: 4px solid 
        }
        .info-label {
            font-weight: 600;
            color: 
            margin-bottom: 0.5rem;
        }
        .info-value {
            color: 
            font-size: 1.1rem;
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
        .student-badge {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card profile-card">
                    <div class="profile-header">
                        <i class="bi bi-person-circle display-4 mb-3"></i>
                        <h2 class="h4 mb-0">Моят профил</h2>
                        <p class="mb-0 opacity-75">Студентски данни</p>
                        <div class="mt-3">
                            <span class="student-badge">
                                Ф№: <?php echo htmlspecialchars($studentData['faculty_number'] ?? ''); ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-body">
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
                        <?php if (!empty($studentData)): ?>
                            <div id="viewMode">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-person me-2"></i>Име
                                            </div>
                                            <div class="info-value" id="viewName">
                                                <?php echo htmlspecialchars($studentData['first_name'] . ' ' . $studentData['last_name']); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-award me-2"></i>Презиме
                                            </div>
                                            <div class="info-value" id="viewMiddleName">
                                                <?php echo htmlspecialchars($studentData['middle_name'] ?? 'Няма'); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-telephone me-2"></i>Телефон
                                            </div>
                                            <div class="info-value" id="viewPhone">
                                                <?php echo htmlspecialchars($studentData['phone'] ?? 'Няма'); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-geo-alt me-2"></i>Адрес
                                            </div>
                                            <div class="info-value" id="viewAddress">
                                                <?php echo htmlspecialchars($studentData['address'] ?? 'Няма'); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-envelope me-2"></i>Имейл
                                            </div>
                                            <div class="info-value" id="viewEmail">
                                                <?php echo htmlspecialchars($studentData['email']); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-mortarboard me-2"></i>Специалност
                                            </div>
                                            <div class="info-value">
                                                <?php echo htmlspecialchars($studentData['specialty_name']); ?>
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-calendar me-2"></i>Курс
                                            </div>
                                            <div class="info-value">
                                                <?php echo $studentData['course']; ?> курс
                                            </div>
                                        </div>
                                        <div class="info-row">
                                            <div class="info-label">
                                                <i class="bi bi-calendar-event me-2"></i>Дата на записване
                                            </div>
                                            <div class="info-value">
                                                <?php echo date('d.m.Y', strtotime($studentData['enrollment_date'])); ?>
                                            </div>
                                        </div>
                                        <?php if (!empty($studentData['birth_date'])): ?>
                                            <div class="info-row">
                                                <div class="info-label">
                                                    <i class="bi bi-calendar-date me-2"></i>Дата на раждане
                                                </div>
                                                <div class="info-value">
                                                    <?php echo date('d.m.Y', strtotime($studentData['birth_date'])); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="text-center mt-4">
                                    <button type="button" class="btn btn-primary" onclick="toggleEditMode()">
                                        <i class="bi bi-pencil me-2"></i>
                                        Редактирай профил
                                    </button>
                                    <a href="/index.php" class="btn btn-secondary ms-2">
                                        <i class="bi bi-arrow-left me-2"></i>
                                        Назад
                                    </a>
                                </div>
                            </div>
                            <div id="editMode" style="display: none;">
                                <form method="POST" action="" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="update_profile">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="first_name" class="form-label">
                                                    <i class="bi bi-person me-2"></i>Име *
                                                </label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="first_name" 
                                                       name="first_name"
                                                       value="<?php echo htmlspecialchars($studentData['first_name']); ?>"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Моля въведете име.
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="middle_name" class="form-label">
                                                    <i class="bi bi-award me-2"></i>Презиме
                                                </label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="middle_name" 
                                                       name="middle_name"
                                                       value="<?php echo htmlspecialchars($studentData['middle_name'] ?? ''); ?>">
                                            </div>
                                            <div class="mb-3">
                                                <label for="last_name" class="form-label">
                                                    <i class="bi bi-person me-2"></i>Фамилия *
                                                </label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       id="last_name" 
                                                       name="last_name"
                                                       value="<?php echo htmlspecialchars($studentData['last_name']); ?>"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Моля въведете фамилия.
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="phone" class="form-label">
                                                    <i class="bi bi-telephone me-2"></i>Телефон
                                                </label>
                                                <input type="tel" 
                                                       class="form-control" 
                                                       id="phone" 
                                                       name="phone"
                                                       value="<?php echo htmlspecialchars($studentData['phone'] ?? ''); ?>">
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
                                                       value="<?php echo htmlspecialchars($studentData['email']); ?>"
                                                       required>
                                                <div class="invalid-feedback">
                                                    Моля въведете валиден имейл адрес.
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label for="address" class="form-label">
                                                    <i class="bi bi-geo-alt me-2"></i>Адрес
                                                </label>
                                                <textarea class="form-control" 
                                                          id="address" 
                                                          name="address" 
                                                          rows="3"><?php echo htmlspecialchars($studentData['address'] ?? ''); ?></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <i class="bi bi-mortarboard me-2"></i>Специалност
                                                </label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       value="<?php echo htmlspecialchars($studentData['specialty_name']); ?>"
                                                       readonly>
                                                <small class="text-muted">Специалността не може да се променя.</small>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    <i class="bi bi-calendar me-2"></i>Курс
                                                </label>
                                                <input type="text" 
                                                       class="form-control" 
                                                       value="<?php echo $studentData['course']; ?> курс"
                                                       readonly>
                                                <small class="text-muted">Курсът не може да се променя.</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center mt-4">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-check-lg me-2"></i>
                                            Запази промените
                                        </button>
                                        <button type="button" class="btn btn-secondary ms-2" onclick="toggleEditMode()">
                                            <i class="bi bi-x-lg me-2"></i>
                                            Отказ
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleEditMode() {
            const viewMode = document.getElementById('viewMode');
            const editMode = document.getElementById('editMode');
            if (viewMode.style.display === 'none') {
                viewMode.style.display = 'block';
                editMode.style.display = 'none';
            } else {
                viewMode.style.display = 'none';
                editMode.style.display = 'block';
            }
        }
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
