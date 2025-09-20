<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Student.php';
Auth::requireLogin('/login.php');
Auth::requireRole('admin');
$student = null;
$specialties = [];
$mode = 'create'; 
$studentId = 0;
$errors = [];
$success = '';
if (isset($_GET['mode'])) {
    $mode = $_GET['mode'];
}
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $studentId = (int)$_GET['id'];
}
try {
    $db = Database::getInstance();
    $studentModel = new Student();
    $specialties = $db->select("SELECT id, name, code FROM specialties ORDER BY name");
    if (($mode === 'edit' || $mode === 'view') && $studentId > 0) {
        $student = $studentModel->getStudentById($studentId);
        if (!$student) {
            $errors[] = "Студентът не е намерен.";
            $mode = 'create';
        }
    }
} catch (Exception $e) {
    $errors[] = "Грешка при зареждане на данни: " . $e->getMessage();
}
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'created':
            $success = "Студентът е създаден успешно!";
            break;
        case 'updated':
            $success = "Студентът е обновен успешно!";
            break;
        case 'deleted':
            $success = "Студентът е изтрит успешно!";
            break;
    }
}
if (isset($_GET['error'])) {
    $errors[] = urldecode($_GET['error']);
}
$pageTitle = '';
switch ($mode) {
    case 'create':
        $pageTitle = 'Добавяне на нов студент';
        break;
    case 'edit':
        $pageTitle = 'Редактиране на студент';
        break;
    case 'view':
        $pageTitle = 'Преглед на студент';
        break;
}
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
            background-color: #f8f9fa;
        }
        .card {
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .card-header {
            background-color: #667eea;
            color: white;
        }
        .btn-primary {
            background-color: #667eea;
            border-color: #667eea;
        }
        .btn-primary:hover {
            background-color: #5a67d8;
            border-color: #5a67d8;
        }
        .required {
            color: red;
        }
        .form-label {
            font-weight: 500;
        }
        .alert {
            border-radius: 0.375rem;
        }
        .nav-pills .nav-link.active {
            background-color: #667eea;
        }
        .student-info {
            background-color: #f8f9fa;
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8 col-xl-6">
                <div class="card mb-4">
                    <div class="card-body">
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="../index.php" class="text-decoration-none">Начало</a></li>
                                <li class="breadcrumb-item"><a href="students_list.php" class="text-decoration-none">Студенти</a></li>
                                <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($pageTitle); ?></li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-0">
                            <?php
                            switch ($mode) {
                                case 'create':
                                    echo '<i class="bi bi-person-plus me-2"></i>Добавяне на нов студент';
                                    break;
                                case 'edit':
                                    echo '<i class="bi bi-pencil me-2"></i>Редактиране на студент';
                                    break;
                                case 'view':
                                    echo '<i class="bi bi-eye me-2"></i>Преглед на студент';
                                    break;
                            }
                            ?>
                        </h4>
                    </div>
                    <div class="card-body">
                        <?php if ($mode === 'view' && $student): ?>
                            <div class="student-info p-4 rounded mb-4">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h5><i class="bi bi-person me-2"></i>Лична информация</h5>
                                        <p><strong>Име:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . ($student['middle_name'] ? $student['middle_name'] . ' ' : '') . $student['last_name']); ?></p>
                                        <p><strong>Факултетен номер:</strong> <?php echo htmlspecialchars($student['faculty_number']); ?></p>
                                        <p><strong>Email:</strong> <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>"><?php echo htmlspecialchars($student['email']); ?></a></p>
                                        <?php if ($student['phone']): ?>
                                            <p><strong>Телефон:</strong> <a href="tel:<?php echo htmlspecialchars($student['phone']); ?>"><?php echo htmlspecialchars($student['phone']); ?></a></p>
                                        <?php endif; ?>
                                        <?php if ($student['birth_date']): ?>
                                            <p><strong>Дата на раждане:</strong> <?php echo date('d.m.Y', strtotime($student['birth_date'])); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <h5><i class="bi bi-book me-2"></i>Академична информация</h5>
                                        <p><strong>Специалност:</strong> <?php echo htmlspecialchars($student['specialty_name']); ?> (<?php echo htmlspecialchars($student['specialty_code']); ?>)</p>
                                        <p><strong>Курс:</strong> <?php echo $student['course']; ?></p>
                                        <p><strong>Статус:</strong> 
                                            <span class="badge <?php echo $student['status'] === 'active' ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php 
                                                switch ($student['status']) {
                                                    case 'active': echo 'Активен'; break;
                                                    case 'inactive': echo 'Неактивен'; break;
                                                    case 'graduated': echo 'Завършил'; break;
                                                    case 'expelled': echo 'Отчислен'; break;
                                                    default: echo ucfirst($student['status']);
                                                }
                                                ?>
                                            </span>
                                        </p>
                                        <p><strong>Дата на записване:</strong> <?php echo date('d.m.Y', strtotime($student['enrollment_date'])); ?></p>
                                        <?php if ($student['address']): ?>
                                            <p><strong>Адрес:</strong> <?php echo htmlspecialchars($student['address']); ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="student_form.php?mode=edit&id=<?php echo $student['id']; ?>" class="btn btn-primary">
                                    <i class="bi bi-pencil me-1"></i> Редактиране
                                </a>
                                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                    <i class="bi bi-trash me-1"></i> Изтриване
                                </button>
                                <a href="../index.php" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i> Назад към началото
                                </a>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="../controllers/student_controller.php" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="<?php echo $mode === 'edit' ? 'update' : 'create'; ?>">
                                <?php if ($mode === 'edit' && $student): ?>
                                    <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                                <?php endif; ?>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="faculty_number" class="form-label">
                                            Факултетен номер <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="faculty_number" 
                                               name="faculty_number"
                                               value="<?php echo $student ? htmlspecialchars($student['faculty_number']) : ''; ?>"
                                               required
                                               maxlength="20"
                                               pattern="[0-9]{6,12}"
                                               placeholder="например: 121220001">
                                        <div class="invalid-feedback">
                                            Моля въведете валиден факултетен номер (6-12 цифри).
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label">
                                            Email <span class="required">*</span>
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               id="email" 
                                               name="email"
                                               value="<?php echo $student ? htmlspecialchars($student['email']) : ''; ?>"
                                               required
                                               maxlength="100"
                                               placeholder="например: student@university.bg">
                                        <div class="invalid-feedback">
                                            Моля въведете валиден email адрес.
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="first_name" class="form-label">
                                            Име <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="first_name" 
                                               name="first_name"
                                               value="<?php echo $student ? htmlspecialchars($student['first_name']) : ''; ?>"
                                               required
                                               maxlength="50"
                                               pattern="[А-Яа-яA-Za-z\s]+"
                                               placeholder="Иван">
                                        <div class="invalid-feedback">
                                            Моля въведете име (само букви).
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="middle_name" class="form-label">Презиме</label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="middle_name" 
                                               name="middle_name"
                                               value="<?php echo $student ? htmlspecialchars($student['middle_name'] ?? '') : ''; ?>"
                                               maxlength="50"
                                               pattern="[А-Яа-яA-Za-z\s]*"
                                               placeholder="Петров">
                                        <div class="invalid-feedback">
                                            Презимето трябва да съдържа само букви.
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <label for="last_name" class="form-label">
                                            Фамилия <span class="required">*</span>
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               id="last_name" 
                                               name="last_name"
                                               value="<?php echo $student ? htmlspecialchars($student['last_name']) : ''; ?>"
                                               required
                                               maxlength="50"
                                               pattern="[А-Яа-яA-Za-z\s]+"
                                               placeholder="Иванов">
                                        <div class="invalid-feedback">
                                            Моля въведете фамилия (само букви).
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="specialty_id" class="form-label">
                                            Специалност <span class="required">*</span>
                                        </label>
                                        <select class="form-select" id="specialty_id" name="specialty_id" required>
                                            <option value="">Изберете специалност</option>
                                            <?php foreach ($specialties as $specialty): ?>
                                                <option value="<?php echo $specialty['id']; ?>"
                                                        <?php echo ($student && $student['specialty_id'] == $specialty['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($specialty['name'] . ' (' . $specialty['code'] . ')'); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Моля изберете специалност.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="course" class="form-label">
                                            Курс <span class="required">*</span>
                                        </label>
                                        <select class="form-select" id="course" name="course" required>
                                            <option value="">Изберете курс</option>
                                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                                <option value="<?php echo $i; ?>"
                                                        <?php echo ($student && $student['course'] == $i) ? 'selected' : ''; ?>>
                                                    <?php echo $i; ?> курс
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <div class="invalid-feedback">
                                            Моля изберете курс.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="phone" class="form-label">Телефон</label>
                                        <input type="tel" 
                                               class="form-control" 
                                               id="phone" 
                                               name="phone"
                                               value="<?php echo $student ? htmlspecialchars($student['phone'] ?? '') : ''; ?>"
                                               maxlength="20"
                                               pattern="[\+]?[0-9\s\-\(\)]+"
                                               placeholder="+359888123456">
                                        <div class="invalid-feedback">
                                            Моля въведете валиден телефонен номер.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="birth_date" class="form-label">Дата на раждане</label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="birth_date" 
                                               name="birth_date"
                                               value="<?php echo $student ? htmlspecialchars($student['birth_date'] ?? '') : ''; ?>"
                                               min="1950-01-01"
                                               max="<?php echo date('Y-m-d'); ?>">
                                        <div class="invalid-feedback">
                                            Моля въведете валидна дата на раждане.
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label for="enrollment_date" class="form-label">
                                            Дата на записване <span class="required">*</span>
                                        </label>
                                        <input type="date" 
                                               class="form-control" 
                                               id="enrollment_date" 
                                               name="enrollment_date"
                                               value="<?php echo $student ? htmlspecialchars($student['enrollment_date']) : date('Y-m-d'); ?>"
                                               required
                                               min="2000-01-01"
                                               max="<?php echo date('Y-m-d'); ?>">
                                        <div class="invalid-feedback">
                                            Моля въведете дата на записване.
                                        </div>
                                    </div>
                                    <?php if ($mode === 'edit'): ?>
                                        <div class="col-md-6">
                                            <label for="status" class="form-label">Статус</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="active" <?php echo ($student && $student['status'] === 'active') ? 'selected' : ''; ?>>Активен</option>
                                                <option value="inactive" <?php echo ($student && $student['status'] === 'inactive') ? 'selected' : ''; ?>>Неактивен</option>
                                                <option value="graduated" <?php echo ($student && $student['status'] === 'graduated') ? 'selected' : ''; ?>>Завършил</option>
                                                <option value="expelled" <?php echo ($student && $student['status'] === 'expelled') ? 'selected' : ''; ?>>Отчислен</option>
                                            </select>
                                        </div>
                                    <?php endif; ?>
                                    <div class="col-12">
                                        <label for="address" class="form-label">Адрес</label>
                                        <textarea class="form-control" 
                                                  id="address" 
                                                  name="address" 
                                                  rows="3"
                                                  maxlength="500"
                                                  placeholder="ул. Витоша 15, София 1000"><?php echo $student ? htmlspecialchars($student['address'] ?? '') : ''; ?></textarea>
                                        <div class="form-text">Максимум 500 символа</div>
                                    </div>
                                </div>
                                <div class="mt-4 d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-check-lg me-1"></i>
                                        <?php echo $mode === 'edit' ? 'Обновяване' : 'Създаване'; ?>
                                    </button>
                                    <?php if ($mode === 'edit' && $student): ?>
                                        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                            <i class="bi bi-trash me-1"></i> Изтриване
                                        </button>
                                    <?php endif; ?>
                                    <a href="../index.php" class="btn btn-secondary">
                                        <i class="bi bi-arrow-left me-1"></i> Отказ
                                    </a>
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise me-1"></i> Изчистване
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if (($mode === 'edit' || $mode === 'view') && $student): ?>
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title" id="deleteModalLabel">
                            <i class="bi bi-exclamation-triangle me-2"></i>Потвърждение за изтриване
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Сигурни ли сте, че искате да изтриете студента?</p>
                        <div class="alert alert-warning">
                            <strong><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></strong><br>
                            Факултетен номер: <?php echo htmlspecialchars($student['faculty_number']); ?><br>
                            Email: <?php echo htmlspecialchars($student['email']); ?>
                        </div>
                        <p><small class="text-muted">Студентът ще бъде деактивиран, но данните му ще останат в системата.</small></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x me-1"></i> Отказ
                        </button>
                        <form method="POST" action="../controllers/student_controller.php" class="d-inline">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $student['id']; ?>">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-trash me-1"></i> Изтриване
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
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
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        document.getElementById('faculty_number')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
        document.getElementById('phone')?.addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9+\-\s\(\)]/g, '');
        });
    </script>
</body>
</html>
