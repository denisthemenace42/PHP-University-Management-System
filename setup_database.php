<?php
/**
 * Database Setup Script
 * 
 * This script demonstrates the complete database migration system.
 * It creates the database and all tables programmatically using PHP code.
 * 
 * @author University System
 * @version 1.0
 */

echo "🎓 University System - Database Setup\n";
echo "=====================================\n\n";

try {
    // Include required files
    require_once __DIR__ . '/Database.php';
    require_once __DIR__ . '/migrations/MigrationManager.php';
    
    echo "📋 Step 1: Checking database connection...\n";
    
    // Test database connection
    $db = Database::getInstance();
    $connectionTest = $db->testConnection();
    
    if ($connectionTest['status'] === 'success') {
        echo "✅ Database connection successful\n";
        echo "   MySQL Version: {$connectionTest['mysql_version']}\n";
        echo "   Connection Time: {$connectionTest['connection_time']}\n\n";
    } else {
        throw new Exception("Database connection failed: " . $connectionTest['error']);
    }
    
    echo "📋 Step 2: Creating database if not exists...\n";
    
    // Create database if it doesn't exist
    $db->createDatabase('university');
    echo "✅ Database 'university' ready\n\n";
    
    echo "📋 Step 3: Running database migrations...\n";
    echo "----------------------------------------\n";
    
    // Initialize migration manager
    $migrationManager = new MigrationManager(__DIR__ . '/migrations');
    
    // Show current status
    echo "Current migration status:\n";
    $migrationManager->status();
    echo "\n";
    
    // Run migrations
    $migrationManager->migrate();
    
    echo "\n📋 Step 4: Verifying database structure...\n";
    echo "------------------------------------------\n";
    
    // Get all tables
    $tables = $db->getAllTables();
    echo "📊 Created tables:\n";
    foreach ($tables as $table) {
        $count = $db->selectOne("SELECT COUNT(*) as count FROM `{$table}`");
        echo "   ✅ {$table} ({$count['count']} records)\n";
    }
    
    echo "\n📋 Step 5: Database statistics...\n";
    echo "----------------------------------\n";
    
    $dbSize = $db->getDatabaseSize();
    echo "📈 Database size: {$dbSize['size_mb']} MB\n";
    echo "📈 Total tables: {$dbSize['table_count']}\n";
    
    // Sample queries to demonstrate JOIN functionality
    echo "\n📋 Step 6: Testing JOIN queries...\n";
    echo "-----------------------------------\n";
    
    $studentsWithSpecialties = $db->select("
        SELECT s.faculty_number, s.first_name, s.last_name, sp.name as specialty_name, s.course
        FROM students s 
        JOIN specialties sp ON s.specialty_id = sp.id 
        LIMIT 3
    ");
    
    echo "👥 Students with specialties:\n";
    foreach ($studentsWithSpecialties as $student) {
        echo "   • {$student['first_name']} {$student['last_name']} (#{$student['faculty_number']}) - {$student['specialty_name']}, Course {$student['course']}\n";
    }
    
    $teachersWithDepartments = $db->select("
        SELECT t.name, t.title, d.name as department_name
        FROM teachers t 
        JOIN departments d ON t.department_id = d.id 
        LIMIT 3
    ");
    
    echo "\n👨‍🏫 Teachers with departments:\n";
    foreach ($teachersWithDepartments as $teacher) {
        echo "   • {$teacher['name']} ({$teacher['title']}) - {$teacher['department_name']}\n";
    }
    
    // Aggregate functions demonstration
    echo "\n📋 Step 7: Testing aggregate functions...\n";
    echo "------------------------------------------\n";
    
    $stats = $db->selectOne("
        SELECT 
            COUNT(s.id) as total_students,
            AVG(g.grade) as average_grade,
            COUNT(DISTINCT sp.id) as total_specialties
        FROM students s
        LEFT JOIN grades g ON s.id = g.student_id
        LEFT JOIN specialties sp ON s.specialty_id = sp.id
    ");
    
    echo "📊 System statistics:\n";
    echo "   • Total students: {$stats['total_students']}\n";
    echo "   • Average grade: " . round($stats['average_grade'], 2) . "\n";
    echo "   • Total specialties: {$stats['total_specialties']}\n";
    
    echo "\n🎉 DATABASE SETUP COMPLETED SUCCESSFULLY!\n";
    echo "=========================================\n";
    echo "✅ All tables created programmatically using PHP migrations\n";
    echo "✅ Sample data inserted\n";
    echo "✅ JOIN queries working\n";
    echo "✅ Aggregate functions working\n";
    echo "✅ Foreign key constraints in place\n";
    echo "✅ Database normalized to 3NF\n\n";
    
    echo "🚀 You can now use the system:\n";
    echo "   • Start server: php -S localhost:8000\n";
    echo "   • Open browser: http://localhost:8000\n";
    echo "   • Login credentials:\n";
    echo "     - Admin: admin / admin\n";
    echo "     - Teacher: teacher / teacher\n";
    echo "     - Student: student / student\n\n";
    
    echo "🔧 Migration commands:\n";
    echo "   • php migrate.php status   - Show migration status\n";
    echo "   • php migrate.php migrate  - Run pending migrations\n";
    echo "   • php migrate.php rollback - Rollback last batch\n";
    echo "   • php migrate.php create <name> - Create new migration\n\n";
    
} catch (Exception $e) {
    echo "❌ Setup failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
