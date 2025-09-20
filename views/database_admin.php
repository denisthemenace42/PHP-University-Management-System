<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../models/TableManager.php';

// Require admin access
Auth::requireRole('admin', '/unauthorized.php');

$user = Auth::getUser();
$selectedTable = $_GET['table'] ?? null;
$action = $_GET['action'] ?? 'list';
$recordId = $_GET['id'] ?? null;

$success = $_GET['success'] ?? null;
$error = $_GET['error'] ?? null;

// Get all manageable tables
$manageableTables = TableManager::getManageableTables();

// If table is selected, create table manager
$tableManager = null;
if ($selectedTable && array_key_exists($selectedTable, $manageableTables)) {
    try {
        $tableManager = new TableManager($selectedTable);
    } catch (Exception $e) {
        $error = $e->getMessage();
        $selectedTable = null;
    }
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Администрация на база данни - Университетска система</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        .table-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: transform 0.2s;
        }
        .table-card:hover {
            transform: translateY(-2px);
        }
        .action-btn {
            margin: 0 2px;
        }
        .breadcrumb-item a {
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="card page-header">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col">
                        <h1 class="h3 mb-0">
                            <i class="bi bi-database me-2"></i>
                            Администрация на база данни
                        </h1>
                        <p class="mb-0 opacity-75">Управление на всички таблици в системата</p>
                    </div>
                    <div class="col-auto">
                        <a href="../index.php" class="btn btn-light">
                            <i class="bi bi-house me-1"></i>
                            Начало
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Начало</a></li>
                <li class="breadcrumb-item active">База данни</li>
                <?php if ($selectedTable): ?>
                    <li class="breadcrumb-item"><a href="?">Таблици</a></li>
                    <li class="breadcrumb-item active"><?php echo htmlspecialchars($manageableTables[$selectedTable]); ?></li>
                <?php endif; ?>
            </ol>
        </nav>

        <!-- Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i>
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-circle me-2"></i>
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!$selectedTable): ?>
            <!-- Table Selection -->
            <div class="row">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-table me-2"></i>
                                Избери таблица за управление
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <?php foreach ($manageableTables as $table => $displayName): ?>
                                    <div class="col-md-6 col-lg-4">
                                        <div class="card h-100 table-card">
                                            <div class="card-body text-center">
                                                <i class="bi bi-table display-4 text-primary mb-3"></i>
                                                <h6 class="card-title"><?php echo htmlspecialchars($displayName); ?></h6>
                                                <p class="card-text text-muted small">
                                                    Таблица: <code><?php echo htmlspecialchars($table); ?></code>
                                                </p>
                                                <a href="?table=<?php echo urlencode($table); ?>" class="btn btn-primary">
                                                    <i class="bi bi-gear me-1"></i>
                                                    Управлявай
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Table Management -->
            <div class="row">
                <div class="col-12">
                    <div class="card table-card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-table me-2"></i>
                                <?php echo htmlspecialchars($tableManager->getDisplayName()); ?>
                            </h5>
                            <div>
                                <a href="database_table_form.php?table=<?php echo urlencode($selectedTable); ?>&action=create" 
                                   class="btn btn-success">
                                    <i class="bi bi-plus me-1"></i>
                                    Добави запис
                                </a>
                                <a href="?" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left me-1"></i>
                                    Назад
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get records with pagination
                            $page = max(1, (int)($_GET['page'] ?? 1));
                            $perPage = 20;
                            $records = $tableManager->getAllRecords($page, $perPage);
                            $totalRecords = $tableManager->getTotalCount();
                            $totalPages = ceil($totalRecords / $perPage);
                            
                            if (empty($records)): ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-inbox display-1 text-muted"></i>
                                    <h4 class="text-muted mt-3">Няма записи</h4>
                                    <p class="text-muted">Започнете като добавите първия запис.</p>
                                    <a href="database_table_form.php?table=<?php echo urlencode($selectedTable); ?>&action=create" 
                                       class="btn btn-primary">
                                        <i class="bi bi-plus me-1"></i>
                                        Добави първия запис
                                    </a>
                                </div>
                            <?php else: ?>
                                <!-- Records Table -->
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <?php 
                                                $structure = $tableManager->getTableStructure();
                                                foreach ($structure as $column): 
                                                    if (in_array($column['Field'], ['created_at', 'updated_at'])) continue;
                                                ?>
                                                    <th><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $column['Field']))); ?></th>
                                                <?php endforeach; ?>
                                                <th width="150">Действия</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($records as $record): ?>
                                                <tr>
                                                    <?php foreach ($structure as $column): 
                                                        if (in_array($column['Field'], ['created_at', 'updated_at'])) continue;
                                                        $field = $column['Field'];
                                                        $value = $record[$field] ?? '';
                                                        
                                                        // Format display value
                                                        if (strlen($value) > 50) {
                                                            $value = substr($value, 0, 47) . '...';
                                                        }
                                                        if ($field === 'password') {
                                                            $value = '••••••••';
                                                        }
                                                    ?>
                                                        <td><?php echo htmlspecialchars($value); ?></td>
                                                    <?php endforeach; ?>
                                                    <td>
                                                        <div class="btn-group btn-group-sm" role="group">
                                                            <a href="database_table_form.php?table=<?php echo urlencode($selectedTable); ?>&action=view&id=<?php echo urlencode($record[$tableManager->getPrimaryKey()]); ?>" 
                                                               class="btn btn-outline-info action-btn" title="Преглед">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                            <a href="database_table_form.php?table=<?php echo urlencode($selectedTable); ?>&action=edit&id=<?php echo urlencode($record[$tableManager->getPrimaryKey()]); ?>" 
                                                               class="btn btn-outline-warning action-btn" title="Редактиране">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                            <button type="button" 
                                                                    class="btn btn-outline-danger action-btn" 
                                                                    title="Изтриване"
                                                                    onclick="confirmDelete('<?php echo urlencode($record[$tableManager->getPrimaryKey()]); ?>', '<?php echo htmlspecialchars($record[$structure[1]['Field']] ?? $record[$tableManager->getPrimaryKey()], ENT_QUOTES); ?>')">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <nav aria-label="Pagination">
                                        <ul class="pagination justify-content-center">
                                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?table=<?php echo urlencode($selectedTable); ?>&page=<?php echo $page - 1; ?>">
                                                    <i class="bi bi-chevron-left"></i>
                                                </a>
                                            </li>
                                            
                                            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                                    <a class="page-link" href="?table=<?php echo urlencode($selectedTable); ?>&page=<?php echo $i; ?>">
                                                        <?php echo $i; ?>
                                                    </a>
                                                </li>
                                            <?php endfor; ?>
                                            
                                            <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                                                <a class="page-link" href="?table=<?php echo urlencode($selectedTable); ?>&page=<?php echo $page + 1; ?>">
                                                    <i class="bi bi-chevron-right"></i>
                                                </a>
                                            </li>
                                        </ul>
                                    </nav>
                                <?php endif; ?>

                                <!-- Statistics -->
                                <div class="row mt-4">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            Показани <?php echo count($records); ?> от <?php echo $totalRecords; ?> записа
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-end">
                                        <small class="text-muted">
                                            Страница <?php echo $page; ?> от <?php echo $totalPages; ?>
                                        </small>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Потвърждение за изтриване</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Сигурни ли сте, че искате да изтриете записа <strong id="deleteItemName"></strong>?</p>
                    <p class="text-danger small">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Това действие не може да бъде отменено.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отказ</button>
                    <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
                        <i class="bi bi-trash me-1"></i>
                        Изтрий
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        function confirmDelete(id, name) {
            document.getElementById('deleteItemName').textContent = name;
            document.getElementById('confirmDeleteBtn').href = 
                'database_table_action.php?table=<?php echo urlencode($selectedTable ?? ''); ?>&action=delete&id=' + encodeURIComponent(id);
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
