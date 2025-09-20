<?php
class Database
{
    private const HOST = 'localhost';
    private const DB_NAME = 'university';
    private const USERNAME = 'root';
    private const PASSWORD = '';
    private const CHARSET = 'utf8mb4';
    private static $connection = null;
    private static $instance = null;
    private const OPTIONS = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    private function __construct() {}
    private function __clone() {}
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function connect(): PDO
    {
        if (self::$connection === null) {
            try {
                $dsn = sprintf(
                    "mysql:host=%s;dbname=%s;charset=%s",
                    self::HOST,
                    self::DB_NAME,
                    self::CHARSET
                );
                self::$connection = new PDO($dsn, self::USERNAME, self::PASSWORD, self::OPTIONS);
                error_log("Database connection established successfully to " . self::DB_NAME);
            } catch (PDOException $e) {
                error_log("Database connection failed: " . $e->getMessage());
                throw new Exception(
                    "Database connection failed. Please check your database configuration.",
                    500
                );
            } catch (Exception $e) {
                error_log("Unexpected error during database connection: " . $e->getMessage());
                throw new Exception("An unexpected error occurred while connecting to the database.", 500);
            }
        }
        return self::$connection;
    }
    public function getConnection(): ?PDO
    {
        return self::$connection;
    }
    public function isConnected(): bool
    {
        try {
            return self::$connection !== null && self::$connection->query('SELECT 1') !== false;
        } catch (PDOException $e) {
            return false;
        }
    }
    public function executeQuery(string $query, array $params = []): PDOStatement
    {
        try {
            $pdo = $this->connect();
            $statement = $pdo->prepare($query);
            $statement->execute($params);
            return $statement;
        } catch (PDOException $e) {
            error_log("Query execution failed: " . $e->getMessage() . " Query: " . $query);
            throw new Exception("Database query execution failed: " . $e->getMessage(), 500);
        }
    }
    public function select(string $query, array $params = []): array
    {
        try {
            $statement = $this->executeQuery($query, $params);
            return $statement->fetchAll();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function selectOne(string $query, array $params = [])
    {
        try {
            $statement = $this->executeQuery($query, $params);
            return $statement->fetch();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function insert(string $query, array $params = []): string
    {
        try {
            $this->executeQuery($query, $params);
            return $this->connect()->lastInsertId();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function update(string $query, array $params = []): int
    {
        try {
            $statement = $this->executeQuery($query, $params);
            return $statement->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function delete(string $query, array $params = []): int
    {
        try {
            $statement = $this->executeQuery($query, $params);
            return $statement->rowCount();
        } catch (Exception $e) {
            throw $e;
        }
    }
    public function beginTransaction(): bool
    {
        try {
            return $this->connect()->beginTransaction();
        } catch (PDOException $e) {
            error_log("Failed to begin transaction: " . $e->getMessage());
            throw new Exception("Failed to begin database transaction.", 500);
        }
    }
    public function commit(): bool
    {
        try {
            return $this->connect()->commit();
        } catch (PDOException $e) {
            error_log("Failed to commit transaction: " . $e->getMessage());
            throw new Exception("Failed to commit database transaction.", 500);
        }
    }
    public function rollback(): bool
    {
        try {
            return $this->connect()->rollback();
        } catch (PDOException $e) {
            error_log("Failed to rollback transaction: " . $e->getMessage());
            throw new Exception("Failed to rollback database transaction.", 500);
        }
    }
    public function inTransaction(): bool
    {
        return self::$connection !== null && self::$connection->inTransaction();
    }
    public function close(): void
    {
        self::$connection = null;
        error_log("Database connection closed.");
    }
    public function getConfig(): array
    {
        return [
            'host' => self::HOST,
            'database' => self::DB_NAME,
            'charset' => self::CHARSET,
            'username' => self::USERNAME,
            'password' => str_repeat('*', strlen(self::PASSWORD)) 
        ];
    }
    public function testConnection(): array
    {
        try {
            $startTime = microtime(true);
            $pdo = $this->connect();
            $endTime = microtime(true);
            $version = $pdo->query('SELECT VERSION() as version')->fetch()['version'];
            return [
                'status' => 'success',
                'message' => 'Database connection successful',
                'mysql_version' => $version,
                'connection_time' => round(($endTime - $startTime) * 1000, 2) . ' ms',
                'host' => self::HOST,
                'database' => self::DB_NAME
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Database connection failed',
                'error' => $e->getMessage(),
                'host' => self::HOST,
                'database' => self::DB_NAME
            ];
        }
    }
    public function __destruct()
    {
        $this->close();
    }
}
if (basename($_SERVER['PHP_SELF']) === 'Database.php') {
    echo "=== Database Connection Test ===\n";
    try {
        $db = Database::getInstance();
        $result = $db->testConnection();
        if ($result['status'] === 'success') {
            echo "✅ " . $result['message'] . "\n";
            echo "📊 MySQL Version: " . $result['mysql_version'] . "\n";
            echo "⏱️  Connection Time: " . $result['connection_time'] . "\n";
            echo "🏠 Host: " . $result['host'] . "\n";
            echo "🗄️  Database: " . $result['database'] . "\n";
            echo "\n=== Testing Simple Query ===\n";
            $tables = $db->select("SHOW TABLES");
            echo "📋 Found " . count($tables) . " tables in database:\n";
            foreach ($tables as $table) {
                echo "   - " . array_values($table)[0] . "\n";
            }
        } else {
            echo "❌ " . $result['message'] . "\n";
            echo "🚨 Error: " . $result['error'] . "\n";
        }
    } catch (Exception $e) {
        echo "❌ Exception: " . $e->getMessage() . "\n";
    }
    echo "\n=== Test Complete ===\n";
}
?>
