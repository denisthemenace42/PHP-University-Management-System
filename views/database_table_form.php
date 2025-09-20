<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../models/TableManager.php';

// Require admin access
Auth::requireRole('admin', '/unauthorized.php');

$user = Auth::getUser();
$tableName = $_GET['table'] ?? null;
$action = $_GET['action'] ?? 'create';
$recordId = $_GET['id'] ?? null;

if (!$tableName) {
    header('Location: database_admin.php?error=' . urlencode('Таблицата не е указана.'));
    exit;
}

try {
    $tableManager = new TableManager($tableName);
} catch (Exception $e) {
    header('Location: database_admin.php?error=' . urlencode($e->getMessage()));
    exit;
}

$record = null;
$pageTitle = '';
$readonly = false;

switch ($action) {
    case 'create':
        $pageTitle = 'Добавяне на нов запис';
        break;
    case 'edit':
        $pageTitle = 'Редактиране на запис';
        if (!$recordId) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('ID на записа не е указано.'));
            exit;
        }
        $record = $tableManager->getRecord($recordId);
        if (!$record) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Записът не е намерен.'));
            exit;
        }
        break;
    case 'view':
        $pageTitle = 'Преглед на запис';
        $readonly = true;
        if (!$recordId) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('ID на записа не е указано.'));
            exit;
        }
        $record = $tableManager->getRecord($recordId);
        if (!$record) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Записът не е намерен.'));
            exit;
        }
        break;
    default:
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Невалидно действие.'));
        exit;
}

$structure = $tableManager->getTableStructure();
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle . ' - ' . $tableManager->getDisplayName()); ?> - Университетска система</title>
    
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
        .form-card {
            background: white;
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        .breadcrumb-item a {
            text-decoration: none;
        }
        .readonly-field {
            background-color: #f8f9fa;
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
                            <?php if ($action === 'create'): ?>
                                <i class="bi bi-plus-circle me-2"></i>
                            <?php elseif ($action === 'edit'): ?>
                                <i class="bi bi-pencil me-2"></i>
                            <?php else: ?>
                                <i class="bi bi-eye me-2"></i>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($pageTitle); ?>
                        </h1>
                        <p class="mb-0 opacity-75">
                            Таблица: <?php echo htmlspecialchars($tableManager->getDisplayName()); ?>
                        </p>
                    </div>
                    <div class="col-auto">
                        <a href="database_admin.php?table=<?php echo urlencode($tableName); ?>" class="btn btn-light">
                            <i class="bi bi-arrow-left me-1"></i>
                            Назад
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="../index.php">Начало</a></li>
                <li class="breadcrumb-item"><a href="database_admin.php">База данни</a></li>
                <li class="breadcrumb-item"><a href="database_admin.php?table=<?php echo urlencode($tableName); ?>"><?php echo htmlspecialchars($tableManager->getDisplayName()); ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($pageTitle); ?></li>
            </ol>
        </nav>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card form-card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <?php if ($action === 'view' && $record): ?>
                                Преглед на запис #<?php echo htmlspecialchars($record[$tableManager->getPrimaryKey()]); ?>
                            <?php elseif ($action === 'edit' && $record): ?>
                                Редактиране на запис #<?php echo htmlspecialchars($record[$tableManager->getPrimaryKey()]); ?>
                            <?php else: ?>
                                Нов запис
                            <?php endif; ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php if (!$readonly): ?>
                            <form method="POST" action="database_table_action.php">
                                <input type="hidden" name="table" value="<?php echo htmlspecialchars($tableName); ?>">
                                <input type="hidden" name="action" value="<?php echo htmlspecialchars($action); ?>">
                                <?php if ($recordId): ?>
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($recordId); ?>">
                                <?php endif; ?>
                        <?php endif; ?>

                        <div class="row">
                            <?php foreach ($structure as $column): 
                                $field = $column['Field'];
                                $value = $record[$field] ?? null;
                                
                                // Skip timestamps for display in readonly mode
                                if ($readonly && in_array($field, ['created_at', 'updated_at'])) {
                                    continue;
                                }
                                
                                // Show primary key only in view/edit mode
                                if ($field === $tableManager->getPrimaryKey() && $action === 'create') {
                                    continue;
                                }
                            ?>
                                <div class="col-md-6">
                                    <?php if ($readonly): ?>
                                        <!-- Readonly display -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">
                                                <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $field))); ?>
                                            </label>
                                            <div class="form-control readonly-field">
                                                <?php 
                                                $displayValue = $value;
                                                
                                                // Format special fields
                                                if ($field === 'password') {
                                                    $displayValue = '••••••••';
                                                } elseif (strpos($field, '_id') !== false && $field !== 'id') {
                                                    // Show foreign key display value
                                                    $options = $tableManager->getForeignKeyOptions($field);
                                                    foreach ($options as $option) {
                                                        if ($option['value'] == $value) {
                                                            $displayValue = $option['display'];
                                                            break;
                                                        }
                                                    }
                                                } elseif (in_array($field, ['created_at', 'updated_at']) && $value) {
                                                    $displayValue = date('d.m.Y H:i:s', strtotime($value));
                                                }
                                                
                                                echo htmlspecialchars($displayValue ?? '—');
                                                ?>
                                            </div>
                                        </div>
                                    <?php elseif ($field === $tableManager->getPrimaryKey()): ?>
                                        <!-- Primary key in edit mode -->
                                        <div class="mb-3">
                                            <label class="form-label fw-bold">ID</label>
                                            <div class="form-control readonly-field">
                                                <?php echo htmlspecialchars($value); ?>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <!-- Editable field -->
                                        <?php echo $tableManager->generateFormField($column, $value); ?>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (!$readonly): ?>
                            <div class="row mt-4">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="database_admin.php?table=<?php echo urlencode($tableName); ?>" 
                                           class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            Отказ
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <?php if ($action === 'create'): ?>
                                                <i class="bi bi-plus me-1"></i>
                                                Създай запис
                                            <?php else: ?>
                                                <i class="bi bi-save me-1"></i>
                                                Запази промени
                                            <?php endif; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            </form>
                        <?php else: ?>
                            <!-- Readonly mode actions -->
                            <div class="row mt-4">
                                <div class="col-12">
                                    <hr>
                                    <div class="d-flex justify-content-between">
                                        <a href="database_admin.php?table=<?php echo urlencode($tableName); ?>" 
                                           class="btn btn-secondary">
                                            <i class="bi bi-arrow-left me-1"></i>
                                            Назад
                                        </a>
                                        <div>
                                            <a href="?table=<?php echo urlencode($tableName); ?>&action=edit&id=<?php echo urlencode($recordId); ?>" 
                                               class="btn btn-warning">
                                                <i class="bi bi-pencil me-1"></i>
                                                Редактирай
                                            </a>
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="confirmDelete('<?php echo urlencode($recordId); ?>', '<?php echo htmlspecialchars($record[$structure[1]['Field']] ?? $recordId, ENT_QUOTES); ?>')">
                                                <i class="bi bi-trash me-1"></i>
                                                Изтрий
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
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
                'database_table_action.php?table=<?php echo urlencode($tableName); ?>&action=delete&id=' + encodeURIComponent(id);
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
