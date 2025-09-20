<?php
require_once __DIR__ . '/../Auth.php';
require_once __DIR__ . '/../models/TableManager.php';

// Require admin access
Auth::requireRole('admin', '/unauthorized.php');

$tableName = $_POST['table'] ?? $_GET['table'] ?? null;
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$recordId = $_POST['id'] ?? $_GET['id'] ?? null;

if (!$tableName || !$action) {
    header('Location: database_admin.php?error=' . urlencode('Параметри липсват.'));
    exit;
}

try {
    $tableManager = new TableManager($tableName);
} catch (Exception $e) {
    header('Location: database_admin.php?error=' . urlencode($e->getMessage()));
    exit;
}

switch ($action) {
    case 'create':
        handleCreate($tableManager, $tableName);
        break;
    case 'edit':
        handleEdit($tableManager, $tableName, $recordId);
        break;
    case 'delete':
        handleDelete($tableManager, $tableName, $recordId);
        break;
    default:
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Невалидно действие.'));
        exit;
}

/**
 * Handle create action
 */
function handleCreate($tableManager, $tableName)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Невалидна заявка.'));
        exit;
    }
    
    try {
        $data = $_POST;
        
        // Remove action and table from data
        unset($data['action'], $data['table'], $data['id']);
        
        // Handle password hashing for users table
        if ($tableName === 'users' && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Remove empty values for optional fields
        $data = array_filter($data, function($value) {
            return $value !== '' && $value !== null;
        });
        
        $success = $tableManager->createRecord($data);
        
        if ($success) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&success=' . urlencode('Записът беше създаден успешно.'));
        } else {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Грешка при създаване на записа.'));
        }
    } catch (Exception $e) {
        header('Location: database_table_form.php?table=' . urlencode($tableName) . '&action=create&error=' . urlencode($e->getMessage()));
    }
    exit;
}

/**
 * Handle edit action
 */
function handleEdit($tableManager, $tableName, $recordId)
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Невалидна заявка.'));
        exit;
    }
    
    if (!$recordId) {
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('ID на записа липсва.'));
        exit;
    }
    
    try {
        $data = $_POST;
        
        // Remove action and table from data
        unset($data['action'], $data['table'], $data['id']);
        
        // Handle password hashing for users table
        if ($tableName === 'users' && !empty($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        } elseif ($tableName === 'users' && empty($data['password'])) {
            // Don't update password if empty
            unset($data['password']);
        }
        
        // Remove empty values for optional fields (but keep explicitly set empty strings for required fields)
        $filteredData = [];
        foreach ($data as $key => $value) {
            if ($value !== null) {
                $filteredData[$key] = $value;
            }
        }
        
        $success = $tableManager->updateRecord($recordId, $filteredData);
        
        if ($success) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&success=' . urlencode('Записът беше обновен успешно.'));
        } else {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Грешка при обновяване на записа или няма промени.'));
        }
    } catch (Exception $e) {
        header('Location: database_table_form.php?table=' . urlencode($tableName) . '&action=edit&id=' . urlencode($recordId) . '&error=' . urlencode($e->getMessage()));
    }
    exit;
}

/**
 * Handle delete action
 */
function handleDelete($tableManager, $tableName, $recordId)
{
    if (!$recordId) {
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('ID на записа липсва.'));
        exit;
    }
    
    try {
        // Check if record exists
        $record = $tableManager->getRecord($recordId);
        if (!$record) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Записът не съществува.'));
            exit;
        }
        
        $success = $tableManager->deleteRecord($recordId);
        
        if ($success) {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&success=' . urlencode('Записът беше изтрит успешно.'));
        } else {
            header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode('Грешка при изтриване на записа.'));
        }
    } catch (Exception $e) {
        // Handle foreign key constraint errors
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, 'foreign key constraint') !== false || strpos($errorMessage, 'Cannot delete') !== false) {
            $errorMessage = 'Записът не може да бъде изтрит защото се използва от други записи в системата.';
        }
        
        header('Location: database_admin.php?table=' . urlencode($tableName) . '&error=' . urlencode($errorMessage));
    }
    exit;
}
?>
