<?php
require_once 'Auth.php';
if (Auth::isLoggedIn()) {
    Auth::redirectToDashboard();
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    if (empty($username) || empty($password)) {
        $error = 'Моля въведете потребителско име и парола.';
    } else {
        $user = Auth::login($username, $password);
        if ($user) {
            header("Location: /index.php");
            exit;
        } else {
            $error = 'Невалидно потребителско име или парола.';
        }
    }
}
if (isset($_GET['logout'])) {
    $success = 'Излязохте успешно от системата.';
}
?>
<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход - Университетска система</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            border: none;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px 20px 0 0;
            padding: 2rem;
            text-align: center;
        }
        .login-body {
            padding: 2rem;
        }
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .btn-login {
            background: linear-gradient(45deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        .demo-credentials {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1rem;
            margin-top: 1rem;
        }
        .demo-credential {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #dee2e6;
        }
        .demo-credential:last-child {
            border-bottom: none;
        }
        .role-badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
        }
        .badge-admin {
            background-color: #dc3545;
            color: white;
        }
        .badge-teacher {
            background-color: #198754;
            color: white;
        }
        .badge-student {
            background-color: #0d6efd;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card login-card">
                    <div class="login-header">
                        <i class="bi bi-mortarboard display-4 mb-3"></i>
                        <h2 class="h4 mb-0">Университетска система</h2>
                        <p class="mb-0 opacity-75">Вход в системата</p>
                    </div>
                    <div class="login-body">
                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="bi bi-check-circle-fill me-2"></i>
                                <?php echo htmlspecialchars($success); ?>
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
                        <form method="POST" action="" class="needs-validation" novalidate>
                            <div class="mb-3">
                                <label for="username" class="form-label">
                                    <i class="bi bi-person me-2"></i>Потребителско име
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="username" 
                                       name="username"
                                       value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>"
                                       required
                                       placeholder="Въведете потребителско име">
                                <div class="invalid-feedback">
                                    Моля въведете потребителско име.
                                </div>
                            </div>
                            <div class="mb-4">
                                <label for="password" class="form-label">
                                    <i class="bi bi-lock me-2"></i>Парола
                                </label>
                                <input type="password" 
                                       class="form-control" 
                                       id="password" 
                                       name="password"
                                       required
                                       placeholder="Въведете парола">
                                <div class="invalid-feedback">
                                    Моля въведете парола.
                                </div>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary btn-login">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>
                                    Вход
                                </button>
                            </div>
                        </form>
                        <div class="demo-credentials">
                            <h6 class="mb-3">
                                <i class="bi bi-info-circle me-2"></i>
                                Демо потребители:
                            </h6>
                            <div class="demo-credential">
                                <div>
                                    <strong>admin</strong>
                                    <span class="role-badge badge-admin ms-2">Администратор</span>
                                </div>
                                <code>admin</code>
                            </div>
                            <div class="demo-credential">
                                <div>
                                    <strong>teacher</strong>
                                    <span class="role-badge badge-teacher ms-2">Преподавател</span>
                                </div>
                                <code>teacher</code>
                            </div>
                            <div class="demo-credential">
                                <div>
                                    <strong>student</strong>
                                    <span class="role-badge badge-student ms-2">Студент</span>
                                </div>
                                <code>student</code>
                            </div>
                        </div>
                        <div class="text-center mt-4">
                            <small class="text-muted">
                                <i class="bi bi-shield-check me-1"></i>
                                Сигурна връзка
                            </small>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <p class="text-white opacity-75 mb-0">
                        <i class="bi bi-mortarboard me-2"></i>
                        Университетска система v1.0
                    </p>
                </div>
            </div>
        </div>
    </div>
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
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('username').focus();
        });
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        document.querySelectorAll('.demo-credential').forEach(function(credential) {
            credential.addEventListener('click', function() {
                const username = this.querySelector('strong').textContent;
                const password = this.querySelector('code').textContent;
                document.getElementById('username').value = username;
                document.getElementById('password').value = password;
                this.style.backgroundColor = '#e3f2fd';
                setTimeout(() => {
                    this.style.backgroundColor = '';
                }, 1000);
            });
        });
    </script>
</body>
</html>
