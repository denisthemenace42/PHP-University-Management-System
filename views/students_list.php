<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../Database.php';
require_once __DIR__ . '/../models/Student.php';
Auth::requireLogin('/login.php');
Auth::requireRole('admin');
$students = [];
$totalStudents = 0;
$currentPage = 1;
$studentsPerPage = 10;
$totalPages = 1;
$filters = [];
$errors = [];
$filters = [
    'specialty_id' => $_GET['specialty_id'] ?? '',
    'course' => $_GET['course'] ?? '',
    'status' => $_GET['status'] ?? 'active',
    'search' => $_GET['search'] ?? ''
];
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$studentsPerPage = max(5, min(50, (int)($_GET['per_page'] ?? 10)));
try {
    $db = Database::getInstance();
    $studentModel = new Student();
    $specialties = $db->select("SELECT id, name, code FROM specialties ORDER BY name");
    $offset = ($currentPage - 1) * $studentsPerPage;
    $students = $studentModel->getAllStudents($filters, $studentsPerPage, $offset);
    $totalStudents = $studentModel->getStudentCount($filters);
    $totalPages = ceil($totalStudents / $studentsPerPage);
} catch (Exception $e) {
    $errors[] = "Грешка при зареждане на данни: " . $e->getMessage();
}
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Списък студенти - Университетска система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body { 
            background-color: #f8f9fa;
        }
        .card { 
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075); 
        }
        .table-responsive { 
            border-radius: 0.375rem; 
        }
        .student-avatar { 
            width: 40px; 
            height: 40px; 
        }
        .badge { 
            font-size: 0.75em; 
        }
        .btn-sm { 
            padding: 0.25rem 0.5rem; 
        }
        .pagination { 
            margin-bottom: 0; 
        }
        .filter-card { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .stats-card { 
            border-left: 4px solid #667eea;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="h3 mb-0"><i class="bi bi-people me-2"></i>Студенти</h1>
                        <p class="text-muted mb-0">Управление на студентски данни</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="../index.php" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Назад
                        </a>
                        <a href="student_form.php?mode=create" class="btn btn-primary">
                            <i class="bi bi-person-plus me-1"></i> Добави студент
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?php
                switch ($success) {
                    case 'created': echo 'Студентът е създаден успешно!'; break;
                    case 'updated': echo 'Студентът е обновен успешно!'; break;
                    case 'deleted': echo 'Студентът е изтрит успешно!'; break;
                    default: echo htmlspecialchars($success);
                }
                ?>
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
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <ul class="mb-0">
                    <?php foreach ($errors as $err): ?>
                        <li><?php echo htmlspecialchars($err); ?></li>
                    <?php endforeach; ?>
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-lg-3 mb-4">
                <div class="card filter-card text-white">
                    <div class="card-header border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-funnel me-2"></i>Филтри
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="" class="mb-0">
                            <div class="mb-3">
                                <label for="search" class="form-label">Търсене</label>
                                <input type="text" 
                                       class="form-control" 
                                       id="search" 
                                       name="search"
                                       value="<?php echo htmlspecialchars($filters['search']); ?>"
                                       placeholder="Име, фамилия, email...">
                            </div>
                            <div class="mb-3">
                                <label for="specialty_id" class="form-label">Специалност</label>
                                <select class="form-select" id="specialty_id" name="specialty_id">
                                    <option value="">Всички специалности</option>
                                    <?php foreach ($specialties as $specialty): ?>
                                        <option value="<?php echo $specialty['id']; ?>"
                                                <?php echo ($filters['specialty_id'] == $specialty['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($specialty['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="course" class="form-label">Курс</label>
                                <select class="form-select" id="course" name="course">
                                    <option value="">Всички курсове</option>
                                    <?php for ($i = 1; $i <= 4; $i++): ?>
                                        <option value="<?php echo $i; ?>"
                                                <?php echo ($filters['course'] == $i) ? 'selected' : ''; ?>>
                                            <?php echo $i; ?> курс
                                        </option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="status" class="form-label">Статус</label>
                                <select class="form-select" id="status" name="status">
                                    <option value="">Всички статуси</option>
                                    <option value="active" <?php echo ($filters['status'] === 'active') ? 'selected' : ''; ?>>Активни</option>
                                    <option value="inactive" <?php echo ($filters['status'] === 'inactive') ? 'selected' : ''; ?>>Неактивни</option>
                                    <option value="graduated" <?php echo ($filters['status'] === 'graduated') ? 'selected' : ''; ?>>Завършили</option>
                                    <option value="expelled" <?php echo ($filters['status'] === 'expelled') ? 'selected' : ''; ?>>Отчислени</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="per_page" class="form-label">На страница</label>
                                <select class="form-select" id="per_page" name="per_page">
                                    <option value="5" <?php echo ($studentsPerPage == 5) ? 'selected' : ''; ?>>5</option>
                                    <option value="10" <?php echo ($studentsPerPage == 10) ? 'selected' : ''; ?>>10</option>
                                    <option value="25" <?php echo ($studentsPerPage == 25) ? 'selected' : ''; ?>>25</option>
                                    <option value="50" <?php echo ($studentsPerPage == 50) ? 'selected' : ''; ?>>50</option>
                                </select>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-light">
                                    <i class="bi bi-search me-1"></i> Търси
                                </button>
                                <a href="students_list.php" class="btn btn-outline-light">
                                    <i class="bi bi-arrow-clockwise me-1"></i> Изчисти
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card mt-4 stats-card">
                    <div class="card-body">
                        <h6 class="card-title">
                            <i class="bi bi-bar-chart me-2"></i>Статистика
                        </h6>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="h4 text-primary mb-0"><?php echo $totalStudents; ?></div>
                                <small class="text-muted">Общо</small>
                            </div>
                            <div class="col-6">
                                <div class="h4 text-success mb-0"><?php echo count($students); ?></div>
                                <small class="text-muted">Показани</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-9">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>Списък студенти
                            <span class="badge bg-primary ms-2"><?php echo $totalStudents; ?></span>
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                                <i class="bi bi-printer"></i>
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="exportToCSV()">
                                <i class="bi bi-download"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <?php if (empty($students)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-people display-1 text-muted"></i>
                                <h4 class="text-muted mt-3">Няма намерени студенти</h4>
                                <p class="text-muted">Опитайте да промените филтрите или добавете нов студент.</p>
                                <a href="student_form.php?mode=create" class="btn btn-primary">
                                    <i class="bi bi-person-plus me-1"></i> Добави първия студент
                                </a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">
                                                <input type="checkbox" class="form-check-input" id="selectAll">
                                            </th>
                                            <th scope="col">Студент</th>
                                            <th scope="col">Факултетен №</th>
                                            <th scope="col">Email</th>
                                            <th scope="col">Специалност</th>
                                            <th scope="col">Курс</th>
                                            <th scope="col">Статус</th>
                                            <th scope="col" class="text-end">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($students as $student): ?>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" class="form-check-input student-checkbox" 
                                                           value="<?php echo $student['id']; ?>">
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="student-avatar bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="bi bi-person text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">
                                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                                            </div>
                                                            <?php if ($student['middle_name']): ?>
                                                                <small class="text-muted">
                                                                    <?php echo htmlspecialchars($student['middle_name']); ?>
                                                                </small>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <code class="bg-light px-2 py-1 rounded">
                                                        <?php echo htmlspecialchars($student['faculty_number']); ?>
                                                    </code>
                                                </td>
                                                <td>
                                                    <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                                                       class="text-decoration-none">
                                                        <?php echo htmlspecialchars($student['email']); ?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <div>
                                                        <?php echo htmlspecialchars($student['specialty_name'] ?? 'Неизвестна'); ?>
                                                    </div>
                                                    <?php if ($student['specialty_code']): ?>
                                                        <small class="text-muted">
                                                            <?php echo htmlspecialchars($student['specialty_code']); ?>
                                                        </small>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php echo $student['course']; ?> курс
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php
                                                    $statusClasses = [
                                                        'active' => 'bg-success',
                                                        'inactive' => 'bg-secondary',
                                                        'graduated' => 'bg-primary',
                                                        'expelled' => 'bg-danger'
                                                    ];
                                                    $statusLabels = [
                                                        'active' => 'Активен',
                                                        'inactive' => 'Неактивен',
                                                        'graduated' => 'Завършил',
                                                        'expelled' => 'Отчислен'
                                                    ];
                                                    $statusClass = $statusClasses[$student['status']] ?? 'bg-secondary';
                                                    $statusLabel = $statusLabels[$student['status']] ?? $student['status'];
                                                    ?>
                                                    <span class="badge <?php echo $statusClass; ?>">
                                                        <?php echo $statusLabel; ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="btn-group btn-group-sm">
                                                        <a href="student_form.php?mode=view&id=<?php echo $student['id']; ?>" 
                                                           class="btn btn-outline-info" 
                                                           title="Преглед">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                        <a href="student_form.php?mode=edit&id=<?php echo $student['id']; ?>" 
                                                           class="btn btn-outline-primary" 
                                                           title="Редактиране">
                                                            <i class="bi bi-pencil"></i>
                                                        </a>
                                                        <button type="button" 
                                                                class="btn btn-outline-danger" 
                                                                title="Изтриване"
                                                                onclick="confirmDelete(<?php echo $student['id']; ?>, '<?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name'], ENT_QUOTES); ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if ($totalPages > 1): ?>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-muted">
                                    Показани <?php echo count($students); ?> от <?php echo $totalStudents; ?> студенти
                                    (страница <?php echo $currentPage; ?> от <?php echo $totalPages; ?>)
                                </div>
                                <nav aria-label="Pagination">
                                    <ul class="pagination pagination-sm mb-0">
                                        <li class="page-item <?php echo ($currentPage <= 1) ? 'disabled' : ''; ?>">
                                            <a class="page-link" 
                                               href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage - 1])); ?>">
                                                <i class="bi bi-chevron-left"></i>
                                            </a>
                                        </li>
                                        <?php
                                        $startPage = max(1, $currentPage - 2);
                                        $endPage = min($totalPages, $currentPage + 2);
                                        if ($startPage > 1): ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => 1])); ?>">1</a>
                                            </li>
                                            <?php if ($startPage > 2): ?>
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                                <a class="page-link" 
                                                   href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>">
                                                    <?php echo $i; ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>
                                        <?php if ($endPage < $totalPages): ?>
                                            <?php if ($endPage < $totalPages - 1): ?>
                                                <li class="page-item disabled"><span class="page-link">...</span></li>
                                            <?php endif; ?>
                                            <li class="page-item">
                                                <a class="page-link" href="?<?php echo http_build_query(array_merge($_GET, ['page' => $totalPages])); ?>"><?php echo $totalPages; ?></a>
                                            </li>
                                        <?php endif; ?>
                                        <li class="page-item <?php echo ($currentPage >= $totalPages) ? 'disabled' : ''; ?>">
                                            <a class="page-link" 
                                               href="?<?php echo http_build_query(array_merge($_GET, ['page' => $currentPage + 1])); ?>">
                                                <i class="bi bi-chevron-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
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
                    <p>Сигурни ли сте, че искате да изтриете студента <strong id="studentName"></strong>?</p>
                    <div class="alert alert-warning">
                        <i class="bi bi-info-circle me-2"></i>
                        Студентът ще бъде деактивиран, но данните му ще останат в системата.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x me-1"></i> Отказ
                    </button>
                    <form method="POST" action="../controllers/student_controller.php" class="d-inline" id="deleteForm">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" id="deleteStudentId">
                        <button type="submit" class="btn btn-danger">
                            <i class="bi bi-trash me-1"></i> Изтриване
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });
        function confirmDelete(studentId, studentName) {
            document.getElementById('studentName').textContent = studentName;
            document.getElementById('deleteStudentId').value = studentId;
            const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
            modal.show();
        }
        function exportToCSV() {
            const table = document.querySelector('.table');
            let csv = [];
            const rows = table.querySelectorAll('tr');
            for (let i = 0; i < rows.length; i++) {
                const row = [], cols = rows[i].querySelectorAll('td, th');
                for (let j = 1; j < cols.length - 1; j++) { 
                    let data = cols[j].innerText.replace(/"/g, '""');
                    row.push('"' + data + '"');
                }
                csv.push(row.join(','));
            }
            const csvFile = new Blob([csv.join('\n')], { type: 'text/csv' });
            const downloadLink = document.createElement('a');
            downloadLink.download = 'students_' + new Date().toISOString().split('T')[0] + '.csv';
            downloadLink.href = window.URL.createObjectURL(csvFile);
            downloadLink.style.display = 'none';
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink);
        }
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
