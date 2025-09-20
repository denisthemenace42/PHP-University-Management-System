-- MySQL Database Script for University Management System
-- Database: university
-- Normalized to 3rd Normal Form (3NF)

-- Create database
CREATE DATABASE IF NOT EXISTS university CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE university;

-- Drop tables if they exist (for clean recreation)
DROP TABLE IF EXISTS grades;
DROP TABLE IF EXISTS disciplines;
DROP TABLE IF EXISTS teachers;
DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS specialties;
DROP TABLE IF EXISTS departments;

-- Create departments table (for normalization)
CREATE TABLE departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create specialties table (for normalization)
CREATE TABLE specialties (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create students table
CREATE TABLE students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    faculty_number VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    specialty_id INT NOT NULL,
    course TINYINT NOT NULL CHECK (course BETWEEN 1 AND 6),
    email VARCHAR(100) NOT NULL UNIQUE,
    address TEXT,
    phone VARCHAR(20),
    birth_date DATE,
    enrollment_date DATE NOT NULL,
    status ENUM('active', 'inactive', 'graduated', 'expelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_faculty_number (faculty_number),
    INDEX idx_email (email),
    INDEX idx_specialty_course (specialty_id, course),
    INDEX idx_status (status),
    
    -- Foreign key constraint
    CONSTRAINT fk_students_specialty 
        FOREIGN KEY (specialty_id) REFERENCES specialties(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Create teachers table
CREATE TABLE teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    title ENUM('assistant', 'chief_assistant', 'associate_professor', 'professor') NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100) NOT NULL UNIQUE,
    department_id INT NOT NULL,
    hire_date DATE NOT NULL,
    salary DECIMAL(10,2),
    status ENUM('active', 'inactive', 'retired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_email (email),
    INDEX idx_department (department_id),
    INDEX idx_title (title),
    INDEX idx_status (status),
    
    -- Foreign key constraint
    CONSTRAINT fk_teachers_department 
        FOREIGN KEY (department_id) REFERENCES departments(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Create disciplines table
CREATE TABLE disciplines (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    semester TINYINT NOT NULL CHECK (semester BETWEEN 1 AND 12),
    teacher_id INT NOT NULL,
    credits TINYINT NOT NULL CHECK (credits BETWEEN 1 AND 10),
    hours_per_week TINYINT DEFAULT 2,
    type ENUM('mandatory', 'elective', 'optional') DEFAULT 'mandatory',
    description TEXT,
    prerequisites TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Indexes
    INDEX idx_code (code),
    INDEX idx_semester (semester),
    INDEX idx_teacher (teacher_id),
    INDEX idx_type (type),
    
    -- Foreign key constraint
    CONSTRAINT fk_disciplines_teacher 
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) 
        ON DELETE RESTRICT ON UPDATE CASCADE
);

-- Create grades table
CREATE TABLE grades (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    discipline_id INT NOT NULL,
    grade DECIMAL(3,2) NOT NULL CHECK (grade BETWEEN 2.00 AND 6.00),
    date DATE NOT NULL,
    exam_type ENUM('written', 'oral', 'practical', 'project', 'continuous_assessment') DEFAULT 'written',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Composite unique constraint to prevent duplicate grades for same student-discipline
    UNIQUE KEY uk_student_discipline (student_id, discipline_id),
    
    -- Indexes
    INDEX idx_student (student_id),
    INDEX idx_discipline (discipline_id),
    INDEX idx_date (date),
    INDEX idx_grade (grade),
    
    -- Foreign key constraints
    CONSTRAINT fk_grades_student 
        FOREIGN KEY (student_id) REFERENCES students(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_grades_discipline 
        FOREIGN KEY (discipline_id) REFERENCES disciplines(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
);

-- Insert sample data for departments
INSERT INTO departments (name, description) VALUES
('Computer Science', 'Department of Computer Science and Information Technologies'),
('Mathematics', 'Department of Mathematics and Statistics'),
('Physics', 'Department of Physics and Astronomy'),
('Engineering', 'Department of Engineering Sciences'),
('Business', 'Department of Business Administration');

-- Insert sample data for specialties
INSERT INTO specialties (name, code, description) VALUES
('Software Engineering', 'SE', 'Software development and engineering'),
('Computer Science', 'CS', 'Computer science and algorithms'),
('Information Systems', 'IS', 'Information systems and databases'),
('Applied Mathematics', 'AM', 'Applied mathematics and statistics'),
('Physics', 'PH', 'Theoretical and applied physics');

-- Insert sample data for teachers
INSERT INTO teachers (name, title, phone, email, department_id, hire_date, salary, status) VALUES
('Prof. Ivan Petrov', 'professor', '+359888123456', 'i.petrov@university.bg', 1, '2010-09-01', 3500.00, 'active'),
('Doc. Maria Georgieva', 'associate_professor', '+359888234567', 'm.georgieva@university.bg', 1, '2015-02-15', 2800.00, 'active'),
('As. Stoyan Dimitrov', 'assistant', '+359888345678', 's.dimitrov@university.bg', 2, '2020-09-01', 2200.00, 'active'),
('Prof. Elena Nikolova', 'professor', '+359888456789', 'e.nikolova@university.bg', 3, '2008-03-10', 3600.00, 'active'),
('Doc. Georgi Stoyanov', 'associate_professor', '+359888567890', 'g.stoyanov@university.bg', 4, '2012-09-01', 2900.00, 'active');

-- Insert sample data for students
INSERT INTO students (faculty_number, first_name, middle_name, last_name, specialty_id, course, email, address, phone, birth_date, enrollment_date, status) VALUES
('121220001', 'Александър', 'Петров', 'Иванов', 1, 3, 'a.ivanov@student.university.bg', 'ул. Витоша 15, София', '+359887123456', '2002-05-15', '2020-09-15', 'active'),
('121220002', 'Мария', 'Георгиева', 'Петрова', 2, 2, 'm.petrova@student.university.bg', 'бул. България 25, Пловдив', '+359887234567', '2003-03-22', '2021-09-15', 'active'),
('121220003', 'Стоян', 'Димитров', 'Николов', 1, 4, 's.nikolov@student.university.bg', 'ул. Раковски 8, Варна', '+359887345678', '2001-11-08', '2019-09-15', 'active'),
('121220004', 'Елена', 'Стоянова', 'Димитрова', 3, 1, 'e.dimitrova@student.university.bg', 'ул. Шипка 12, Бургас', '+359887456789', '2004-07-30', '2022-09-15', 'active'),
('121220005', 'Георги', 'Николов', 'Стоянов', 2, 3, 'g.stoyanov@student.university.bg', 'бул. Левски 33, Русе', '+359887567890', '2002-12-12', '2020-09-15', 'active');

-- Insert sample data for disciplines
INSERT INTO disciplines (name, code, semester, teacher_id, credits, hours_per_week, type, description) VALUES
('Programming Fundamentals', 'CS101', 1, 1, 6, 4, 'mandatory', 'Introduction to programming concepts and algorithms'),
('Data Structures and Algorithms', 'CS201', 3, 1, 5, 3, 'mandatory', 'Advanced data structures and algorithmic thinking'),
('Database Systems', 'CS301', 5, 2, 4, 3, 'mandatory', 'Relational databases and SQL'),
('Linear Algebra', 'MA101', 2, 3, 4, 3, 'mandatory', 'Vectors, matrices and linear transformations'),
('Physics I', 'PH101', 1, 4, 5, 4, 'mandatory', 'Classical mechanics and thermodynamics'),
('Software Engineering', 'SE401', 7, 2, 6, 4, 'mandatory', 'Software development methodologies and practices'),
('Web Development', 'CS302', 6, 1, 3, 2, 'elective', 'Modern web technologies and frameworks');

-- Insert sample data for grades
INSERT INTO grades (student_id, discipline_id, grade, date, exam_type, notes) VALUES
(1, 1, 5.50, '2021-01-20', 'written', 'Excellent understanding of programming concepts'),
(1, 2, 4.75, '2022-01-15', 'written', 'Good algorithmic thinking'),
(1, 3, 5.25, '2022-06-10', 'practical', 'Strong database design skills'),
(2, 1, 4.25, '2022-01-20', 'written', 'Good basic programming skills'),
(2, 4, 5.00, '2022-06-15', 'written', 'Solid mathematical foundation'),
(3, 1, 5.75, '2020-01-20', 'written', 'Outstanding programming abilities'),
(3, 2, 5.50, '2021-01-15', 'written', 'Excellent algorithmic problem solving'),
(3, 3, 5.00, '2021-06-10', 'practical', 'Good database implementation'),
(3, 6, 4.50, '2022-06-20', 'project', 'Well-structured software project'),
(4, 1, 3.50, '2023-01-20', 'written', 'Needs improvement in programming logic'),
(5, 1, 4.00, '2021-01-20', 'written', 'Adequate programming skills'),
(5, 4, 4.75, '2021-06-15', 'written', 'Strong mathematical abilities');

-- Create views for common queries

-- View for student information with specialty name
CREATE VIEW student_details AS
SELECT 
    s.id,
    s.faculty_number,
    CONCAT(s.first_name, ' ', IFNULL(s.middle_name, ''), ' ', s.last_name) AS full_name,
    sp.name AS specialty_name,
    s.course,
    s.email,
    s.phone,
    s.status
FROM students s
JOIN specialties sp ON s.specialty_id = sp.id;

-- View for teacher information with department
CREATE VIEW teacher_details AS
SELECT 
    t.id,
    t.name,
    t.title,
    t.email,
    t.phone,
    d.name AS department_name,
    t.status
FROM teachers t
JOIN departments d ON t.department_id = d.id;

-- View for discipline information with teacher name
CREATE VIEW discipline_details AS
SELECT 
    d.id,
    d.name,
    d.code,
    d.semester,
    d.credits,
    d.type,
    t.name AS teacher_name,
    t.title AS teacher_title
FROM disciplines d
JOIN teachers t ON d.teacher_id = t.id;

-- View for grades with student and discipline details
CREATE VIEW grade_report AS
SELECT 
    g.id,
    CONCAT(s.first_name, ' ', s.last_name) AS student_name,
    s.faculty_number,
    d.name AS discipline_name,
    d.code AS discipline_code,
    g.grade,
    g.date,
    g.exam_type
FROM grades g
JOIN students s ON g.student_id = s.id
JOIN disciplines d ON g.discipline_id = d.id;

-- Create stored procedures for common operations

DELIMITER //

-- Procedure to calculate student GPA
CREATE PROCEDURE GetStudentGPA(IN student_id INT)
BEGIN
    SELECT 
        s.faculty_number,
        CONCAT(s.first_name, ' ', s.last_name) AS student_name,
        ROUND(AVG(g.grade), 2) AS gpa,
        COUNT(g.id) AS total_grades
    FROM students s
    LEFT JOIN grades g ON s.id = g.student_id
    WHERE s.id = student_id
    GROUP BY s.id;
END //

-- Procedure to get students by specialty and course
CREATE PROCEDURE GetStudentsBySpecialtyAndCourse(IN specialty_name VARCHAR(100), IN course_num TINYINT)
BEGIN
    SELECT 
        s.faculty_number,
        CONCAT(s.first_name, ' ', s.last_name) AS full_name,
        s.email,
        s.phone,
        s.status
    FROM students s
    JOIN specialties sp ON s.specialty_id = sp.id
    WHERE sp.name = specialty_name AND s.course = course_num
    ORDER BY s.last_name, s.first_name;
END //

DELIMITER ;

-- Create triggers for data integrity

DELIMITER //

-- Trigger to validate grade range
CREATE TRIGGER validate_grade_before_insert
BEFORE INSERT ON grades
FOR EACH ROW
BEGIN
    IF NEW.grade < 2.00 OR NEW.grade > 6.00 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Grade must be between 2.00 and 6.00';
    END IF;
END //

-- Trigger to log grade updates
CREATE TRIGGER log_grade_update
AFTER UPDATE ON grades
FOR EACH ROW
BEGIN
    INSERT INTO grade_history (grade_id, old_grade, new_grade, changed_date)
    VALUES (NEW.id, OLD.grade, NEW.grade, NOW());
END //

DELIMITER ;

-- Create grade history table for audit trail
CREATE TABLE grade_history (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grade_id INT NOT NULL,
    old_grade DECIMAL(3,2),
    new_grade DECIMAL(3,2),
    changed_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    CONSTRAINT fk_grade_history_grade 
        FOREIGN KEY (grade_id) REFERENCES grades(id) 
        ON DELETE CASCADE
);

-- Grant appropriate permissions (example for application user)
-- CREATE USER 'university_app'@'localhost' IDENTIFIED BY 'secure_password';
-- GRANT SELECT, INSERT, UPDATE ON university.* TO 'university_app'@'localhost';
-- GRANT DELETE ON university.grades TO 'university_app'@'localhost';
-- FLUSH PRIVILEGES;

-- Display database structure summary
SELECT 'Database university created successfully!' AS status;
SHOW TABLES;

