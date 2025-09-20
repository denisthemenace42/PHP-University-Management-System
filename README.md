# Университетска система - Ръководство за инсталация и стартиране

## 📋 Изисквания

- **PHP 7.4+** с PDO MySQL разширение
- **MySQL 5.7+** или **MariaDB 10.3+**
- **Web сървър** (Apache, Nginx) или PHP built-in сървър

## 🚀 Инсталация

### 1. Инсталиране на PHP и MySQL (macOS)

```bash
# Инсталиране на Homebrew (ако не е инсталиран)
/bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)"

# Инсталиране на PHP
brew install php

# Инсталиране на MySQL
brew install mysql

# Стартиране на MySQL сервиса
brew services start mysql
```

### 2. Инсталиране на PHP и MySQL (Ubuntu/Debian)

```bash
# Обновяване на системата
sudo apt update

# Инсталиране на PHP и необходимите разширения
sudo apt install php php-mysql php-pdo php-mbstring php-json

# Инсталиране на MySQL
sudo apt install mysql-server

# Стартиране на MySQL
sudo systemctl start mysql
sudo systemctl enable mysql
```

### 3. Инсталиране на PHP и MySQL (Windows)

1. **XAMPP** (препоръчително): Изтеглете от https://www.apachefriends.org/
2. **WAMP**: Изтеглете от https://www.wampserver.com/
3. **MAMP**: Изтеглете от https://www.mamp.info/

## 🗄️ Настройка на базата данни

### 1. Създаване на базата данни

```bash
# Влизане в MySQL
mysql -u root -p

# Или ако няма парола
mysql -u root
```

```sql
-- Създаване на базата данни
CREATE DATABASE university CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Създаване на потребител (опционално)
CREATE USER 'university_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON university.* TO 'university_user'@'localhost';
FLUSH PRIVILEGES;

-- Използване на базата данни
USE university;
```

### 2. Импортиране на SQL схемата

```bash
# От терминала
mysql -u root -p university < university_database.sql

# Или от MySQL командния ред
mysql> source /path/to/university_database.sql;
```

## ⚙️ Конфигурация

### 1. Настройка на Database.php

Редактирайте `Database.php` и променете настройките за връзка:

```php
private const HOST = 'localhost';
private const DB_NAME = 'university';
private const USERNAME = 'root';           // Или 'university_user'
private const PASSWORD = '';               // Вашата парола
```

## 🏃‍♂️ Стартиране на проекта

### Метод 1: PHP Built-in сървър (препоръчително за развитие)

```bash
# От директорията на проекта
cd /Users/belqta/phpProject

# Стартиране на PHP сървър на порт 8000
php -S localhost:8000

# Или на друг порт
php -S localhost:3000
```

Отворете браузър на: **http://localhost:8000**

### Метод 2: Apache/Nginx

1. Копирайте проекта в web root директорията:
   - **XAMPP**: `C:\xampp\htdocs\university` (Windows)
   - **MAMP**: `/Applications/MAMP/htdocs/university` (macOS)
   - **Apache**: `/var/www/html/university` (Linux)

2. Отворете браузър на: **http://localhost/university**


## 🧪 Тестване на инсталацията

### 1. Тест на връзката с базата данни

```bash
# Изпълнение на тест скрипт
php Database.php
```

Очакван изход:
```
=== Database Connection Test ===
✅ Database connection successful
📊 MySQL Version: 8.0.x
⏱️  Connection Time: XX.XX ms
🏠 Host: localhost
🗄️  Database: university
```

### 2. Тест на Student модела

```bash
# Изпълнение на тест скрипт
php models/Student.php
```

### 3. Проверка в браузър

1. Отидете на **http://localhost:8000**
2. Трябва да видите dashboard страницата
3. Тествайте създаване на студент
4. Проверете списъка със студенти

## 📁 Структура на проекта

```
phpProject/
├── index.php                    # Dashboard начална страница
├── Database.php                 # Database connection клас
├── university_database.sql      # SQL схема и примерни данни
├── controllers/
│   └── student_controller.php   # Student CRUD controller
├── models/
│   └── Student.php              # Student model клас
├── views/
│   ├── student_form.php         # Форма за студенти
│   └── students_list.php        # Списък със студенти
└── README.md                    # Това ръководство
```

## 🔧 Основни функционалности

### Dashboard (index.php)
- Преглед на статистики
- Връзки към основните функции
- Проверка на състоянието на системата

### Управление на студенти
- **Създаване**: `views/student_form.php?mode=create`
- **Редактиране**: `views/student_form.php?mode=edit&id=X`
- **Преглед**: `views/student_form.php?mode=view&id=X`
- **Списък**: `views/students_list.php`

### CRUD операции
- **POST** към `controllers/student_controller.php`
- Actions: `create`, `update`, `delete`
- Валидация и error handling
- Success/error съобщения

## 🐛 Отстраняване на проблеми

### Проблем: "Database connection failed"

**Решения:**
1. Проверете дали MySQL работи: `brew services list | grep mysql`
2. Проверете настройките в `Database.php`
3. Проверете дали базата данни съществува
4. Проверете потребителските права

### Проблем: "Class 'PDO' not found"

**Решения:**
1. Инсталирайте PHP PDO разширението
2. Ubuntu/Debian: `sudo apt install php-pdo php-mysql`
3. macOS: `brew install php` (включва PDO)

### Проблем: "Permission denied"

**Решения:**
1. Проверете правата на файловете: `chmod -R 755 /path/to/project`
2. Проверете ownership: `chown -R www-data:www-data /path/to/project`

### Проблем: Празна страница

**Решения:**
1. Проверете PHP error log: `tail -f /var/log/php_errors.log`
2. Включете error reporting в PHP:
   ```php
   ini_set('display_errors', 1);
   error_reporting(E_ALL);
   ```

## 📞 Поддръжка

При проблеми:
1. Проверете error logs
2. Тествайте връзката с базата данни
3. Уверете се, че всички зависимости са инсталирани
4. Проверете файловите права

## 🔒 Сигурност

За production среда:
1. Променете database паролите
2. Настройте HTTPS
3. Ограничете файловите права
4. Включете error logging (не показвайте errors на потребителите)
5. Валидирайте всички входни данни

## 📈 Следващи стъпки

- Добавяне на authentication система
- Създаване на teacher и discipline модели
- Имплементиране на grades функционалност
- API endpoints за mobile приложения
- Unit testing

---

**Версия:** 1.0.0  
**Дата:** <?php echo date('Y-m-d'); ?>  
**Автор:** University System Team
