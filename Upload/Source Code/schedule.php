<html>
<head>
<meta>
	<title>CIS Scheduling Program</title>
	
  <script>
    function hidden() {
      // switch screens
      document.getElementById('button').hidden = true;
    }
  </script>
	
</head>
<style>
table {
  font-family: arial, sans-serif;
  border-collapse: collapse;
  width: 100%;
}

td, th {
  border: 1px solid #dddddd;
  text-align: left;
  padding: 8px;
}

tr:nth-child(even) {
  background-color: #dddddd;
}
<div class="schedule">
<?php include 'design.php';?>
</div>
</style>
<body>
<br>
	<form method="post" enctype="multipart/form-data">
	<?php
	ob_start();
	set_time_limit(200);
	$servername = "localhost";
	$username = "root";
	$db_name = "rooms";
	$conn = mysqli_connect($servername, $username);
	if(!$conn){
		die("Connection failed:".mysqli_connect_error());
	}
	//echo "Connected successfully";

	function upload($filename, $conn){
		$table_name;
		echo "<input type='file' name = 'file'>";
		/*if (@$_FILES["file"]["error"])
			echo "Error: ".$_FILES['file']['error']."<br>";
		else {*/
		if(isset($_FILES["file"]) && $_FILES["file"]["size"] > 0){
			echo "<p>File Name: ".@$_FILES["file"]["name"]."</p>";
			echo "<p>File Size: ".(@$_FILES["file"]["size"] / 1024)." Kb</p>";
			$file_name = explode('.', @$_FILES["file"]["name"]);
			$i = 0;
			if(@$file_name[1] == 'csv'){
				mysqli_query($conn, "Create database if not exists csc350");
				$handle = fopen($_FILES["file"]["tmp_name"], "r");
				while($data = fgetcsv($handle)){
					if($i == 0){
						$table_name = $file_name[0];
						setcookie($filename, $table_name);
						mysqli_query($conn, "DROP TABLE IF EXISTS csc350.`".$table_name."`");
						mysqli_query($conn, "CREATE TABLE csc350.`".$table_name."` (tempColumn VARCHAR(255))");
						for ($j=0; $j < count($data); $j++) {
							$newColumn=mysqli_real_escape_string($conn, $data[$j]);
							mysqli_query($conn, "ALTER TABLE csc350.`".$table_name."` ADD `".$newColumn."` VARCHAR(255) ");
						}
						mysqli_query($conn, "ALTER TABLE csc350.`".$table_name."` DROP tempColumn");
						$i++;
					}
					else{
						$newdata = "'".implode($data, "','")."'";
						$newdata_sql = "insert into csc350.`".$table_name."` values(".$newdata.")";
						mysqli_query($conn, $newdata_sql);
					}
				}
			}
			if((@$_FILES["file"]["size"] / 1024)){
				echo "File selected, please click the <b>submit</b> button below to upload.";
			}
		}
	}
	
	function exist($servername,$username,$db_name,$courseNo,$sectionNo){
		$conn = mysqli_connect($servername, $username);
		
		$showAllTable_sql="show tables from $db_name";
		$tableNameResult = mysqli_query($conn, $showAllTable_sql);
		while($row = mysqli_fetch_assoc($tableNameResult)){		//row[0] = 'f900'
			$sql = "select * from $db_name.{$row['Tables_in_rooms']} where course = '$courseNo' and section = '$sectionNo'";
				$result = mysqli_query($conn,$sql);
				if(mysqli_num_rows($result) > 0)
					return 1;
			}
		return 0;
	}
	
	

	mysqli_query($conn, "Create database if not exists $db_name;");
	mysqli_query($conn, "Create database if not exists csc350;");

	$result = mysqli_query($conn, "show tables from csc350 like 'course';");
	if(mysqli_num_rows($result) < 1) {
		$sql_file = 'course.sql';
		$lines = file($sql_file);
		$op_data = '';
		mysqli_query($conn, "use csc350;");
		foreach ($lines as $line){
			if (substr($line, 0, 2) == '--' || $line == '')//This IF Remove Comment Inside SQL FILE
				continue;
			$op_data .= $line;
			if (substr(trim($line), -1, 1) == ';') {//Breack Line Upto ';' NEW QUERY
				mysqli_query($conn, $op_data);
				$op_data = '';
			}
		}
	}

	$course_table_name = null;
	$room_table_name = null;
	

	
	if(isset($_COOKIE['course'])){	//check for course cookies first, check room after course is set
		$result = mysqli_query($conn, "show tables from csc350 like '".$_COOKIE['course']."';");
		if(mysqli_num_rows($result) < 1) {	// check for course table
			echo "Please select a csv file for <font color='#6495ED'><b>COURSE  </b></font>";
			upload("course", $conn);
		}
		else{	//if course table is all set, do the room table
			$course_table_name = $_COOKIE['course'];
			if(isset($_COOKIE['room'])){	//check for room cookie
				$result = mysqli_query($conn, "show tables from csc350 like '".$_COOKIE['room']."';");
				if(mysqli_num_rows($result) < 1) {
					echo "Please select a csv file for <font color='#6495ED'><b>ROOM  </b></font>";
					upload("room", $conn);
				}
				else
					$room_table_name = $_COOKIE['room'];
			}
			else{	//if room cookie not set
				echo "Please select a csv file for <font color='#6495ED'><b>ROOM  </b></font>";
				upload("room", $conn);
			}
		}
	}
	else{	//if cookies AND the database tables are not set
		echo "Please select a csv file for <font color='#6495ED'><b>COURSE  </b></font>";
		upload("course", $conn);
	}
	
	echo "<p id='button'><input type='submit' value='Submit'></p>";

	
	$cno_arr;
	$days_arr;
	$room_arr;
	$Ccredits;
	
	
	if(($course_table_name != null) && ($room_table_name != null)) {
		$failed_count = 0;
		$scheduled_count = 0;
		
		echo "<script>hidden();</script>";
		$sql = "select * from csc350.$course_table_name";
		$result = mysqli_query($conn, $sql);
		if(mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				$cno_arr[] = $row['Course'];
				$days_arr[] = $row['Days'];
			}
		}
		
		$sql2 = "select * from csc350.$room_table_name";
		$result2 = mysqli_query($conn, $sql2);
		if(mysqli_num_rows($result2) > 0) {
			while($row = mysqli_fetch_assoc($result2)) {
				$room_arr[] = $row['Room'];
			}
		}

	for($m=0; $m<count($cno_arr); $m++){
		$CNo = $cno_arr[$m];
		$numOfDays = $days_arr[$m];
		if($numOfDays > 3){
			echo "<p>$CNo"." didn't scheduled, invalid days</p>";
			$failed_count++;
			continue;
		}
		$sql = "select * from csc350.course where CourseNo = '$CNo';";
		$result = mysqli_query($conn, $sql);
		if(mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_assoc($result)) {
				$Ccredits = $row['Credits'];
			}
		}
		else{
			echo "<p>$CNo"." didn't scheduled, unable to find course or credit</p>";
			$failed_count++;
			continue;
		}
		for($j=0; $j<count($room_arr); $j++){
			$roomNo = $room_arr[$j];
			$full = false;
	
	
	
	$startTime;
	$endTime;
	$days = [null,'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
	//@$roomNo = $_REQUEST['room'];
	//@$numOfDays = $_REQUEST['days'];
	@$totalHours = $Ccredits;
	@$totalMins = $totalHours * 60;
	@$minsPerDay = $totalMins / $numOfDays;

	$open_time = date('Y-m-d 08:00:00');	//building open time
	$close_time = date('Y-m-d 22:00:00');	//building close time
	$club_start = date('Y-m-d 14:00:00');
	$club_end = date('Y-m-d 16:00:00');

	$club_start = date('H:i',strtotime("$club_start"));
	$club_end = date('H:i',strtotime("$club_end"));


	
		$table_sql = "CREATE TABLE rooms.$roomNo(day char(3) DEFAULT NULL, course varchar(10) NOT NULL, startTime varchar(10) DEFAULT NULL, endTime varchar(10) DEFAULT NULL, section char(4) DEFAULT NULL, primary_key varchar(15) DEFAULT NULL)";
		mysqli_query($conn,$table_sql);
		if($numOfDays == 1) {	//if day = 1
			for($i=5;$i<=7;$i++) {	// Fri to Sun
				$sql = "select * from rooms.$roomNo where day = '$days[$i]'";	// check if there is any classes on that day
				$result = mysqli_query($conn,$sql);
				if(mysqli_num_rows($result) < 1) {		// if there is no class on this day, then the first class starts at 9:00
					$startTime = $open_time;	// set start time to yyyy-mm-dd 09:00:00
					$endTime = date('Y-m-d H:i:s',strtotime("$startTime + $totalMins minute"));	// set end time to (start time + total hour)
					$startTime = date('H:i',strtotime("$startTime"));	// get hr:min only
					$endTime = date('H:i',strtotime("$endTime"));		// get hr:min only
					$section = str_replace(":","",$startTime);

					$done = 0;
					while(!$done){
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						if(exist($servername,$username,$db_name,$CNo,$section))
							$section++;
						else
							$done = 1;
					}
					$section = str_pad($section,4,"0",STR_PAD_LEFT);
					$primaryKey = "$CNo-$section";

					$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
					mysqli_query($conn,$sql3);
					$scheduled_count++;
					break;	// once inserted, end loop
				}
				else {
					$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day = '$days[$i]')";
					$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time
					while($row = mysqli_fetch_assoc($endtime_result))
						$pre_endTime = $row["endTime"];		//assign that value
					$startTime = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
					$endTime = date('H:i',strtotime("$startTime + $totalMins minute"));	//new start time + total hour = new end time
					$section = str_replace(":","",$startTime);

					$done = 0;
					while(!$done){
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						if(exist($servername,$username,$db_name,$CNo,$section))
							$section++;
						else
							$done = 1;
					}
					$section = str_pad($section,4,"0",STR_PAD_LEFT);
					$primaryKey = "$CNo-$section";


					$latestStartTime = date('H:i', strtotime("$close_time - $totalMins minute"));	//class must begin before this time
					if($startTime <= $latestStartTime) {	//check if the class begin before $latestStartTime
						$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
						mysqli_query($conn,$sql3);	//if yes, insert data
						$scheduled_count++;
						break;	//once inserted, end loop
					}								// if no, do nothing, $i++ go to the next day
					else {							// no more class can be scheduled
						if($i == 7){				// AND it's Sunday
							$full = true;
							continue;
						}
					}
				}
			}
			if($full == true){
				continue;
			}
			else
				break;
		}
		if($numOfDays == 2) {	//if day = 2
			for($i=1;$i<=2;$i++) {
				$sql = "select * from rooms.$roomNo where day = '$days[$i]'";	// check if there is any classes on that day
				$result = mysqli_query($conn,$sql);
				if(mysqli_num_rows($result) < 1) {		// if there is no class on this day, then the first class starts at 9:00
					$startTime = $open_time;	// set start time to yyyy-mm-dd 09:00:00
					$endTime = date('Y-m-d H:i:s',strtotime("$startTime + $minsPerDay minute"));	// set end time to (start time + total minute)
					$startTime = date('H:i',strtotime("$startTime"));	// get hr:min only
					$endTime = date('H:i',strtotime("$endTime"));		// get hr:min only
					$section = str_replace(":","",$startTime);

					$done = 0;
					while(!$done){
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						if(exist($servername,$username,$db_name,$CNo,$section))
							$section++;
						else
							$done = 1;
					}
					$section = str_pad($section,4,"0",STR_PAD_LEFT);
					$primaryKey = "$CNo-$section";

					$sql_Mon_Tue = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
					$sql_Wed_Thu = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
					mysqli_query($conn,$sql_Mon_Tue);	//insert Mon/Tue class
					mysqli_query($conn,$sql_Wed_Thu);	//insert Wed/Thu class
					$scheduled_count++;
					break;	// once inserted, end loop
				}
				else {
					if($i == 2){	//tues and thurs
						$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day = '$days[$i]')";
						$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time on day[i]
						while($row = mysqli_fetch_assoc($endtime_result))
							$pre_endTime = $row["endTime"];		//assign that value
						$startTime = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
						$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));	//new start time + total hour = new end time
						$section = str_replace(":","",$startTime);

						$done = 0;
						while(!$done){
							$section = str_pad($section,4,"0",STR_PAD_LEFT);
							if(exist($servername,$username,$db_name,$CNo,$section))
								$section++;
							else
								$done = 1;
						}
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						$primaryKey = "$CNo-$section";

						$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time

						if($startTime < $latestStartTime) {	//check if the class begin before $latestStartTime
							$sql_Tue = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
							$sql_Thu = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
							mysqli_query($conn,$sql_Tue);	//if yes, insert data
							mysqli_query($conn,$sql_Thu);	//if yes, insert data
							$scheduled_count++;
							break;	//once inserted, end loop
						}								// if no, do nothing, $i++ go to the next day
						else{
							$full = true;
							continue;
							//echo "Room '$roomNo' is fully scheduled on Mon, Tues, Wed, Thur";
						}
					}
					else {	//mon and wed
						$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day in ('$days[$i]','{$days[$i+2]}'))";
						$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time on day[i]
						while($row = mysqli_fetch_assoc($endtime_result))
							$pre_endTime = $row["endTime"];		//assign that value
						$startTime = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
						$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));	//new start time + total hour = new end time
						$section = str_replace(":","",$startTime);

						$done = 0;
						while(!$done){
							$section = str_pad($section,4,"0",STR_PAD_LEFT);
							if(exist($servername,$username,$db_name,$CNo,$section))
								$section++;
							else
								$done = 1;
						}
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						$primaryKey = "$CNo-$section";

						$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time
						if($startTime <= $latestStartTime) {	//check if the class begin before $latestStartTime
							if($startTime <= $club_start && $endTime > $club_start){				//check if the class END time is between club hour
								$cut_mins = floor(strtotime($endTime)-strtotime($club_start))%86400/60+10;	//calculate exceeding time
								$Mon_end_time = date('H:i',strtotime("$endTime + $cut_mins minute"));	//Monday class hour will increase
								$Wed_end_time = date('H:i',strtotime("$endTime - $cut_mins minute"));	//Wednesday class hour will decrease
								$Wed_duration = floor(strtotime($Wed_end_time)-strtotime($startTime))%86400/60;	//calculate class hour
								if($Wed_duration >= 60){	// if the class is not less than 60mins
									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$Mon_end_time', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$Wed_end_time', '$section', '$primaryKey')";	// insert time to database

									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									break;	//once inserted, end loop
								}
								else {						// if the class is less than 50 mins, then it will start after the club hour
									$startTime = date('H:i',strtotime("$club_end + 10 minute"));	//set start time to 16:00 + 10 mins
									$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));
									$section = str_replace(":","",$startTime);

									$done = 0;
									while(!$done){
										$section = str_pad($section,4,"0",STR_PAD_LEFT);
										if(exist($servername,$username,$db_name,$CNo,$section))
											$section++;
										else
											$done = 1;
									}
									$section = str_pad($section,4,"0",STR_PAD_LEFT);
									$primaryKey = "$CNo-$section";

									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									break;	//once inserted, end loop
								}
							}
							else {	//if the class end time is not between club hour
								if(($startTime >= $club_start) && ($startTime <= $club_end)){ 		//if class END time is not between club hour and class START time is between club hour
									$startTime = date('H:i',strtotime("$club_end + 10 minute"));	//set start time to 16:00 + 10 mins
									$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));
									$section = str_replace(":","",$startTime);

									$done = 0;
									while(!$done){
										$section = str_pad($section,4,"0",STR_PAD_LEFT);
										if(exist($servername,$username,$db_name,$CNo,$section))
											$section++;
										else
											$done = 1;
									}
									$section = str_pad($section,4,"0",STR_PAD_LEFT);
									$primaryKey = "$CNo-$section";

									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									break;	//once inserted, end loop
								}
								else {	//no club hour related, go here
									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									break;	//once inserted, end loop
								}
							}
						}
					}
				}
			}
			if($full == true)
				continue;
			else
				break;
		}
		if($numOfDays == 3) {
			$startTimeDay3;
			$endTimeDay3;
			for($i=1;$i<=2;$i++) {
				$sql = "select * from rooms.$roomNo where day = '$days[$i]'";	// check if there is any classes on that day
				$result = mysqli_query($conn,$sql);
				if(mysqli_num_rows($result) < 1) {		// if there is no class on this day, then the first class starts at 9:00
					$startTime = $open_time;	// set start time to yyyy-mm-dd 09:00:00
					$endTime = date('Y-m-d H:i:s',strtotime("$startTime + $minsPerDay minute"));	// set end time to (start time + total minute)
					$startTime = date('H:i',strtotime("$startTime"));	// get hr:min only
					$endTime = date('H:i',strtotime("$endTime"));		// get hr:min only
					$section = str_replace(":","",$startTime);

					$done = 0;
					while(!$done){	//creating section number
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						if(exist($servername,$username,$db_name,$CNo,$section))
							$section++;
						else
							$done = 1;
					}
					$section = str_pad($section,4,"0",STR_PAD_LEFT);
					$primaryKey = "$CNo-$section";

					$sql_Mon_Tue = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
					$sql_Wed_Thu = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
					mysqli_query($conn,$sql_Mon_Tue);	//insert Mon/Tue class
					mysqli_query($conn,$sql_Wed_Thu);	//insert Wed/Thu class
					$scheduled_count++;

					for($k=5;$k<=7;$k++) {	// Fri to Sun
						$sql = "select * from rooms.$roomNo where day = '$days[$k]'";	// check if there is any classes on that day
						$result = mysqli_query($conn,$sql);
						$primaryKey = "$CNo-$section";

						if(mysqli_num_rows($result) < 1) {		// if there is no class on this day, then the first class starts at 9:00
							$startTime = $open_time;	// set start time to yyyy-mm-dd 09:00:00
							$endTime = date('Y-m-d H:i:s',strtotime("$startTime + $minsPerDay minute"));	// set end time to (start time + total hour)
							$startTimeDay3 = date('H:i',strtotime("$startTime"));	// get hr:min only
							$endTimeDay3 = date('H:i',strtotime("$endTime"));		// get hr:min only

							$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$k]', '$CNo', '$startTimeDay3', '$endTimeDay3', '$section', '$primaryKey')";	// insert time to database
							mysqli_query($conn,$sql3);
							break;	// once inserted, end loop
						}
						else {
							$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day = '$days[$k]')";
							$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time
							while($row = mysqli_fetch_assoc($endtime_result))
								$pre_endTime = $row["endTime"];		//assign that value
							$startTimeDay3 = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
							$endTimeDay3 = date('H:i',strtotime("$startTimeDay3 + $minsPerDay minute"));	//new start time + total hour = new end time

							$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time
							if($startTimeDay3 <= $latestStartTime) {	//check if the class begin before $latestStartTime
								$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$k]', '$CNo', '$startTimeDay3', '$endTimeDay3', '$section', '$primaryKey')";	// insert time to database
								mysqli_query($conn,$sql3);	//if yes, insert data
								break;	//once inserted, end loop
							}								// if no, do nothing, $i++ go to the next day
							else {
								if($k == 7) {//if the class does not begin before $latestStartTime AND it's Sunday, then the room is fully scheduled on FRI, SAT AND SUN.
									$full = true;
									continue;
								}
									//echo "Room '$roomNo' is fully scheduled on Friday, Saturday and Sunday";
							}
						}
					}
				break;	// once inserted, end loop
			}
				else {	//if there is class on this day already
					if($i == 2){ //Tues AND Thur
						$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day = '$days[$i]')";
						$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time on day[i]
						while($row = mysqli_fetch_assoc($endtime_result))
							$pre_endTime = $row["endTime"];		//assign that value
						$startTime = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
						$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));	//new start time + total hour = new end time
						$section = str_replace(":","",$startTime);

						$done = 0;
						while(!$done){
							$section = str_pad($section,4,"0",STR_PAD_LEFT);
							if(exist($servername,$username,$db_name,$CNo,$section))
								$section++;
							else
								$done = 1;
						}
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						$primaryKey = "$CNo-$section";

						$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time

						if($startTime < $latestStartTime) {	//check if the class begin before $latestStartTime
							$sql_Tue = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
							$sql_Thu = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
							mysqli_query($conn,$sql_Tue);	//if yes, insert data
							mysqli_query($conn,$sql_Thu);	//if yes, insert data
							$scheduled_count++;
							for($k=5;$k<=7;$k++) {	// Fri to Sun
								$sql = "select * from rooms.$roomNo where day = '$days[$k]'";	// check if there is any classes on that day
								$result = mysqli_query($conn,$sql);
								$primaryKey = "$CNo-$section";

								if(mysqli_num_rows($result) < 1) {		// if there is no class on this day, then the first class starts at 9:00
									$startTime = $open_time;	// set start time to yyyy-mm-dd 09:00:00
									$endTime = date('Y-m-d H:i:s',strtotime("$startTime + $minsPerDay minute"));	// set end time to (start time + total hour)
									$startTimeDay3 = date('H:i',strtotime("$startTime"));	// get hr:min only
									$endTimeDay3 = date('H:i',strtotime("$endTime"));		// get hr:min only

									$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$k]', '$CNo', '$startTimeDay3', '$endTimeDay3', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql3);
									break;	// once inserted, end loop
								}
								else {
									$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day = '$days[$k]')";
									$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time
									while($row = mysqli_fetch_assoc($endtime_result))
										$pre_endTime = $row["endTime"];		//assign that value
									$startTimeDay3 = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
									$endTimeDay3 = date('H:i',strtotime("$startTimeDay3 + $minsPerDay minute"));	//new start time + total hour = new end time

									$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time
									if($startTimeDay3 <= $latestStartTime) {	//check if the class begin before $latestStartTime
										$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$k]', '$CNo', '$startTimeDay3', '$endTimeDay3', '$section', '$primaryKey')";	// insert time to database
										mysqli_query($conn,$sql3);	//if yes, insert data
										break;	//once inserted, end loop
									}								// if no, do nothing, $i++ go to the next day
									else {
										if($k == 7) {	//if the class does not begin before $latestStartTime AND it's Sunday, then the room is fully scheduled on FRI, SAT AND SUN.
											$full = true;
											continue;
										}
											//echo "Room '$roomNo' is fully scheduled on Friday, Saturday and Sunday";
									}
								}
							}
							break;	//once inserted, end loop
						}
						else{
							$full = true;
							continue;
						}
							//echo "Room '$roomNo' is fully scheduled on Mon, Tues, Wed, Thur";
					}
					else {	// if i == 1, Mon AND Wed
						$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day in ('$days[$i]','{$days[$i+2]}'))";
						$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time on day[i]
						while($row = mysqli_fetch_assoc($endtime_result))
							$pre_endTime = $row["endTime"];		//assign that value
						$startTime = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
						$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));	//new start time + total hour = new end time
						$section = str_replace(":","",$startTime);

						$done = 0;
						while(!$done){
							$section = str_pad($section,4,"0",STR_PAD_LEFT);
							if(exist($servername,$username,$db_name,$CNo,$section))
								$section++;
							else
								$done = 1;
						}
						$section = str_pad($section,4,"0",STR_PAD_LEFT);
						$primaryKey = "$CNo-$section";

						$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time
						if($startTime <= $latestStartTime) {	//check if the class begin before $latestStartTime
							if($startTime <= $club_start && $endTime > $club_start){				//check if the class END time is between club hour
								$cut_mins = floor(strtotime($endTime)-strtotime($club_start))%86400/60+10;	//calculate exceeding time
								$Mon_end_time = date('H:i',strtotime("$endTime + $cut_mins minute"));	//Monday class hour will increase
								$Wed_end_time = date('H:i',strtotime("$endTime - $cut_mins minute"));	//Wednesday class hour will decrease
								$Wed_duration = floor(strtotime($Wed_end_time)-strtotime($startTime))%86400/60;	//calculate class hour
								if($Wed_duration >= 60){	// if the class is not less than 60mins
									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$Mon_end_time', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$Wed_end_time', '$section', '$primaryKey')";	// insert time to database
									$scheduled_count++;
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									//break;	//once inserted, end loop
								}
								else {						// if the class is less than 50 mins, then it will start after the club hour
									$startTime = date('H:i',strtotime("$club_end + 10 minute"));	//set start time to 16:00 + 10 mins
									$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));
									$section = str_replace(":","",$startTime);

									$done = 0;
									while(!$done){
										$section = str_pad($section,4,"0",STR_PAD_LEFT);
										if(exist($servername,$username,$db_name,$CNo,$section))
											$section++;
										else
											$done = 1;
									}
									$section = str_pad($section,4,"0",STR_PAD_LEFT);
									$primaryKey = "$CNo-$section";

									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									//break;	//once inserted, end loop
								}
							}
							else {	//if the class end time is not between club hour
								if(($startTime >= $club_start) && ($startTime <= $club_end)){ 		//if class END time is not between club hour and class START time is between club hour
									$startTime = date('H:i',strtotime("$club_end + 10 minute"));	//set start time to 16:00 + 10 mins
									$endTime = date('H:i',strtotime("$startTime + $minsPerDay minute"));
									$section = str_replace(":","",$startTime);

									$done = 0;
									while(!$done){
										$section = str_pad($section,4,"0",STR_PAD_LEFT);
										if(exist($servername,$username,$db_name,$CNo,$section))
											$section++;
										else
											$done = 1;
									}
									$section = str_pad($section,4,"0",STR_PAD_LEFT);
									$primaryKey = "$CNo-$section";

									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									//break;	//once inserted, end loop
								}
								else {	//no club hour related, go here
									$sql_Mon = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$i]', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									$sql_Wed = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('{$days[$i+2]}', '$CNo', '$startTime', '$endTime', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql_Mon);	//if yes, insert data
									mysqli_query($conn,$sql_Wed);	//if yes, insert data
									$scheduled_count++;
									//break;	//once inserted, end loop
								}
							}

							for($k=5;$k<=7;$k++) {	// Fri to Sun
								$sql = "select * from rooms.$roomNo where day = '$days[$k]'";	// check if there is any classes on that day
								$result = mysqli_query($conn,$sql);
								$primaryKey = "$CNo-$section";

								if(mysqli_num_rows($result) < 1) {		// if there is no class on this day, then the first class starts at 9:00
									$startTime = $open_time;	// set start time to yyyy-mm-dd 09:00:00
									$endTime = date('Y-m-d H:i:s',strtotime("$startTime + $minsPerDay minute"));	// set end time to (start time + total hour)
									$startTimeDay3 = date('H:i',strtotime("$startTime"));	// get hr:min only
									$endTimeDay3 = date('H:i',strtotime("$endTime"));		// get hr:min only

									$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$k]', '$CNo', '$startTimeDay3', '$endTimeDay3', '$section', '$primaryKey')";	// insert time to database
									mysqli_query($conn,$sql3);
									break;	// once inserted, end loop
								}
								else {
									$endtime_sql = "select * from rooms.$roomNo where endTime = (select max(endTime) from rooms.$roomNo where day = '$days[$k]')";
									$endtime_result = mysqli_query($conn,$endtime_sql);	//find latest end time
									while($row = mysqli_fetch_assoc($endtime_result))
										$pre_endTime = $row["endTime"];		//assign that value
									$startTimeDay3 = date('H:i',strtotime("$pre_endTime + 10 minute"));	//end time + 10min = new start time
									$endTimeDay3 = date('H:i',strtotime("$startTimeDay3 + $minsPerDay minute"));	//new start time + total hour = new end time

									$latestStartTime = date('H:i', strtotime("$close_time - $minsPerDay minute"));	//class must begin before this time
									if($startTimeDay3 <= $latestStartTime) {	//check if the class begin before $latestStartTime
										$sql3 = "insert into rooms.$roomNo(day, course, startTime, endTime, section, primary_key) values ('$days[$k]', '$CNo', '$startTimeDay3', '$endTimeDay3', '$section', '$primaryKey')";	// insert time to database
										mysqli_query($conn,$sql3);	//if yes, insert data
										break;	//once inserted, end loop
									}								// if no, do nothing, $i++ go to the next day
									else {
										if($k == 7) {	//if the class does not begin before $latestStartTime AND it's Sunday, then the room is fully scheduled on FRI, SAT AND SUN.
											$full = true;
											continue;
										}
											//echo "Room '$roomNo' is fully scheduled on Friday, Saturday and Sunday, Please try another room.";
									}
								}
							}
							break;
						}
					}
				}
			}
			if($full == true)
				continue;
			else break;
		}
	}
	}
	echo "<p><font color='#008000'>SCHEDULED: ".$scheduled_count."</font></p>";
	echo "<p><font color='#B22222'>FAILED: ".$failed_count."</font></p><br><br>";
		
	echo "<h3><p> All Scheduled Sections </p></h3>";
	$showAllTable_sql="show tables from $db_name";
	$tableNameResult = mysqli_query($conn, $showAllTable_sql);
	while($table = mysqli_fetch_assoc($tableNameResult))			//row[0] = 'f900'
	{	
		$sql_allscheduled = "select * from $db_name.{$table['Tables_in_rooms']} order by primary_key";
		$result = mysqli_query($conn,$sql_allscheduled);
		if(mysqli_num_rows($result) > 0)
		{
			
			echo "<table border='2'>";
			echo "<tr><b>"."<td>"."Room"."</td>"."<td>"."Section"."</td>"."<td>"."Course"."</td>"."<td>"."Day"."</td>"."<td>"."Start Time"."</td>"."<td>"."End Time"."</td>"."</b></tr>" ;
			while($row = mysqli_fetch_assoc($result))
			{
				echo "<tr>"."<td>".$table['Tables_in_rooms']."</td>"."<td>".$row["primary_key"]."</td>"."<td>".$row["course"]."</td>"."<td>".$row["day"]."</td>"."<td>".date('h:i A', strtotime($row['startTime']))."</td>"."<td>".date('h:i A', strtotime($row['endTime']))."</td>"."</tr>" ; // output data of that row
			}
			//echo "<br><br>";
			echo "</table>";
			echo "<br><br><br><br>";

		}

	}
	echo "<p>";
	
	echo "<h2 style='text-align: center'><p> Weekly View </p></h2>";

				function cmp($a, $b)				// this is the funtion to compare the array $a to return bool value and to sort with the new array later
{

    $a = preg_replace('{\:}', '', $a);
  
    $a = (int)$a;
    $b = preg_replace('{\:}', '', $b);
 
    $b = (int)$b;

    if ($a == $b) {
        return 0;
    }
    return ($a < $b) ? -1 : 1;  
}
	$tableNameResult = mysqli_query($conn, $showAllTable_sql);
	while($table = mysqli_fetch_assoc($tableNameResult))			//row[0] = 'f900'
	{	
		$sql_allscheduled = "select * from $db_name.{$table['Tables_in_rooms']}";
		
	
		$getRoom = mysqli_query($conn, "SELECT day, course, startTime, primary_key, endTime FROM $db_name.{$table['Tables_in_rooms']}");

		$result = mysqli_query($conn,$sql_allscheduled);

		
		if(mysqli_num_rows($result) > 0 )
		{
			
									
		echo "<table border=2><br><br>
		<h1 style='text-align: center'>Room ".$table['Tables_in_rooms']."</h1><p>		
		<tr>
		<tr>"."<td width=100>"."Time"."</td>"."<td width=100>"."Mon"."</td>"."<td width=100>"."Tue"."</td>"."<td width=100>"."Wed"."</td>"."<td width=100>"."Thu"."</td>"."<td width=100>"."Fri"."</td>"."<td width=100>"."Sat"."</td>"."<td width=100>"."Sun"."</td>"."</tr>
		</tr>
		</table>";
		
			$a = mysqli_fetch_all($getRoom, MYSQLI_ASSOC);
		
			$new_array=array();
		foreach($a AS $k =>$v){
     if(!array_key_exists($v['startTime'],$new_array)){
          $new_array[$v['startTime']]=array("","","","","","","","");			// creating multidimensional array "" to assign columns Time|Mon|Tue|Wed|Thu|Fri|Sat|Sun
          unset($new_array[$v['startTime']][0]);
     }
	 if ($v['day'] == "Mon")	{   		// assign number for particular day to make it work as $v['startTime'] index
	 $dayassign = 1;
	 }
	 else if ($v['day'] == "Tue")	{
	 $dayassign = 2;
	 }
	 else if ($v['day'] == "Wed")	{
	 $dayassign = 3;
	 }
	 else if ($v['day'] == "Thu")	{
	 $dayassign = 4;
	 }
	 else if ($v['day'] == "Fri")	{
	 $dayassign = 5;
	 }
	 else if ($v['day'] == "Sat")	{
	 $dayassign = 6;
	 }
	 else if ($v['day'] == "Sun")	{
	 $dayassign = 7;
	 }
	

    $new_array[$v['startTime']][$dayassign]=$v['primary_key']."<br>class ends at ".date('h:i A', strtotime($v['endTime']));			// creating multidimensional array

}


uksort($new_array, "cmp");  //  sort the new arry which is time(startTime)
//$weekmap = array( '','Sun','Mon','Tue','Wed','Thu','Fri','Sat');
//print_r($new_array);


echo "<table border=1>";
foreach($new_array AS $k =>$v){
echo "<tr><td width=100>".date('h:i A', strtotime($k))."</td>";
		
		foreach($v AS $k1 =>$v1){
            //echo $weekmap[$k1];
			
            //echo '->';
            if($v1==''){
            echo "<td width=100> -- </td>";
            }
			else
            echo "<td width=100> ".$v1." </td>";
            //echo '|';
			
        }
		
         echo PHP_EOL;
		 echo "</tr>";
}
echo "</table>";
		
		}
		echo "<p>";
	}
	
	echo "<p>";
	}

	
	$conn->close();
	?>
	</form>
</body>
</html>
