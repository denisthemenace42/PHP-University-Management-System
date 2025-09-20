-- Add more sample data without conflicts
USE university;

-- Add more students with unique faculty numbers and emails
INSERT INTO students (faculty_number, first_name, middle_name, last_name, specialty_id, course, email, address, phone, birth_date, enrollment_date, status) VALUES
('121220041', 'Андрей', 'Петров', 'Стоянов', 1, 2, 'a.stoyanov@student.university.bg', 'ул. Витоша 123, София', '+359887111111', '2003-06-15', '2021-09-15', 'active'),
('121220042', 'Биляна', 'Георгиева', 'Петрова', 1, 3, 'b.petrova@student.university.bg', 'бул. Цариградско шосе 45, София', '+359887222222', '2002-09-20', '2020-09-15', 'active'),
('121220043', 'Владимир', 'Стоянов', 'Димитров', 2, 1, 'v.dimitrov@student.university.bg', 'ул. Славянска 67, Пловдив', '+359887333333', '2004-03-10', '2022-09-15', 'active'),
('121220044', 'Галина', 'Николова', 'Стоянова', 2, 4, 'g.stoyanova@student.university.bg', 'ул. Марица 89, Пловдив', '+359887444444', '2001-11-25', '2019-09-15', 'active'),
('121220045', 'Димитър', 'Димитров', 'Николов', 3, 2, 'd.nikolov@student.university.bg', 'ул. Дунав 12, Варна', '+359887555555', '2003-04-18', '2021-09-15', 'active'),
('121220046', 'Емилия', 'Петрова', 'Георгиева', 3, 1, 'e.georgieva@student.university.bg', 'ул. Приморски 34, Варна', '+359887666666', '2004-08-05', '2022-09-15', 'active'),
('121220047', 'Жоро', 'Георгиев', 'Стоянов', 4, 3, 'zh.stoyanov@student.university.bg', 'ул. България 56, Бургас', '+359887777777', '2002-12-12', '2020-09-15', 'active'),
('121220048', 'Зорница', 'Стоянова', 'Димитрова', 4, 2, 'z.dimitrova@student.university.bg', 'ул. Славейков 78, Бургас', '+359887888888', '2003-07-30', '2021-09-15', 'active'),
('121220049', 'Иван', 'Николов', 'Петров', 5, 1, 'i.petrov@student.university.bg', 'ул. Левски 90, Русе', '+359887999999', '2004-01-15', '2022-09-15', 'active'),
('121220050', 'Йоана', 'Димитрова', 'Николова', 5, 4, 'yo.nikolova@student.university.bg', 'ул. Хан Крум 45, Русе', '+359887000000', '2001-05-22', '2019-09-15', 'active');

-- Add more disciplines with unique codes
INSERT INTO disciplines (name, code, semester, teacher_id, credits, hours_per_week, type, description) VALUES
('Advanced Programming', 'CS103', 3, 1, 5, 3, 'mandatory', 'Advanced programming techniques and design patterns'),
('Computer Architecture', 'CS203', 5, 2, 4, 3, 'mandatory', 'Computer organization and architecture'),
('Software Testing', 'CS303', 7, 1, 3, 2, 'elective', 'Software testing methodologies and tools'),
('Project Management', 'CS403', 8, 2, 4, 3, 'elective', 'IT project management and methodologies'),
('Numerical Methods', 'MA103', 3, 3, 4, 3, 'mandatory', 'Numerical analysis and computational methods'),
('Differential Equations', 'MA203', 5, 3, 4, 3, 'mandatory', 'Ordinary and partial differential equations'),
('Quantum Physics', 'PH103', 3, 4, 4, 3, 'mandatory', 'Introduction to quantum mechanics'),
('Electronics', 'PH203', 5, 4, 4, 3, 'mandatory', 'Electronic circuits and devices'),
('Digital Signal Processing', 'CE102', 4, 5, 4, 3, 'mandatory', 'Signal processing algorithms and applications'),
('Embedded Systems', 'CE202', 6, 5, 4, 3, 'mandatory', 'Microcontroller programming and embedded design');

-- Add grades for the new students and disciplines
INSERT INTO grades (student_id, discipline_id, grade, date, exam_type, notes) VALUES
-- Grades for new students (IDs 7-16) in existing disciplines
(7, 1, 4.75, '2023-01-20', 'written', 'Good programming foundation'),
(7, 2, 4.50, '2023-06-15', 'written', 'Solid algorithmic thinking'),
(7, 3, 4.25, '2023-06-20', 'practical', 'Good database skills'),

(8, 1, 5.25, '2023-01-20', 'written', 'Excellent programming abilities'),
(8, 2, 5.00, '2023-06-15', 'written', 'Strong algorithmic approach'),
(8, 3, 4.75, '2023-06-20', 'practical', 'Good database understanding'),

(9, 1, 4.00, '2023-01-20', 'written', 'Adequate programming skills'),
(9, 2, 3.75, '2023-06-15', 'written', 'Developing algorithmic thinking'),
(9, 3, 4.00, '2023-06-20', 'practical', 'Basic database knowledge'),

(10, 1, 5.50, '2023-01-20', 'written', 'Outstanding programming skills'),
(10, 2, 5.25, '2023-06-15', 'written', 'Excellent algorithmic problem solving'),
(10, 3, 5.00, '2023-06-20', 'practical', 'Strong database design'),

(11, 1, 4.25, '2023-01-20', 'written', 'Good programming foundation'),
(11, 2, 4.00, '2023-06-15', 'written', 'Solid algorithmic understanding'),
(11, 3, 4.50, '2023-06-20', 'practical', 'Good database implementation'),

(12, 1, 3.75, '2023-01-20', 'written', 'Basic programming skills'),
(12, 2, 3.50, '2023-06-15', 'written', 'Developing algorithmic thinking'),
(12, 3, 3.75, '2023-06-20', 'practical', 'Learning database concepts'),

(13, 1, 4.50, '2023-01-20', 'written', 'Good programming abilities'),
(13, 2, 4.25, '2023-06-15', 'written', 'Solid algorithmic approach'),
(13, 3, 4.00, '2023-06-20', 'practical', 'Adequate database skills'),

(14, 1, 5.00, '2023-01-20', 'written', 'Excellent programming skills'),
(14, 2, 4.75, '2023-06-15', 'written', 'Strong algorithmic thinking'),
(14, 3, 5.25, '2023-06-20', 'practical', 'Outstanding database design'),

(15, 1, 4.75, '2023-01-20', 'written', 'Very good programming skills'),
(15, 2, 4.50, '2023-06-15', 'written', 'Good algorithmic understanding'),
(15, 3, 4.25, '2023-06-20', 'practical', 'Solid database knowledge'),

(16, 1, 4.00, '2023-01-20', 'written', 'Adequate programming skills'),
(16, 2, 3.75, '2023-06-15', 'written', 'Basic algorithmic thinking'),
(16, 3, 4.00, '2023-06-20', 'practical', 'Good database understanding'),

-- Additional grades for existing students in new disciplines
(1, 17, 5.25, '2023-06-20', 'written', 'Excellent advanced programming skills'),
(1, 18, 5.00, '2023-06-15', 'written', 'Strong computer architecture understanding'),
(1, 19, 4.75, '2023-06-10', 'practical', 'Good software testing knowledge'),

(2, 17, 4.50, '2023-06-20', 'written', 'Good advanced programming skills'),
(2, 18, 4.25, '2023-06-15', 'written', 'Solid computer architecture foundation'),
(2, 19, 4.00, '2023-06-10', 'practical', 'Basic software testing understanding'),

(3, 17, 5.50, '2023-06-20', 'written', 'Outstanding advanced programming abilities'),
(3, 18, 5.25, '2023-06-15', 'written', 'Excellent computer architecture knowledge'),
(3, 19, 5.00, '2023-06-10', 'practical', 'Strong software testing skills'),

(4, 17, 3.75, '2023-06-20', 'written', 'Basic advanced programming understanding'),
(4, 18, 3.50, '2023-06-15', 'written', 'Developing computer architecture knowledge'),
(4, 19, 3.75, '2023-06-10', 'practical', 'Learning software testing concepts'),

(5, 17, 4.25, '2023-06-20', 'written', 'Good advanced programming foundation'),
(5, 18, 4.00, '2023-06-15', 'written', 'Solid computer architecture understanding'),
(5, 19, 4.25, '2023-06-10', 'practical', 'Adequate software testing knowledge'),

(6, 17, 4.75, '2023-06-20', 'written', 'Very good advanced programming skills'),
(6, 18, 4.50, '2023-06-15', 'written', 'Good computer architecture knowledge'),
(6, 19, 4.75, '2023-06-10', 'practical', 'Strong software testing understanding'),

-- Some grades in mathematics disciplines
(1, 21, 5.75, '2023-06-15', 'written', 'Excellent numerical methods understanding'),
(2, 21, 5.00, '2023-06-15', 'written', 'Good numerical methods knowledge'),
(3, 21, 5.50, '2023-06-15', 'written', 'Strong numerical methods skills'),
(7, 21, 4.75, '2023-06-15', 'written', 'Solid numerical methods foundation'),
(8, 21, 5.25, '2023-06-15', 'written', 'Excellent numerical methods abilities'),

(1, 22, 5.50, '2023-06-20', 'written', 'Strong differential equations understanding'),
(2, 22, 4.75, '2023-06-20', 'written', 'Good differential equations knowledge'),
(3, 22, 5.25, '2023-06-20', 'written', 'Excellent differential equations skills'),
(7, 22, 4.50, '2023-06-20', 'written', 'Solid differential equations foundation'),
(8, 22, 5.00, '2023-06-20', 'written', 'Strong differential equations abilities'),

-- Some grades in physics disciplines
(4, 23, 4.75, '2023-06-15', 'written', 'Good quantum physics understanding'),
(5, 23, 5.00, '2023-06-15', 'written', 'Strong quantum physics knowledge'),
(9, 23, 4.25, '2023-06-15', 'written', 'Basic quantum physics understanding'),
(10, 23, 5.25, '2023-06-15', 'written', 'Excellent quantum physics abilities'),

(4, 24, 4.50, '2023-06-20', 'written', 'Good electronics foundation'),
(5, 24, 4.75, '2023-06-20', 'written', 'Solid electronics understanding'),
(9, 24, 4.00, '2023-06-20', 'written', 'Basic electronics knowledge'),
(10, 24, 5.00, '2023-06-20', 'written', 'Strong electronics skills'),

-- Some grades in computer engineering disciplines
(11, 25, 4.50, '2023-06-15', 'practical', 'Good digital signal processing understanding'),
(12, 25, 4.25, '2023-06-15', 'practical', 'Solid digital signal processing knowledge'),
(13, 25, 4.75, '2023-06-15', 'practical', 'Strong digital signal processing skills'),
(14, 25, 5.00, '2023-06-15', 'practical', 'Excellent digital signal processing abilities'),

(11, 26, 4.25, '2023-06-20', 'practical', 'Good embedded systems foundation'),
(12, 26, 4.00, '2023-06-20', 'practical', 'Basic embedded systems understanding'),
(13, 26, 4.50, '2023-06-20', 'practical', 'Solid embedded systems knowledge'),
(14, 26, 4.75, '2023-06-20', 'practical', 'Strong embedded systems skills');
