# 資料庫系統作業

<course.sql>  
1.建立資料表:  
資料表名稱為"course"。  
資料表包含以下欄位：  
id：課程代碼。  
time1、time2、time3：課程時間的相關資訊(W1 1-14節W2 15-28節…以此類推)。  
credit：課程學分。  
most_people、now_people：分別代表最大選課人數和目前選課人數。  
required_elective：課程類型，可能是"必修"或"選修"。  
name：課程名稱。  
grade：整數型別，代表開課年級。  
department：開課系所。  
detail：包含開課時間、地點以及授課教師等詳細資訊。  
2.插入資料:  
使用INSERT INTO將資料插入"course"資料表中，每個INSERT INTO語句代表一門課程的資料。  
每次插入都提供了所有欄位的值，按照建立資料表時的順序依次對應。  
3.查詢資料:  
使用SELECT * FROM course;語句，將會列出"course"資料表中所有的資料列，即所有已插入的課程資料。  

<schedule.sql>  
1.建立兩個欄位:  
	1.student_id: 代表學生的學號  
	2.course_id: 代表課程的編號  
2.插入資料:  
使用INSERT INTO語句將資料插入"schedule"資料表中，每個INSERT INTO語句代表一筆學生選課的資料，每筆插入都提供了學生學號和所選課程的編號。  
3.查詢資料:  
使用SELECT * FROM schedule;這個查詢語句，將會列出"schedule"資料表中所有的  資料列，即所有學生的選課情況  

<students.sql>  
1.建立資料表:  
id：學生學號  
name：學生名字  
grade：年級  
department：科系名稱  
credit：學分數  
2.插入資料：  
使用INSERT INTO語句將資料插入"students"資料表中，每個INSERT INTO語句代表一筆學生選課的資料，每次插入依序對應到學生基本資料。  
3.查詢資料：  
	使用SELECT * FROM students;語句，將會列出"students"資料表中所有的資料列，即學生基本資料。  
	

