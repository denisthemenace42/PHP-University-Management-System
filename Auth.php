<?php

require_once __DIR__ . '/models/User.php';

/**
 * Authentication System
 * 
 * Handles user sessions, login/logout, and authorization
 * 
 * @author University System
 * @version 1.0
 */
class Auth
{
    private static $user = null;
    
    /**
     * Start session if not already started
     */
    public static function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Login user
     * 
     * @param string $username Username
     * @param string $password Password
     * @return array|false User data or false if login fails
     */
    public static function login(string $username, string $password)
    {
        try {
            $userModel = new User();
            $user = $userModel->authenticate($username, $password);
            
            if ($user) {
                self::startSession();
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['user_data'] = $user;
                
                self::$user = $user;
                
                error_log("User logged in successfully: {$username} ({$user['role']})");
                return $user;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout user
     */
    public static function logout()
    {
        self::startSession();
        
        $username = $_SESSION['username'] ?? 'unknown';
        $role = $_SESSION['role'] ?? 'unknown';
        
        // Clear all session data
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
        
        self::$user = null;
        
        error_log("User logged out: {$username} ({$role})");
    }
    
    /**
     * Check if user is logged in
     * 
     * @return bool True if logged in, false otherwise
     */
    public static function isLoggedIn(): bool
    {
        self::startSession();
        
        return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
    }
    
    /**
     * Get current logged in user
     * 
     * @return array|null User data or null if not logged in
     */
    public static function getUser()
    {
        if (self::$user !== null) {
            return self::$user;
        }
        
        if (self::isLoggedIn()) {
            self::startSession();
            
            // Load user data from session or refresh from database
            if (isset($_SESSION['user_data'])) {
                self::$user = $_SESSION['user_data'];
                return self::$user;
            }
            
            // Refresh user data from database
            $userModel = new User();
            $user = $userModel->getUserById($_SESSION['user_id']);
            
            if ($user) {
                $_SESSION['user_data'] = $user;
                self::$user = $user;
                return $user;
            } else {
                // User no longer exists, logout
                self::logout();
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Get current user role
     * 
     * @return string|null User role or null if not logged in
     */
    public static function getRole(): ?string
    {
        $user = self::getUser();
        return $user ? $user['role'] : null;
    }
    
    /**
     * Get current user ID
     * 
     * @return int|null User ID or null if not logged in
     */
    public static function getUserId(): ?int
    {
        $user = self::getUser();
        return $user ? $user['id'] : null;
    }
    
    /**
     * Check if user has permission for specific action
     * 
     * @param string $action Action to check
     * @return bool True if allowed, false otherwise
     */
    public static function hasPermission(string $action): bool
    {
        $role = self::getRole();
        
        if (!$role) {
            return false;
        }
        
        return User::hasPermission($role, $action);
    }
    
    /**
     * Require user to be logged in
     * 
     * @param string $redirectUrl URL to redirect to if not logged in
     */
    public static function requireLogin(string $redirectUrl = '/login.php')
    {
        if (!self::isLoggedIn()) {
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    /**
     * Require specific role
     * 
     * @param string|array $requiredRoles Required role(s)
     * @param string $redirectUrl URL to redirect to if not authorized
     */
    public static function requireRole($requiredRoles, string $redirectUrl = '/unauthorized.php')
    {
        self::requireLogin();
        
        $userRole = self::getRole();
        
        if (is_string($requiredRoles)) {
            $requiredRoles = [$requiredRoles];
        }
        
        if (!in_array($userRole, $requiredRoles)) {
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    /**
     * Require specific permission
     * 
     * @param string $permission Required permission
     * @param string $redirectUrl URL to redirect to if not authorized
     */
    public static function requirePermission(string $permission, string $redirectUrl = '/unauthorized.php')
    {
        self::requireLogin();
        
        if (!self::hasPermission($permission)) {
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    /**
     * Check if user can access specific student data
     * 
     * @param int $studentId Student ID to check
     * @return bool True if user can access, false otherwise
     */
    public static function canAccessStudent(int $studentId): bool
    {
        $user = self::getUser();
        
        if (!$user) {
            return false;
        }
        
        // Admin can access all students
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Student can only access their own data
        if ($user['role'] === 'student') {
            return $user['student_id'] === $studentId;
        }
        
        // Teacher can access students they teach
        if ($user['role'] === 'teacher') {
            // For now, teachers can access all students
            // In a real system, you'd check if teacher teaches disciplines to this student
            return true;
        }
        
        return false;
    }
    
    /**
     * Check if user can access specific grade data
     * 
     * @param int $gradeId Grade ID to check
     * @return bool True if user can access, false otherwise
     */
    public static function canAccessGrade(int $gradeId): bool
    {
        $user = self::getUser();
        
        if (!$user) {
            return false;
        }
        
        // Admin can access all grades
        if ($user['role'] === 'admin') {
            return true;
        }
        
        // Students can access their own grades
        if ($user['role'] === 'student') {
            // Check if grade belongs to this student
            try {
                $db = Database::getInstance();
                $grade = $db->selectOne("SELECT student_id FROM grades WHERE id = ?", [$gradeId]);
                return $grade && $grade['student_id'] == $user['student_id'];
            } catch (Exception $e) {
                return false;
            }
        }
        
        // Teachers can access grades for disciplines they teach
        if ($user['role'] === 'teacher') {
            // For now, teachers can access all grades
            // In a real system, you'd check if teacher teaches the discipline for this grade
            return true;
        }
        
        return false;
    }
    
    /**
     * Get navigation menu items based on user role
     * 
     * @return array Menu items
     */
    public static function getNavigationMenu(): array
    {
        $user = self::getUser();
        
        if (!$user) {
            return [];
        }
        
        $menu = [
            'dashboard' => [
                'title' => 'Начало',
                'url' => '/index.php',
                'icon' => 'bi-house'
            ]
        ];
        
        switch ($user['role']) {
            case 'admin':
                $menu = array_merge($menu, [
                    'students' => [
                        'title' => 'Студенти',
                        'url' => '/views/students_list.php',
                        'icon' => 'bi-people'
                    ],
                    'teachers' => [
                        'title' => 'Преподаватели',
                        'url' => '/views/teachers_list.php',
                        'icon' => 'bi-person-badge'
                    ],
                    'disciplines' => [
                        'title' => 'Дисциплини',
                        'url' => '/views/disciplines_list.php',
                        'icon' => 'bi-book'
                    ],
                    'grades' => [
                        'title' => 'Оценки',
                        'url' => '/views/grades_list.php',
                        'icon' => 'bi-star'
                    ],
                    'users' => [
                        'title' => 'Потребители',
                        'url' => '/views/users_list.php',
                        'icon' => 'bi-person-gear'
                    ]
                ]);
                break;
                
            case 'teacher':
                $menu = array_merge($menu, [
                    'my_disciplines' => [
                        'title' => 'Мои дисциплини',
                        'url' => '/views/teacher_disciplines.php',
                        'icon' => 'bi-book'
                    ],
                    'my_students' => [
                        'title' => 'Мои студенти',
                        'url' => '/views/teacher_students.php',
                        'icon' => 'bi-people'
                    ],
                    'grades' => [
                        'title' => 'Оценки',
                        'url' => '/views/teacher_grades.php',
                        'icon' => 'bi-star'
                    ]
                ]);
                break;
                
            case 'student':
                $menu = array_merge($menu, [
                    'my_profile' => [
                        'title' => 'Моят профил',
                        'url' => '/views/student_profile.php',
                        'icon' => 'bi-person'
                    ],
                    'my_grades' => [
                        'title' => 'Мои оценки',
                        'url' => '/views/student_grades.php',
                        'icon' => 'bi-star'
                    ],
                    'my_disciplines' => [
                        'title' => 'Мои дисциплини',
                        'url' => '/views/student_disciplines.php',
                        'icon' => 'bi-book'
                    ]
                ]);
                break;
        }
        
        return $menu;
    }
    
    /**
     * Redirect to appropriate dashboard based on user role
     */
    public static function redirectToDashboard()
    {
        $user = self::getUser();
        
        if (!$user) {
            header("Location: /login.php");
            exit;
        }
        
        switch ($user['role']) {
            case 'admin':
                header("Location: /admin_dashboard.php");
                break;
            case 'teacher':
                header("Location: /teacher_dashboard.php");
                break;
            case 'student':
                header("Location: /student_dashboard.php");
                break;
            default:
                header("Location: /index.php");
        }
        
        exit;
    }
}

// Initialize session on every request
Auth::startSession();

?>
