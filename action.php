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

    $sql = "SELECT * FROM course WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $MyHead);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row_count = mysqli_num_rows($result);

    if($row_count > 0) {
        $row = mysqli_fetch_assoc($result); //印出課程內容
        echo "課程ID: " . $row['id'] . "<br>";
        echo "學分: " . $row['credit'] . "<br>";
        echo "人數上限: " . $row['most_people'] . "<br>";
        echo "已選人數: " . $row['now_people'] . "<br>";
        echo "必修或選修: " . $row['required_elective'] . "<br>";
        echo "課程名稱: " . $row['name'] . "<br>";
        echo "年級: " . $row['grade'] . "<br>";
        echo "系所: " . $row['department'] . "<br>";
        echo "" . $row['detail'] . "<br>";

        echo '<form method="post">';
        echo '<input type="hidden" name="course_id" value="' . $row['id'] . '">';
        echo '<input type="hidden" name="course_name" value="' . $row['name'] . '">';
        echo '<input type="hidden" name="most_people" value="' . $row['most_people'] . '">';
        echo '<input type="hidden" name="now_people" value="' . $row['now_people'] . '">';
        echo '<input type="hidden" name="course_credit" value="' . $row['credit'] . '">';
        echo '<input type="hidden" name="course_department" value="' . $row['department'] . '">';
        echo '<input type="hidden" name="time1" value="' . $row['time1'] . '">';
        echo '<input type="hidden" name="time2" value="' . $row['time2'] . '">';
        echo '<input type="hidden" name="time3" value="' . $row['time3'] . '">';
        echo '<input type="submit" name="select_course" value="加選">';
        echo '<input type="submit" name="drop_course" value="退選">';
        echo '</form>';
    }
    else{
        echo '找不到符合該 選課代號 的學生資料';
        header('Location: choose.php');//找不到就回到choose.php
        exit(); // 确保在重定向之后立即退出脚本
    }

    mysqli_stmt_close($stmt);
    mysqli_close($conn);
}

if (isset($_POST['select_course'])) {
    
    $selected_course_id = (int)$_POST['course_id']; 
    $most_people  = $_POST['most_people'];
    $now_people  = $_POST['now_people'];
    $course_name = $_POST['course_name'];
    $course_credit = $_POST['course_credit'];
    $course_department = $_POST['course_department'];
    $time1 = $_POST['time1'];
    $time2 = $_POST['time2'];
    $time3 = $_POST['time3'];

    if ($most_people > $now_people) { //沒超過人數限制
        $now_people += 1;
        
        $dbhost = '127.0.0.1';
        $dbuser = 'hj';
        $dbpass = 'test1234';
        $dbname = 'testdb';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Error with MySQL connection');
        mysqli_set_charset($conn, 'utf8');

        $student_id = $_SESSION['student_id']; 

        $sql_student = "SELECT * FROM students WHERE id = ?";
        $stmt_student = mysqli_prepare($conn, $sql_student);
        mysqli_stmt_bind_param($stmt_student, 'i', $student_id);
        mysqli_stmt_execute($stmt_student);
        $result_student = mysqli_stmt_get_result($stmt_student);
        $row_student = mysqli_fetch_assoc($result_student);

        $schedule_sql = "SELECT * FROM schedule WHERE student_id = ?";
        $schedule_stmt = mysqli_prepare($conn, $schedule_sql);
        mysqli_stmt_bind_param($schedule_stmt, 'i', $student_id);
        mysqli_stmt_execute($schedule_stmt);
        $schedule_result = mysqli_stmt_get_result($schedule_stmt);
        $schedule_row = mysqli_fetch_assoc($schedule_result);

        $course_sql = "SELECT * FROM course WHERE id = ?";
        $course_stmt = mysqli_prepare($conn, $course_sql);
        mysqli_stmt_bind_param($course_stmt, 'i', $schedule_row['course_id']);
        mysqli_stmt_execute($course_stmt);
        $course_result = mysqli_stmt_get_result($course_stmt);
        $course_row = mysqli_fetch_assoc($course_result);

        $flag = 0;
        $time = array_fill(0, 100, 0);

        $schedule_result->data_seek(0);
        while($schedule_row = mysqli_fetch_assoc($schedule_result)) {           
            
            $course_sql = "SELECT * FROM course WHERE id = ?";
            $course_stmt = mysqli_prepare($conn, $course_sql);
            mysqli_stmt_bind_param($course_stmt, 'i', $schedule_row['course_id']);
            mysqli_stmt_execute($course_stmt);
            $course_result = mysqli_stmt_get_result($course_stmt);
            $course_row = mysqli_fetch_assoc($course_result);

            if ($course_row) {
                if ($schedule_row['course_id'] == $selected_course_id) { //判斷有無選過這堂課
                    $flag = 1;
                } 
                else if ($course_row['name'] == $course_name) {   //判斷有無選過同名的課
                    $flag = 2;
                }
    
                if ($course_row['time1'] != 0) {
                    $time[$course_row['time1']] = 1;
                }
                if ($course_row['time2'] != 0) {
                    $time[$course_row['time2']] = 1;
                }
                if ($course_row['time3'] != 0) {
                    $time[$course_row['time3']] = 1;
                }
            }
        }

        if ($time[$time1] == 0){
            if ($time[$time2] == 0){
                if ($time[$time3] == 0){
                    if ($flag == 1){
                        echo "你就選過這堂課<br>";
                    }
                    else if ($flag == 2){
                        echo "同名了啦<br>";
                    }
                    else{
                        if ($course_department == $row_student['department']){ //判斷是否是本系的課
                            if ($row_student['credit'] + $course_credit <= 30){ //判斷是否超過30學分
                                $sql_update_student_credit = "UPDATE students SET credit = credit + ? WHERE id = ?";
                                $stmt_update_student_credit = mysqli_prepare($conn, $sql_update_student_credit);
                                mysqli_stmt_bind_param($stmt_update_student_credit, 'ii', $course_credit, $student_id);
                                mysqli_stmt_execute($stmt_update_student_credit);

                                $sql_insert_schedule = "INSERT INTO schedule (student_id, course_id) VALUES (?, ?)";
                                $stmt_insert_schedule = mysqli_prepare($conn, $sql_insert_schedule);
                                mysqli_stmt_bind_param($stmt_insert_schedule, 'ii', $student_id, $selected_course_id);
                                mysqli_stmt_execute($stmt_insert_schedule);
 
                                mysqli_stmt_close($stmt_update_student_credit);
                                echo "課程 " . $selected_course_id . " 已成功加選！<br>";
                            }
                            else{
                                echo "超過30學分了<br>";
                            }

                            // 增加课程当前人数
                            $sql_update_course_people = "UPDATE course SET now_people = now_people + 1 WHERE id = ?";
                            $stmt_update_course_people = mysqli_prepare($conn, $sql_update_course_people);
                            mysqli_stmt_bind_param($stmt_update_course_people, 'i', $selected_course_id);
                            mysqli_stmt_execute($stmt_update_course_people);

                            mysqli_stmt_close($stmt_update_course_people);
                        }
                        else{
                            echo "這不是本系的課<br>";
                        }
                    }
                }
                else{
                    echo "衝堂囉<br>";
                }
            }
            else{
                echo "衝堂囉<br>";
            }
        }
        else{
            echo "衝堂囉<br>";
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
            echo '<br><a href="choose.php">返回選課</a>';
        }      
        
        mysqli_close($conn);
    } 
    else {
        echo "人滿了<br>";
        echo '<br><a href="choose.php">返回選課</a>';
    }
}

if (isset($_POST['drop_course'])) {
    
    $selected_course_id = (int)$_POST['course_id']; // Convert course_id to integer
    $most_people  = $_POST['most_people'];
    $now_people  = $_POST['now_people'];
    $course_credit = $_POST['course_credit'];
    $course_department = $_POST['course_department'];

    if ($now_people - 1 > 0) {
        $now_people -= 1;
        
        $dbhost = '127.0.0.1';
        $dbuser = 'hj';
        $dbpass = 'test1234';
        $dbname = 'testdb';
        $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname) or die('Error with MySQL connection');
        mysqli_set_charset($conn, 'utf8');

        $student_id = $_SESSION['student_id']; 

        $sql_student = "SELECT * FROM students WHERE id = ?";
        $stmt_student = mysqli_prepare($conn, $sql_student);
        mysqli_stmt_bind_param($stmt_student, 'i', $student_id);
        mysqli_stmt_execute($stmt_student);
        $result_student = mysqli_stmt_get_result($stmt_student);
        $row_student = mysqli_fetch_assoc($result_student);

        $course_sql = "SELECT * FROM course WHERE id = ?";
        $course_stmt = mysqli_prepare($conn, $course_sql);
        mysqli_stmt_bind_param($course_stmt, 'i', $selected_course_id);
        mysqli_stmt_execute($course_stmt);
        $course_result = mysqli_stmt_get_result($course_stmt);
        $course_row = mysqli_fetch_assoc($course_result);

        $schedule_sql = "SELECT * FROM schedule WHERE student_id = ?";
        $schedule_stmt = mysqli_prepare($conn, $schedule_sql);
        mysqli_stmt_bind_param($schedule_stmt, 'i', $student_id);
        mysqli_stmt_execute($schedule_stmt);
        $schedule_result = mysqli_stmt_get_result($schedule_stmt);
        $schedule_row = mysqli_fetch_assoc($schedule_result);

        $flag = 0;
        $schedule_result->data_seek(0);
        while($schedule_row = mysqli_fetch_assoc($schedule_result)) {           
            if ($schedule_row['course_id'] ==  $selected_course_id){ //判斷有無選過這堂課

                $sql_update_student_credit = "UPDATE students SET credit = credit - ? WHERE id = ?";
                $stmt_update_student_credit = mysqli_prepare($conn, $sql_update_student_credit);
                mysqli_stmt_bind_param($stmt_update_student_credit, 'ii', $course_credit, $student_id);
                mysqli_stmt_execute($stmt_update_student_credit);

                $delete_sql = "DELETE FROM schedule WHERE student_id = ? AND course_id = ?";
                $delete_stmt = mysqli_prepare($conn, $delete_sql);
                mysqli_stmt_bind_param($delete_stmt, 'ii', $student_id, $selected_course_id);
                mysqli_stmt_execute($delete_stmt);
                mysqli_stmt_close($delete_stmt);

                mysqli_stmt_close($stmt_update_student_credit);
                echo "課程 " . $selected_course_id . " 已成功退選！<br>";

                if ($row_student['credit'] - $course_credit < 9){  //低於9學分發出警告
                    echo "低於9學分了<br>";
                }

                if ($course_row['required_elective'] == "必修"){   ////退選必修課發出警告
                    echo "你退選必修課欸<br>";
                }

                mysqli_stmt_close($course_stmt);

                // 減少课程当前人数
                $sql_update_course_people = "UPDATE course SET now_people = now_people - 1 WHERE id = ?";
                $stmt_update_course_people = mysqli_prepare($conn, $sql_update_course_people);
                mysqli_stmt_bind_param($stmt_update_course_people, 'i', $selected_course_id);
                mysqli_stmt_execute($stmt_update_course_people);

                mysqli_stmt_close($stmt_update_course_people);

                $flag = 1;
            }
        }

        if ($flag == 0){
            echo "你就沒選這堂課<br>";
        }

        
        $sql_student = "SELECT * FROM students WHERE id = ?";
        $stmt_student = mysqli_prepare($conn, $sql_student);
        mysqli_stmt_bind_param($stmt_student, 'i', $student_id);
        mysqli_stmt_execute($stmt_student);
        $result_student = mysqli_stmt_get_result($stmt_student);
        $row_student = mysqli_fetch_assoc($result_student);

        if ($row_student > 0) { // 检查是否成功获取学生信息
            echo "學生ID: " . $row_student['id'] . "<br>";
            echo "姓名: " . $row_student['name'] . "<br>";
            echo "年級: " . $row_student['grade'] . "<br>";
            echo "學分: " . $row_student['credit'] . "<br>";
            echo "系所: " . $row_student['department'] . "<br>";
            echo '<br><a href="choose.php">返回選課</a>';
        } 
        
        mysqli_close($conn);
    } else {
        echo "奇怪了";
        echo '<br><a href="choose.php">返回選課</a>';
    }
}
?>
