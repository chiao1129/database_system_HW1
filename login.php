<?php
session_start(); // 启动会话

if(isset($_POST['MyHead'])) {
    $MyHead = $_POST['MyHead'];

    $dbhost = '127.0.0.1';
    $dbuser = 'hj';
    $dbpass = 'test1234';
    $dbname = 'testdb';
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Error with MySQL connection');
    mysqli_set_charset($conn, 'utf8');
    
    $sql = "SELECT * FROM students WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $MyHead);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row_count = mysqli_num_rows($result);

    if($row_count > 0) {
        $row = mysqli_fetch_assoc($result);       
        $_SESSION['student_id'] = $row['id'];
        header('Location: choose.php'); //跑去choose.php
        exit(); 
    } else {
        echo '找不到符合該 ID 的學生資料';
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}
?>
<a href="index.php">回首頁</a>
