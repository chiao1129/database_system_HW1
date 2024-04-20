<?php
session_start(); 

$dbhost = '127.0.0.1';
$dbuser = 'hj';
$dbpass = 'test1234';
$dbname = 'testdb';
$conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Error with MySQL connection');
mysqli_set_charset($conn, 'utf8');


if(isset($_SESSION['student_id'])) {
    $student_id = $_SESSION['student_id'];

    $sql = "SELECT * FROM schedule WHERE student_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $student_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if(mysqli_num_rows($result) > 0) {
        echo '<h2>課表</h2>';
        echo '<table border="1">';
        echo '<tr><th>課程</th><th>代號</th><th>上課時間/上課教室/授課教師</th></tr>';
        while($row = mysqli_fetch_assoc($result)) {  //印出課表

            $course_sql = "SELECT * FROM course WHERE id = ?";
            $course_stmt = mysqli_prepare($conn, $course_sql);
            mysqli_stmt_bind_param($course_stmt, 'i', $row['course_id']);  //用student_id去找
            mysqli_stmt_execute($course_stmt);
            $course_result = mysqli_stmt_get_result($course_stmt);
            $course_row = mysqli_fetch_assoc($course_result);

            echo '<tr>';
            echo '<td>' . $course_row['name'] . '</td>';
            echo '<td>' . $row['course_id'] . '</td>';
            echo '<td>' . $course_row['detail'] . '</td>';
            echo '</tr>';

            mysqli_stmt_close($course_stmt);
        }
        echo '</table>';
    } else {
        echo '你的課表示空的<br>';
    }

    $sql_student = "SELECT * FROM students WHERE id = ?";
    $stmt_student = mysqli_prepare($conn, $sql_student);
    mysqli_stmt_bind_param($stmt_student, 'i', $student_id);
    mysqli_stmt_execute($stmt_student);
    $result_student = mysqli_stmt_get_result($stmt_student);
    $row_student = mysqli_fetch_assoc($result_student);

    if ($row_student > 0) { //印出學生訊息
        echo "學生ID: " . $row_student['id'] . "<br>";
        echo "姓名: " . $row_student['name'] . "<br>";
        echo "年級: " . $row_student['grade'] . "<br>";
        echo "學分: " . $row_student['credit'] . "<br>";
        echo "系所: " . $row_student['department'] . "<br>";
    }      

    mysqli_stmt_close($stmt);
} 
else {
    echo '找不到學號<br>';
}

mysqli_close($conn);
?>
<a href="choose.php">回選課</a>
