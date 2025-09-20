<?php
require_once 'Database.php';
try {
    echo "<h2>Database Population Script</h2>\n";
    echo "<p>Starting database population...</p>\n";
    $db = Database::getInstance();
    $sqlFile = __DIR__ . '/populate_database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL file not found: $sqlFile");
    }
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        throw new Exception("Could not read SQL file");
    }
    echo "<p>SQL file loaded successfully. Executing queries...</p>\n";
    $sql = preg_replace('/--.*$/m', '', $sql); 
    $sql = preg_replace('/\/\*.*?\*\
    $statements = [];
    $currentStatement = '';
    $inString = false;
    $stringChar = '';
    for ($i = 0; $i < strlen($sql); $i++) {
        $char = $sql[$i];
        if (!$inString && ($char === '"' || $char === "'")) {
            $inString = true;
            $stringChar = $char;
        } elseif ($inString && $char === $stringChar) {
            $inString = false;
        } elseif (!$inString && $char === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
            continue;
        }
        $currentStatement .= $char;
    }
    if (!empty(trim($currentStatement))) {
        $statements[] = trim($currentStatement);
    }
    $statements = array_filter($statements, function($stmt) {
        $stmt = trim($stmt);
        return !empty($stmt) && 
               !preg_match('/^(USE|SELECT)/i', $stmt) &&
               !preg_match('/^SHOW/i', $stmt) &&
               !preg_match('/^CREATE VIEW/i', $stmt) &&
               !preg_match('/^CREATE PROCEDURE/i', $stmt) &&
               !preg_match('/^CREATE TRIGGER/i', $stmt) &&
               !preg_match('/^DELIMITER/i', $stmt) &&
               !preg_match('/^GRANT/i', $stmt) &&
               !preg_match('/^FLUSH/i', $stmt);
    });
    $executedCount = 0;
    $errorCount = 0;
    foreach ($statements as $statement) {
        if (empty(trim($statement))) {
            continue;
        }
        try {
            $db->executeQuery($statement);
            $executedCount++;
            if (preg_match('/INSERT INTO (\w+)/i', $statement, $matches)) {
                $table = $matches[1];
                $count = preg_match_all('/\s*\([^)]+\)\s*,?\s*$/m', $statement);
                echo "<p>✓ Inserted $count records into $table</p>\n";
            } elseif (preg_match('/CREATE TABLE (\w+)/i', $statement, $matches)) {
                $table = $matches[1];
                echo "<p>✓ Created table $table</p>\n";
            }
        } catch (Exception $e) {
            $errorCount++;
            echo "<p style='color: red;'>✗ Error executing statement: " . htmlspecialchars($e->getMessage()) . "</p>\n";
            echo "<p style='color: gray; font-size: 0.8em;'>Statement: " . htmlspecialchars(substr($statement, 0, 100)) . "...</p>\n";
        }
    }
    echo "<hr>\n";
    echo "<h3>Population Summary</h3>\n";
    echo "<p><strong>Statements executed:</strong> $executedCount</p>\n";
    echo "<p><strong>Errors encountered:</strong> $errorCount</p>\n";
    if ($errorCount === 0) {
        echo "<p style='color: green;'><strong>✓ Database populated successfully!</strong></p>\n";
    } else {
        echo "<p style='color: orange;'><strong>⚠ Database populated with some errors.</strong></p>\n";
    }
    echo "<hr>\n";
    echo "<h3>Current Database Statistics</h3>\n";
    $stats = [
        'students' => $db->select("SELECT COUNT(*) as count FROM students")[0]['count'],
        'teachers' => $db->select("SELECT COUNT(*) as count FROM teachers")[0]['count'],
        'disciplines' => $db->select("SELECT COUNT(*) as count FROM disciplines")[0]['count'],
        'grades' => $db->select("SELECT COUNT(*) as count FROM grades")[0]['count'],
        'specialties' => $db->select("SELECT COUNT(*) as count FROM specialties")[0]['count'],
        'departments' => $db->select("SELECT COUNT(*) as count FROM departments")[0]['count']
    ];
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr style='background-color: 
    foreach ($stats as $table => $count) {
        echo "<tr><td style='padding: 5px;'>" . ucfirst($table) . "</td><td style='padding: 5px; text-align: center;'>$count</td></tr>\n";
    }
    echo "</table>\n";
    echo "<hr>\n";
    echo "<h3>Sample Data Preview</h3>\n";
    echo "<h4>Recent Students:</h4>\n";
    $recentStudents = $db->select("
        SELECT s.faculty_number, s.first_name, s.last_name, sp.name as specialty, s.course 
        FROM students s 
        JOIN specialties sp ON s.specialty_id = sp.id 
        ORDER BY s.id DESC 
        LIMIT 5
    ");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr style='background-color: 
    foreach ($recentStudents as $student) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars($student['faculty_number']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars($student['specialty']) . "</td>";
        echo "<td style='padding: 5px; text-align: center;'>" . $student['course'] . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "<h4>Available Disciplines:</h4>\n";
    $disciplines = $db->select("
        SELECT d.name, d.code, d.semester, t.name as teacher_name 
        FROM disciplines d 
        JOIN teachers t ON d.teacher_id = t.id 
        ORDER BY d.semester, d.name 
        LIMIT 10
    ");
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr style='background-color: 
    foreach ($disciplines as $discipline) {
        echo "<tr>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars($discipline['name']) . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars($discipline['code']) . "</td>";
        echo "<td style='padding: 5px; text-align: center;'>" . $discipline['semester'] . "</td>";
        echo "<td style='padding: 5px;'>" . htmlspecialchars($discipline['teacher_name']) . "</td>";
        echo "</tr>\n";
    }
    echo "</table>\n";
    echo "<h4>Grade Statistics:</h4>\n";
    $gradeStats = $db->select("
        SELECT 
            COUNT(*) as total_grades,
            ROUND(AVG(grade), 2) as average_grade,
            MIN(grade) as min_grade,
            MAX(grade) as max_grade
        FROM grades
    ")[0];
    echo "<table border='1' style='border-collapse: collapse; margin: 10px 0;'>\n";
    echo "<tr style='background-color: 
    echo "<tr>";
    echo "<td style='padding: 5px; text-align: center;'>" . $gradeStats['total_grades'] . "</td>";
    echo "<td style='padding: 5px; text-align: center;'>" . $gradeStats['average_grade'] . "</td>";
    echo "<td style='padding: 5px; text-align: center;'>" . $gradeStats['min_grade'] . "</td>";
    echo "<td style='padding: 5px; text-align: center;'>" . $gradeStats['max_grade'] . "</td>";
    echo "</tr>\n";
    echo "</table>\n";
    echo "<hr>\n";
    echo "<p><a href='index.php'>← Back to Main Page</a></p>\n";
    echo "<p><a href='views/reports.php'>→ Go to Reports</a></p>\n";
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error:</h3>\n";
    echo "<p style='color: red;'>" . htmlspecialchars($e->getMessage()) . "</p>\n";
    echo "<p><a href='index.php'>← Back to Main Page</a></p>\n";
}
?>
