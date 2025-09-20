-- Additional Sample Data for University Management System
-- This script adds more students, teachers, disciplines, and grades

USE university;

-- Add more departments
INSERT INTO departments (name, description) VALUES
('Computer Engineering', 'Department of Computer Engineering and Robotics'),
('Economics', 'Department of Economics and Finance'),
('Psychology', 'Department of Psychology and Education'),
('Languages', 'Department of Foreign Languages'),
('History', 'Department of History and Archaeology');

-- Add more specialties
INSERT INTO specialties (name, code, description) VALUES
('Computer Engineering', 'CE', 'Computer engineering and hardware design'),
('Economics', 'EC', 'Economics and business administration'),
('Psychology', 'PS', 'Psychology and human behavior'),
('English Philology', 'EP', 'English language and literature'),
('History', 'HI', 'History and archaeology studies'),
('Data Science', 'DS', 'Data science and machine learning'),
('Cybersecurity', 'CS', 'Cybersecurity and information security'),
('Business Administration', 'BA', 'Business administration and management');

-- Add more teachers
INSERT INTO teachers (name, title, phone, email, department_id, hire_date, salary, status) VALUES
('Prof. Dimitar Todorov', 'professor', '+359888678901', 'd.todorov@university.bg', 1, '2005-09-01', 3800.00, 'active'),
('Doc. Anna Petrova', 'associate_professor', '+359888789012', 'a.petrova@university.bg', 1, '2013-03-01', 3000.00, 'active'),
('As. Krasimir Georgiev', 'assistant', '+359888890123', 'k.georgiev@university.bg', 1, '2021-09-01', 2400.00, 'active'),
('Prof. Violeta Stoyanova', 'professor', '+359888901234', 'v.stoyanova@university.bg', 2, '2007-02-15', 3700.00, 'active'),
('Doc. Petar Nikolov', 'associate_professor', '+359888012345', 'p.nikolov@university.bg', 2, '2014-09-01', 3100.00, 'active'),
('As. Radka Dimitrova', 'assistant', '+359888123456', 'r.dimitrova@university.bg', 2, '2020-02-01', 2300.00, 'active'),
('Prof. Hristo Petkov', 'professor', '+359888234567', 'h.petkov@university.bg', 3, '2006-09-01', 3600.00, 'active'),
('Doc. Silvia Georgieva', 'associate_professor', '+359888345678', 's.georgieva@university.bg', 3, '2015-03-01', 2900.00, 'active'),
('As. Boris Ivanov', 'assistant', '+359888456789', 'b.ivanov@university.bg', 3, '2019-09-01', 2200.00, 'active'),
('Prof. Magdalena Stoyanova', 'professor', '+359888567890', 'm.stoyanova@university.bg', 4, '2004-02-01', 3900.00, 'active'),
('Doc. Nikolay Dimitrov', 'associate_professor', '+359888678901', 'n.dimitrov@university.bg', 4, '2012-09-01', 3200.00, 'active'),
('As. Viktoria Petrova', 'assistant', '+359888789012', 'v.petrova@university.bg', 4, '2021-02-01', 2500.00, 'active'),
('Prof. Georgi Nikolov', 'professor', '+359888890123', 'g.nikolov@university.bg', 5, '2003-09-01', 3700.00, 'active'),
('Doc. Daniela Georgieva', 'associate_professor', '+359888901234', 'd.georgieva@university.bg', 5, '2011-03-01', 3000.00, 'active'),
('As. Martin Stoyanov', 'assistant', '+359888012345', 'm.stoyanov@university.bg', 5, '2018-09-01', 2300.00, 'active');

-- Add many more students
INSERT INTO students (faculty_number, first_name, middle_name, last_name, specialty_id, course, email, address, phone, birth_date, enrollment_date, status) VALUES
-- Software Engineering students
('121220006', 'Николай', 'Петров', 'Димитров', 1, 2, 'n.dimitrov@student.university.bg', 'ул. Цар Освободител 45, София', '+359887678901', '2003-04-18', '2021-09-15', 'active'),
('121220007', 'Светлана', 'Георгиева', 'Петрова', 1, 3, 's.petrova@student.university.bg', 'бул. Витоша 67, София', '+359887789012', '2002-08-25', '2020-09-15', 'active'),
('121220008', 'Димитър', 'Стоянов', 'Николов', 1, 1, 'd.nikolov@student.university.bg', 'ул. Граф Игнатиев 23, София', '+359887890123', '2004-12-03', '2022-09-15', 'active'),
('121220009', 'Радостина', 'Николова', 'Стоянова', 1, 4, 'r.stoyanova@student.university.bg', 'ул. Раковски 89, Пловдив', '+359887901234', '2001-06-14', '2019-09-15', 'active'),
('121220010', 'Васил', 'Димитров', 'Георгиев', 1, 2, 'v.georgiev@student.university.bg', 'бул. Мария Луиза 12, Пловдив', '+359887012345', '2003-09-28', '2021-09-15', 'active'),

-- Computer Science students
('121220011', 'Анна', 'Петрова', 'Иванова', 2, 1, 'a.ivanova@student.university.bg', 'ул. Шипка 34, Варна', '+359887123456', '2004-01-15', '2022-09-15', 'active'),
('121220012', 'Кристиян', 'Георгиев', 'Петров', 2, 3, 'k.petrov@student.university.bg', 'ул. Драган Цанков 56, Варна', '+359887234567', '2002-05-22', '2020-09-15', 'active'),
('121220013', 'Милена', 'Стоянова', 'Димитрова', 2, 2, 'm.dimitrova@student.university.bg', 'ул. Славянска 78, Бургас', '+359887345678', '2003-11-08', '2021-09-15', 'active'),
('121220014', 'Тодор', 'Николов', 'Стоянов', 2, 4, 't.stoyanov@student.university.bg', 'ул. Александровска 90, Бургас', '+359887456789', '2001-03-30', '2019-09-15', 'active'),
('121220015', 'Камелия', 'Димитрова', 'Николова', 2, 1, 'k.nikolova@student.university.bg', 'ул. Братя Миладинови 45, Русе', '+359887567890', '2004-07-12', '2022-09-15', 'active'),

-- Information Systems students
('121220016', 'Борис', 'Петров', 'Георгиев', 3, 3, 'b.georgiev@student.university.bg', 'ул. Дунавска 67, Русе', '+359887678901', '2002-12-25', '2020-09-15', 'active'),
('121220017', 'Десислава', 'Георгиева', 'Стоянова', 3, 2, 'd.stoyanova@student.university.bg', 'ул. Търговска 89, Стара Загора', '+359887789012', '2003-08-18', '2021-09-15', 'active'),
('121220018', 'Емил', 'Стоянов', 'Петров', 3, 1, 'e.petrov@student.university.bg', 'ул. Свобода 12, Стара Загора', '+359887890123', '2004-04-05', '2022-09-15', 'active'),
('121220019', 'Жасмина', 'Николова', 'Димитрова', 3, 4, 'j.dimitrova@student.university.bg', 'ул. Христо Ботев 34, Плевен', '+359887901234', '2001-10-20', '2019-09-15', 'active'),
('121220020', 'Златимир', 'Димитров', 'Николов', 3, 2, 'z.nikolov@student.university.bg', 'ул. Съединение 56, Плевен', '+359887012345', '2003-02-14', '2021-09-15', 'active'),

-- Applied Mathematics students
('121220021', 'Ивайло', 'Петров', 'Стоянов', 4, 1, 'i.stoyanov@student.university.bg', 'ул. Оборище 78, Добрич', '+359887123456', '2004-06-30', '2022-09-15', 'active'),
('121220022', 'Йорданка', 'Георгиева', 'Петрова', 4, 3, 'y.petrova@student.university.bg', 'ул. Плиска 90, Добрич', '+359887234567', '2002-09-15', '2020-09-15', 'active'),
('121220023', 'Калин', 'Стоянов', 'Георгиев', 4, 2, 'k.georgiev@student.university.bg', 'ул. Тракия 45, Шумен', '+359887345678', '2003-01-25', '2021-09-15', 'active'),
('121220024', 'Лилиана', 'Николова', 'Димитрова', 4, 4, 'l.dimitrova@student.university.bg', 'ул. Преслав 67, Шумен', '+359887456789', '2001-05-10', '2019-09-15', 'active'),
('121220025', 'Мартин', 'Димитров', 'Николов', 4, 1, 'm.nikolov@student.university.bg', 'ул. Цар Симеон 89, Велико Търново', '+359887567890', '2004-11-18', '2022-09-15', 'active'),

-- Physics students
('121220026', 'Нели', 'Петрова', 'Стоянова', 5, 2, 'n.stoyanova@student.university.bg', 'ул. Самуил 12, Велико Търново', '+359887678901', '2003-03-08', '2021-09-15', 'active'),
('121220027', 'Огнян', 'Георгиев', 'Петров', 5, 3, 'o.petrov@student.university.bg', 'ул. Гурко 34, Габрово', '+359887789012', '2002-07-22', '2020-09-15', 'active'),
('121220028', 'Павлина', 'Стоянова', 'Димитрова', 5, 1, 'p.dimitrova@student.university.bg', 'ул. Априлов 56, Габрово', '+359887890123', '2004-12-12', '2022-09-15', 'active'),
('121220029', 'Румен', 'Николов', 'Георгиев', 5, 4, 'r.georgiev@student.university.bg', 'ул. Възраждане 78, Сливен', '+359887901234', '2001-04-05', '2019-09-15', 'active'),
('121220030', 'Стефания', 'Димитрова', 'Николова', 5, 2, 's.nikolova@student.university.bg', 'ул. Хан Аспарух 90, Сливен', '+359887012345', '2003-10-28', '2021-09-15', 'active'),

-- Computer Engineering students
('121220031', 'Теодор', 'Петров', 'Стоянов', 6, 1, 't.stoyanov@student.university.bg', 'ул. Стефан Караджа 45, Ямбол', '+359887123456', '2004-08-15', '2022-09-15', 'active'),
('121220032', 'Уляна', 'Георгиева', 'Петрова', 6, 3, 'u.petrova@student.university.bg', 'ул. Иван Вазов 67, Ямбол', '+359887234567', '2002-02-28', '2020-09-15', 'active'),
('121220033', 'Филип', 'Стоянов', 'Димитров', 6, 2, 'f.dimitrov@student.university.bg', 'ул. Христо Смирненски 89, Хасково', '+359887345678', '2003-06-14', '2021-09-15', 'active'),
('121220034', 'Христина', 'Николова', 'Стоянова', 6, 4, 'h.stoyanova@student.university.bg', 'ул. България 12, Хасково', '+359887456789', '2001-09-30', '2019-09-15', 'active'),
('121220035', 'Цветан', 'Димитров', 'Николов', 6, 1, 'c.nikolov@student.university.bg', 'ул. Свобода 34, Кърджали', '+359887567890', '2004-01-22', '2022-09-15', 'active'),

-- Economics students
('121220036', 'Чавдар', 'Петров', 'Георгиев', 7, 2, 'ch.georgiev@student.university.bg', 'ул. Македония 56, Кърджали', '+359887678901', '2003-05-08', '2021-09-15', 'active'),
('121220037', 'Шенка', 'Георгиева', 'Димитрова', 7, 3, 'sh.dimitrova@student.university.bg', 'ул. Тракия 78, Смолян', '+359887789012', '2002-11-18', '2020-09-15', 'active'),
('121220038', 'Щерю', 'Стоянова', 'Петрова', 7, 1, 'sht.petrova@student.university.bg', 'ул. Родопи 90, Смолян', '+359887890123', '2004-07-25', '2022-09-15', 'active'),
('121220039', 'Юлиян', 'Николов', 'Стоянов', 7, 4, 'yu.stoyanov@student.university.bg', 'ул. Пирин 45, Благоевград', '+359887901234', '2001-03-12', '2019-09-15', 'active'),
('121220040', 'Яна', 'Димитрова', 'Николова', 7, 2, 'ya.nikolova@student.university.bg', 'ул. Македония 67, Благоевград', '+359887012345', '2003-12-05', '2021-09-15', 'active');

-- Add more disciplines
INSERT INTO disciplines (name, code, semester, teacher_id, credits, hours_per_week, type, description) VALUES
-- Additional Computer Science disciplines
('Object-Oriented Programming', 'CS102', 2, 1, 5, 3, 'mandatory', 'Advanced programming concepts and OOP principles'),
('Computer Networks', 'CS202', 4, 2, 4, 3, 'mandatory', 'Network protocols and communication'),
('Operating Systems', 'CS302', 6, 1, 4, 3, 'mandatory', 'System programming and OS concepts'),
('Machine Learning', 'CS401', 8, 2, 5, 4, 'elective', 'Introduction to machine learning algorithms'),
('Computer Graphics', 'CS402', 8, 1, 3, 2, 'elective', '2D and 3D graphics programming'),
('Mobile Development', 'CS403', 7, 2, 4, 3, 'elective', 'Mobile app development for iOS and Android'),

-- Mathematics disciplines
('Calculus I', 'MA102', 1, 3, 5, 4, 'mandatory', 'Differential and integral calculus'),
('Calculus II', 'MA202', 3, 3, 5, 4, 'mandatory', 'Multivariable calculus and series'),
('Statistics and Probability', 'MA302', 5, 3, 4, 3, 'mandatory', 'Statistical analysis and probability theory'),
('Discrete Mathematics', 'MA201', 4, 3, 4, 3, 'mandatory', 'Logic, sets, and combinatorics'),

-- Physics disciplines
('Physics II', 'PH102', 2, 4, 5, 4, 'mandatory', 'Electricity and magnetism'),
('Modern Physics', 'PH201', 4, 4, 4, 3, 'mandatory', 'Quantum mechanics and relativity'),
('Laboratory Physics', 'PH301', 6, 4, 3, 2, 'mandatory', 'Experimental physics and measurements'),

-- Engineering disciplines
('Digital Electronics', 'CE101', 3, 5, 4, 3, 'mandatory', 'Digital circuits and logic design'),
('Microprocessors', 'CE201', 5, 5, 4, 3, 'mandatory', 'Microprocessor architecture and programming'),
('Computer Architecture', 'CE301', 7, 5, 4, 3, 'mandatory', 'Computer organization and design'),

-- Economics disciplines
('Microeconomics', 'EC101', 1, 6, 4, 3, 'mandatory', 'Individual economic behavior'),
('Macroeconomics', 'EC201', 3, 6, 4, 3, 'mandatory', 'National economy analysis'),
('Financial Management', 'EC301', 5, 6, 4, 3, 'mandatory', 'Corporate finance and investment'),

-- Psychology disciplines
('General Psychology', 'PS101', 1, 7, 4, 3, 'mandatory', 'Introduction to psychological concepts'),
('Developmental Psychology', 'PS201', 3, 7, 4, 3, 'mandatory', 'Human development across lifespan'),
('Social Psychology', 'PS301', 5, 7, 4, 3, 'mandatory', 'Group behavior and social influence'),

-- Language disciplines
('English Grammar', 'EP101', 1, 8, 3, 2, 'mandatory', 'English grammar and syntax'),
('English Literature', 'EP201', 3, 8, 4, 3, 'mandatory', 'British and American literature'),
('Academic Writing', 'EP301', 5, 8, 3, 2, 'mandatory', 'Academic writing and research skills'),

-- History disciplines
('Ancient History', 'HI101', 1, 9, 4, 3, 'mandatory', 'Ancient civilizations and cultures'),
('Medieval History', 'HI201', 3, 9, 4, 3, 'mandatory', 'Medieval period and feudalism'),
('Modern History', 'HI301', 5, 9, 4, 3, 'mandatory', 'Modern world history and revolutions');

-- Add many more grades
INSERT INTO grades (student_id, discipline_id, grade, date, exam_type, notes) VALUES
-- Grades for Software Engineering students (students 1, 6-10)
(1, 1, 5.50, '2021-01-20', 'written', 'Excellent understanding of programming concepts'),
(1, 2, 4.75, '2022-01-15', 'written', 'Good algorithmic thinking'),
(1, 3, 5.25, '2022-06-10', 'practical', 'Strong database design skills'),
(1, 8, 5.00, '2021-06-20', 'written', 'Solid OOP implementation'),
(1, 9, 4.50, '2022-06-15', 'practical', 'Good network configuration skills'),

(6, 1, 4.25, '2022-01-20', 'written', 'Good basic programming skills'),
(6, 8, 4.00, '2022-06-20', 'written', 'Adequate OOP understanding'),
(6, 10, 4.75, '2023-01-15', 'written', 'Good mathematical foundation'),

(7, 1, 5.75, '2021-01-20', 'written', 'Outstanding programming abilities'),
(7, 2, 5.50, '2022-01-15', 'written', 'Excellent algorithmic problem solving'),
(7, 3, 5.00, '2022-06-10', 'practical', 'Good database implementation'),
(7, 8, 5.25, '2021-06-20', 'written', 'Excellent OOP design'),
(7, 9, 5.00, '2022-06-15', 'practical', 'Strong networking skills'),

(8, 1, 3.50, '2023-01-20', 'written', 'Needs improvement in programming logic'),
(8, 8, 3.25, '2023-06-20', 'written', 'Basic OOP concepts understood'),
(8, 10, 4.00, '2023-06-15', 'written', 'Satisfactory mathematical skills'),

(9, 1, 4.00, '2020-01-20', 'written', 'Adequate programming skills'),
(9, 2, 4.25, '2021-01-15', 'written', 'Good algorithmic thinking'),
(9, 3, 4.50, '2021-06-10', 'practical', 'Decent database skills'),
(9, 8, 4.75, '2020-06-20', 'written', 'Good OOP implementation'),
(9, 9, 4.25, '2021-06-15', 'practical', 'Adequate networking knowledge'),

(10, 1, 4.75, '2022-01-20', 'written', 'Good programming foundation'),
(10, 8, 4.50, '2022-06-20', 'written', 'Solid OOP understanding'),
(10, 10, 5.25, '2022-06-15', 'written', 'Strong mathematical abilities'),

-- Grades for Computer Science students (students 2, 11-15)
(2, 1, 4.25, '2022-01-20', 'written', 'Good basic programming skills'),
(2, 4, 5.00, '2022-06-15', 'written', 'Solid mathematical foundation'),
(2, 8, 4.50, '2022-06-20', 'written', 'Good OOP concepts'),

(11, 1, 3.75, '2023-01-20', 'written', 'Basic programming understanding'),
(11, 8, 3.50, '2023-06-20', 'written', 'Developing OOP skills'),
(11, 10, 4.25, '2023-06-15', 'written', 'Good mathematical potential'),

(12, 1, 5.25, '2021-01-20', 'written', 'Strong programming skills'),
(12, 2, 4.75, '2022-01-15', 'written', 'Good algorithmic approach'),
(12, 8, 5.00, '2021-06-20', 'written', 'Excellent OOP design'),
(12, 9, 4.50, '2022-06-15', 'practical', 'Good networking knowledge'),

(13, 1, 4.50, '2022-01-20', 'written', 'Solid programming foundation'),
(13, 8, 4.25, '2022-06-20', 'written', 'Good OOP understanding'),
(13, 10, 4.75, '2022-06-15', 'written', 'Strong mathematical skills'),

(14, 1, 5.00, '2020-01-20', 'written', 'Excellent programming abilities'),
(14, 2, 4.50, '2021-01-15', 'written', 'Good algorithmic thinking'),
(14, 8, 4.75, '2020-06-20', 'written', 'Solid OOP implementation'),
(14, 9, 4.25, '2021-06-15', 'practical', 'Adequate networking skills'),

(15, 1, 3.25, '2023-01-20', 'written', 'Needs more practice in programming'),
(15, 8, 3.00, '2023-06-20', 'written', 'Basic OOP concepts'),
(15, 10, 3.75, '2023-06-15', 'written', 'Developing mathematical skills'),

-- Grades for Information Systems students (students 4, 16-20)
(4, 1, 3.50, '2023-01-20', 'written', 'Needs improvement in programming logic'),
(4, 8, 3.25, '2023-06-20', 'written', 'Basic OOP concepts understood'),

(16, 1, 4.75, '2021-01-20', 'written', 'Good programming skills'),
(16, 2, 4.25, '2022-01-15', 'written', 'Solid algorithmic thinking'),
(16, 3, 4.50, '2022-06-10', 'practical', 'Good database understanding'),
(16, 8, 4.75, '2021-06-20', 'written', 'Strong OOP implementation'),

(17, 1, 4.00, '2022-01-20', 'written', 'Adequate programming skills'),
(17, 8, 3.75, '2022-06-20', 'written', 'Developing OOP skills'),
(17, 10, 4.50, '2022-06-15', 'written', 'Good mathematical foundation'),

(18, 1, 3.75, '2023-01-20', 'written', 'Basic programming understanding'),
(18, 8, 3.50, '2023-06-20', 'written', 'Learning OOP concepts'),
(18, 10, 4.00, '2023-06-15', 'written', 'Satisfactory mathematical skills'),

(19, 1, 5.25, '2020-01-20', 'written', 'Excellent programming abilities'),
(19, 2, 5.00, '2021-01-15', 'written', 'Strong algorithmic thinking'),
(19, 3, 5.50, '2021-06-10', 'practical', 'Outstanding database skills'),
(19, 8, 5.25, '2020-06-20', 'written', 'Excellent OOP design'),

(20, 1, 4.25, '2022-01-20', 'written', 'Good programming foundation'),
(20, 8, 4.00, '2022-06-20', 'written', 'Solid OOP understanding'),
(20, 10, 4.75, '2022-06-15', 'written', 'Strong mathematical abilities'),

-- Grades for Applied Mathematics students (students 21-25)
(21, 10, 5.50, '2023-01-20', 'written', 'Excellent mathematical understanding'),
(21, 11, 5.25, '2023-06-20', 'written', 'Strong calculus skills'),
(21, 12, 5.00, '2023-06-15', 'written', 'Good statistical knowledge'),

(22, 10, 4.75, '2021-01-20', 'written', 'Good mathematical foundation'),
(22, 11, 4.50, '2021-06-20', 'written', 'Solid calculus understanding'),
(22, 12, 4.25, '2021-06-15', 'written', 'Adequate statistical skills'),

(23, 10, 5.00, '2022-01-20', 'written', 'Strong mathematical abilities'),
(23, 11, 4.75, '2022-06-20', 'written', 'Good calculus knowledge'),
(23, 12, 5.25, '2022-06-15', 'written', 'Excellent statistical understanding'),

(24, 10, 4.50, '2020-01-20', 'written', 'Solid mathematical skills'),
(24, 11, 4.25, '2020-06-20', 'written', 'Good calculus foundation'),
(24, 12, 4.00, '2020-06-15', 'written', 'Basic statistical knowledge'),

(25, 10, 3.75, '2023-01-20', 'written', 'Developing mathematical skills'),
(25, 11, 3.50, '2023-06-20', 'written', 'Basic calculus understanding'),
(25, 12, 3.25, '2023-06-15', 'written', 'Learning statistical concepts'),

-- Grades for Physics students (students 5, 26-30)
(5, 1, 4.00, '2021-01-20', 'written', 'Adequate programming skills'),
(5, 4, 4.75, '2021-06-15', 'written', 'Strong mathematical abilities'),
(5, 13, 5.25, '2021-06-20', 'written', 'Excellent physics understanding'),

(26, 13, 4.50, '2022-01-20', 'written', 'Good physics foundation'),
(26, 14, 4.25, '2022-06-20', 'written', 'Solid understanding of modern physics'),
(26, 15, 4.00, '2022-06-15', 'practical', 'Adequate laboratory skills'),

(27, 13, 5.00, '2021-01-20', 'written', 'Strong physics abilities'),
(27, 14, 4.75, '2021-06-20', 'written', 'Good modern physics knowledge'),
(27, 15, 4.50, '2021-06-15', 'practical', 'Good laboratory work'),

(28, 13, 3.50, '2023-01-20', 'written', 'Basic physics understanding'),
(28, 14, 3.25, '2023-06-20', 'written', 'Developing modern physics knowledge'),
(28, 15, 3.00, '2023-06-15', 'practical', 'Learning laboratory techniques'),

(29, 13, 4.75, '2020-01-20', 'written', 'Good physics foundation'),
(29, 14, 4.50, '2020-06-20', 'written', 'Solid modern physics understanding'),
(29, 15, 4.25, '2020-06-15', 'practical', 'Adequate laboratory skills'),

(30, 13, 4.00, '2022-01-20', 'written', 'Satisfactory physics knowledge'),
(30, 14, 3.75, '2022-06-20', 'written', 'Basic modern physics understanding'),
(30, 15, 3.50, '2022-06-15', 'practical', 'Developing laboratory skills'),

-- Additional grades for various disciplines
-- Some students with multiple grades in different subjects
(1, 11, 5.75, '2021-06-15', 'written', 'Excellent mathematical abilities'),
(1, 13, 4.50, '2021-06-20', 'written', 'Good physics understanding'),
(2, 11, 4.25, '2022-06-15', 'written', 'Solid mathematical foundation'),
(3, 11, 5.50, '2020-06-15', 'written', 'Strong mathematical skills'),
(7, 11, 5.00, '2021-06-15', 'written', 'Good mathematical understanding'),
(12, 11, 4.75, '2021-06-15', 'written', 'Solid mathematical abilities'),
(16, 11, 4.50, '2021-06-15', 'written', 'Good mathematical foundation'),
(19, 11, 5.25, '2020-06-15', 'written', 'Excellent mathematical skills');

-- Add some grades for newer disciplines
INSERT INTO grades (student_id, discipline_id, grade, date, exam_type, notes) VALUES
-- Grades for newer disciplines (Machine Learning, Mobile Development, etc.)
(1, 14, 5.50, '2023-01-15', 'project', 'Excellent machine learning project'),
(7, 14, 5.25, '2023-01-15', 'project', 'Strong ML implementation'),
(12, 14, 4.75, '2023-01-15', 'project', 'Good ML understanding'),
(16, 14, 5.00, '2023-01-15', 'project', 'Solid ML skills'),

(1, 15, 4.75, '2022-06-20', 'practical', 'Good graphics programming'),
(7, 15, 5.00, '2022-06-20', 'practical', 'Excellent graphics skills'),
(12, 15, 4.50, '2022-06-20', 'practical', 'Solid graphics understanding'),

(1, 16, 5.25, '2022-06-15', 'project', 'Excellent mobile app development'),
(7, 16, 5.00, '2022-06-15', 'project', 'Strong mobile development skills'),
(12, 16, 4.75, '2022-06-15', 'project', 'Good mobile app implementation'),

-- Grades for Engineering disciplines
(31, 17, 4.50, '2023-01-20', 'written', 'Good digital electronics understanding'),
(32, 17, 4.75, '2021-01-20', 'written', 'Solid digital circuits knowledge'),
(33, 17, 4.25, '2022-01-20', 'written', 'Adequate electronics foundation'),
(34, 17, 5.00, '2020-01-20', 'written', 'Excellent digital electronics skills'),
(35, 17, 3.75, '2023-01-20', 'written', 'Basic electronics understanding'),

(31, 18, 4.25, '2023-06-15', 'practical', 'Good microprocessor programming'),
(32, 18, 4.50, '2021-06-15', 'practical', 'Solid microprocessor skills'),
(33, 18, 4.00, '2022-06-15', 'practical', 'Adequate microprocessor knowledge'),
(34, 18, 4.75, '2020-06-15', 'practical', 'Strong microprocessor abilities'),
(35, 18, 3.50, '2023-06-15', 'practical', 'Basic microprocessor understanding'),

-- Grades for Economics disciplines
(36, 19, 4.75, '2022-01-20', 'written', 'Good microeconomics understanding'),
(37, 19, 5.00, '2021-01-20', 'written', 'Excellent microeconomics knowledge'),
(38, 19, 4.50, '2023-01-20', 'written', 'Solid microeconomics foundation'),
(39, 19, 4.25, '2020-01-20', 'written', 'Adequate microeconomics skills'),
(40, 19, 4.00, '2022-01-20', 'written', 'Satisfactory microeconomics understanding'),

(36, 20, 4.50, '2022-06-15', 'written', 'Good macroeconomics knowledge'),
(37, 20, 4.75, '2021-06-15', 'written', 'Strong macroeconomics understanding'),
(38, 20, 4.25, '2023-06-15', 'written', 'Solid macroeconomics foundation'),
(39, 20, 4.00, '2020-06-15', 'written', 'Adequate macroeconomics skills'),
(40, 20, 3.75, '2022-06-15', 'written', 'Basic macroeconomics understanding');

-- Display summary of added data
SELECT 'Sample data added successfully!' AS status;
SELECT COUNT(*) AS total_students FROM students;
SELECT COUNT(*) AS total_teachers FROM teachers;
SELECT COUNT(*) AS total_disciplines FROM disciplines;
SELECT COUNT(*) AS total_grades FROM grades;
SELECT COUNT(*) AS total_specialties FROM specialties;
SELECT COUNT(*) AS total_departments FROM departments;
