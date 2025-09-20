<?php
require_once 'Database.php';
try {
    echo "<h2>Adding More Sample Data</h2>\n";
    $db = Database::getInstance();
    $sqlFile = __DIR__ . '/add_more_data.sql';
    $sql = file_get_contents($sqlFile);
    $sql = preg_replace('/--.*$/m', '', $sql);
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) {
            $stmt = trim($stmt);
            return !empty($stmt) && 
                   !preg_match('/^(USE|SELECT)/i', $stmt) &&
                   !preg_match('/^SHOW/i', $stmt);
        }
    );
    $executedCount = 0;
    $errorCount = 0;
    foreach ($statements as $statement) {
        if (empty(trim($statement))) continue;
        try {
            $db->executeQuery($statement);
            $executedCount++;
            if (preg_match('/INSERT INTO (\w+)/i', $statement, $matches)) {
                $table = $matches[1];
                echo "<p>✓ Added data to $table</p>\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error: " . htmlspecialchars($e->getMessage()) . "</p>\n";
        }
    }
    echo "<hr>\n";
    echo "<h3>Results</h3>\n";
    echo "<p><strong>Statements executed:</strong> $executedCount</p>\n";
    echo "<p><strong>Errors:</strong> $errorCount</p>\n";
    echo "<hr>\n";
    echo "<h3>Updated Database Statistics</h3>\n";
    $stats = [
        'students' => $db->select("SELECT COUNT(*) as count FROM students")[0]['count'],
        'teachers' => $db->select("SELECT COUNT(*) as count FROM teachers")[0]['count'],
        'disciplines' => $db->select("SELECT COUNT(*) as count FROM disciplines")[0]['count'],
        'grades' => $db->select("SELECT COUNT(*) as count FROM grades")[0]['count']
    ];
    echo "<table border='1' style='border-collapse: collapse;'>\n";
    echo "<tr style='background-color: 
    foreach ($stats as $table => $count) {
        echo "<tr><td style='padding: 5px;'>" . ucfirst($table) . "</td><td style='padding: 5px; text-align: center;'>$count</td></tr>\n";
    }
    echo "</table>\n";
    echo "<hr>\n";
    echo "<p><a href='index.php'>← Back to Main Page</a></p>\n";
    echo "<p><a href='views/reports.php'>→ Go to Reports</a></p>\n";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>\n";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>\n";
}
?>
