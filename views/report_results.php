<?php
switch ($reportType) {
    case 'search_grades_student':
        displayGradesByStudent($results);
        break;
    case 'search_grades_discipline':
        displayGradesByDiscipline($results);
        break;
    case 'student_academic_report':
        displayStudentAcademicReport($results);
        break;
    case 'teachers_discipline':
        displayTeachersByDiscipline($results);
        break;
    case 'average_success_specialty':
        displayAverageSuccessBySpecialty($results);
        break;
    case 'discipline_average':
        displayDisciplineAverage($results);
        break;
    case 'top_students_discipline':
        displayTopStudentsByDiscipline($results);
        break;
    case 'diploma_eligible':
        displayDiplomaEligibleStudents($results);
        break;
    default:
        echo '<div class="text-center text-muted py-4">Няма данни за показване</div>';
        break;
}
function displayGradesByStudent($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени оценки за този студент</div>';
        return;
    }
    echo '<table class="table table-hover mb-0">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>Дисциплина</th>';
    echo '<th>Код</th>';
    echo '<th>Оценка</th>';
    echo '<th>Преподавател</th>';
    echo '<th>Дата</th>';
    echo '<th>Бележки</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($results as $grade) {
        $badgeClass = getGradeBadgeClass($grade['grade']);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($grade['discipline_name']) . '</td>';
        echo '<td>' . htmlspecialchars($grade['discipline_code']) . '</td>';
        echo '<td><span class="badge ' . $badgeClass . '">' . $grade['grade'] . '</span></td>';
        echo '<td>' . htmlspecialchars((!empty($grade['teacher_title']) ? $grade['teacher_title'] . ' ' : '') . $grade['teacher_name']) . '</td>';
        echo '<td>' . date('d.m.Y', strtotime($grade['created_at'])) . '</td>';
        echo '<td>' . htmlspecialchars($grade['notes'] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
function displayGradesByDiscipline($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени оценки за тази дисциплина</div>';
        return;
    }
    echo '<table class="table table-hover mb-0">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>Студент</th>';
    echo '<th>Факултетен номер</th>';
    echo '<th>Оценка</th>';
    echo '<th>Дата</th>';
    echo '<th>Бележки</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($results as $grade) {
        $badgeClass = getGradeBadgeClass($grade['grade']);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($grade['first_name'] . ' ' . $grade['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($grade['faculty_number']) . '</td>';
        echo '<td><span class="badge ' . $badgeClass . '">' . $grade['grade'] . '</span></td>';
        echo '<td>' . date('d.m.Y', strtotime($grade['created_at'])) . '</td>';
        echo '<td>' . htmlspecialchars($grade['notes'] ?? '') . '</td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
function displayStudentAcademicReport($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени данни за този студент</div>';
        return;
    }
    $student = $results[0];
    echo '<div class="row mb-4">';
    echo '<div class="col-md-6">';
    echo '<h6 class="text-primary">Обща информация</h6>';
    echo '<table class="table table-sm">';
    echo '<tr><td><strong>Име:</strong></td><td>' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</td></tr>';
    echo '<tr><td><strong>Факултетен номер:</strong></td><td>' . htmlspecialchars($student['faculty_number']) . '</td></tr>';
    echo '<tr><td><strong>Специалност:</strong></td><td>' . htmlspecialchars($student['specialty_name']) . '</td></tr>';
    echo '<tr><td><strong>Курс:</strong></td><td>' . $student['course'] . '</td></tr>';
    echo '<tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($student['email']) . '</td></tr>';
    echo '</table>';
    echo '</div>';
    echo '<div class="col-md-6">';
    echo '<h6 class="text-primary">Академични показатели</h6>';
    echo '<table class="table table-sm">';
    echo '<tr><td><strong>Общо оценки:</strong></td><td>' . $student['total_grades'] . '</td></tr>';
    echo '<tr><td><strong>Среден успех:</strong></td><td><span class="badge ' . getGradeBadgeClass($student['average_grade']) . '">' . ($student['average_grade'] ? round($student['average_grade'], 2) : 'Няма') . '</span></td></tr>';
    echo '<tr><td><strong>Най-ниска оценка:</strong></td><td><span class="badge ' . getGradeBadgeClass($student['min_grade']) . '">' . $student['min_grade'] . '</span></td></tr>';
    echo '<tr><td><strong>Най-висока оценка:</strong></td><td><span class="badge ' . getGradeBadgeClass($student['max_grade']) . '">' . $student['max_grade'] . '</span></td></tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
    if (!empty($student['detailed_grades'])) {
        echo '<h6 class="text-primary mb-3">Детайлни оценки по дисциплини</h6>';
        echo '<table class="table table-hover mb-0">';
        echo '<thead class="table-dark">';
        echo '<tr>';
        echo '<th>Дисциплина</th>';
        echo '<th>Код</th>';
        echo '<th>Семестър</th>';
        echo '<th>Кредити</th>';
        echo '<th>Оценка</th>';
        echo '<th>Преподавател</th>';
        echo '<th>Дата</th>';
        echo '</tr>';
        echo '</thead>';
        echo '<tbody>';
        foreach ($student['detailed_grades'] as $grade) {
            $badgeClass = getGradeBadgeClass($grade['grade']);
            echo '<tr>';
            echo '<td>' . htmlspecialchars($grade['discipline_name']) . '</td>';
            echo '<td>' . htmlspecialchars($grade['discipline_code']) . '</td>';
            echo '<td>' . $grade['semester'] . '</td>';
            echo '<td>' . $grade['credits'] . '</td>';
            echo '<td><span class="badge ' . $badgeClass . '">' . $grade['grade'] . '</span></td>';
            echo '<td>' . htmlspecialchars((!empty($grade['teacher_title']) ? $grade['teacher_title'] . ' ' : '') . $grade['teacher_name']) . '</td>';
            echo '<td>' . date('d.m.Y', strtotime($grade['created_at'])) . '</td>';
            echo '</tr>';
        }
        echo '</tbody>';
        echo '</table>';
    }
}
function displayTeachersByDiscipline($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени преподаватели за тази дисциплина</div>';
        return;
    }
    foreach ($results as $teacher) {
        echo '<div class="card mb-3">';
        echo '<div class="card-body">';
        echo '<div class="row">';
        echo '<div class="col-md-6">';
        echo '<h6 class="text-primary">Преподавател</h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Име:</strong></td><td>' . htmlspecialchars($teacher['name']) . '</td></tr>';
        echo '<tr><td><strong>Титла:</strong></td><td>' . htmlspecialchars($teacher['title'] ?? '') . '</td></tr>';
        echo '<tr><td><strong>Отдел:</strong></td><td>' . htmlspecialchars($teacher['department_name'] ?? '') . '</td></tr>';
        echo '<tr><td><strong>Email:</strong></td><td>' . htmlspecialchars($teacher['email']) . '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '<div class="col-md-6">';
        echo '<h6 class="text-primary">Дисциплина</h6>';
        echo '<table class="table table-sm">';
        echo '<tr><td><strong>Дисциплина:</strong></td><td>' . htmlspecialchars($teacher['discipline_name']) . '</td></tr>';
        echo '<tr><td><strong>Код:</strong></td><td>' . htmlspecialchars($teacher['discipline_code']) . '</td></tr>';
        echo '<tr><td><strong>Семестър:</strong></td><td>' . $teacher['semester'] . '</td></tr>';
        echo '<tr><td><strong>Кредити:</strong></td><td>' . $teacher['credits'] . '</td></tr>';
        echo '<tr><td><strong>Студенти:</strong></td><td>' . $teacher['students_count'] . '</td></tr>';
        echo '<tr><td><strong>Средна оценка:</strong></td><td>' . ($teacher['average_grade'] ? round($teacher['average_grade'], 2) : 'Няма') . '</td></tr>';
        echo '</table>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
    }
}
function displayAverageSuccessBySpecialty($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени студенти за тази специалност и курс</div>';
        return;
    }
    echo '<table class="table table-hover mb-0">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>Студент</th>';
    echo '<th>Факултетен номер</th>';
    echo '<th>Специалност</th>';
    echo '<th>Курс</th>';
    echo '<th>Общо оценки</th>';
    echo '<th>Среден успех</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($results as $student) {
        $badgeClass = getGradeBadgeClass($student['average_grade']);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['faculty_number']) . '</td>';
        echo '<td>' . htmlspecialchars($student['specialty_name']) . '</td>';
        echo '<td>' . $student['course'] . '</td>';
        echo '<td>' . $student['total_grades'] . '</td>';
        echo '<td><span class="badge ' . $badgeClass . '">' . ($student['average_grade'] ? round($student['average_grade'], 2) : 'Няма') . '</span></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
function displayDisciplineAverage($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени данни за тази дисциплина</div>';
        return;
    }
    $discipline = $results[0];
    echo '<div class="row">';
    echo '<div class="col-md-6">';
    echo '<h6 class="text-primary">Информация за дисциплината</h6>';
    echo '<table class="table table-sm">';
    echo '<tr><td><strong>Дисциплина:</strong></td><td>' . htmlspecialchars($discipline['discipline_name']) . '</td></tr>';
    echo '<tr><td><strong>Код:</strong></td><td>' . htmlspecialchars($discipline['discipline_code']) . '</td></tr>';
    echo '<tr><td><strong>Семестър:</strong></td><td>' . $discipline['semester'] . '</td></tr>';
    echo '<tr><td><strong>Преподавател:</strong></td><td>' . htmlspecialchars((!empty($discipline['teacher_title']) ? $discipline['teacher_title'] . ' ' : '') . ($discipline['teacher_name'] ?? 'Не е назначен')) . '</td></tr>';
    echo '</table>';
    echo '</div>';
    echo '<div class="col-md-6">';
    echo '<h6 class="text-primary">Статистики</h6>';
    echo '<table class="table table-sm">';
    echo '<tr><td><strong>Общо оценки:</strong></td><td>' . $discipline['total_grades'] . '</td></tr>';
    echo '<tr><td><strong>Студенти:</strong></td><td>' . $discipline['students_count'] . '</td></tr>';
    echo '<tr><td><strong>Среден успех:</strong></td><td><span class="badge ' . getGradeBadgeClass($discipline['average_grade']) . '">' . ($discipline['average_grade'] ? round($discipline['average_grade'], 2) : 'Няма') . '</span></td></tr>';
    echo '<tr><td><strong>Най-ниска оценка:</strong></td><td><span class="badge ' . getGradeBadgeClass($discipline['min_grade']) . '">' . $discipline['min_grade'] . '</span></td></tr>';
    echo '<tr><td><strong>Най-висока оценка:</strong></td><td><span class="badge ' . getGradeBadgeClass($discipline['max_grade']) . '">' . $discipline['max_grade'] . '</span></td></tr>';
    echo '</table>';
    echo '</div>';
    echo '</div>';
}
function displayTopStudentsByDiscipline($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма намерени оценки за тази дисциплина</div>';
        return;
    }
    echo '<div class="row">';
    $position = 1;
    foreach ($results as $student) {
        $badgeClass = getPositionBadgeClass($position);
        echo '<div class="col-md-4 mb-3">';
        echo '<div class="card text-center">';
        echo '<div class="card-body">';
        echo '<div class="mb-2">';
        echo '<span class="badge ' . $badgeClass . ' fs-6">' . $position . '</span>';
        echo '</div>';
        echo '<h6 class="card-title">' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</h6>';
        echo '<p class="card-text">';
        echo '<strong>Ф№:</strong> ' . htmlspecialchars($student['faculty_number']) . '<br>';
        echo '<strong>Оценка:</strong> <span class="badge ' . getGradeBadgeClass($student['grade']) . '">' . $student['grade'] . '</span><br>';
        echo '<strong>Специалност:</strong> ' . htmlspecialchars($student['specialty_name']) . '<br>';
        echo '<strong>Курс:</strong> ' . $student['course'] . '<br>';
        echo '<strong>Дата:</strong> ' . date('d.m.Y', strtotime($student['created_at'])) . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        $position++;
    }
    echo '</div>';
}
function displayDiplomaEligibleStudents($results) {
    if (empty($results)) {
        echo '<div class="text-center text-muted py-4">Няма студенти с достатъчен успех за дипломна работа (над 5.00)</div>';
        return;
    }
    echo '<table class="table table-hover mb-0">';
    echo '<thead class="table-dark">';
    echo '<tr>';
    echo '<th>Студент</th>';
    echo '<th>Факултетен номер</th>';
    echo '<th>Специалност</th>';
    echo '<th>Курс</th>';
    echo '<th>Общо оценки</th>';
    echo '<th>Среден успех</th>';
    echo '<th>Статус</th>';
    echo '</tr>';
    echo '</thead>';
    echo '<tbody>';
    foreach ($results as $student) {
        $badgeClass = getGradeBadgeClass($student['average_grade']);
        echo '<tr>';
        echo '<td>' . htmlspecialchars($student['first_name'] . ' ' . $student['last_name']) . '</td>';
        echo '<td>' . htmlspecialchars($student['faculty_number']) . '</td>';
        echo '<td>' . htmlspecialchars($student['specialty_name']) . '</td>';
        echo '<td>' . $student['course'] . '</td>';
        echo '<td>' . $student['total_grades'] . '</td>';
        echo '<td><span class="badge ' . $badgeClass . '">' . ($student['average_grade'] ? round($student['average_grade'], 2) : 'Няма') . '</span></td>';
        echo '<td><span class="badge badge-excellent">Годeн за дипломна работа</span></td>';
        echo '</tr>';
    }
    echo '</tbody>';
    echo '</table>';
}
function getGradeBadgeClass($grade) {
    if ($grade >= 5.50) {
        return 'badge-excellent';
    } elseif ($grade >= 4.50) {
        return 'badge-good';
    } else {
        return 'badge-average';
    }
}
function getPositionBadgeClass($position) {
    switch ($position) {
        case 1:
            return 'badge-excellent'; 
        case 2:
            return 'badge-good'; 
        case 3:
            return 'badge-average'; 
        default:
            return 'badge-secondary';
    }
}
?>
