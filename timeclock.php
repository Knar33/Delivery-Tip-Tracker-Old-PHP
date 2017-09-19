<?php
session_start();
$mysqli = new mysqli('localhost', 'ab19320_admin', 'godsmile5', 'ab19320_atdmain');
if($mysqli->connect_errno > 0){
    die('Unable to connect to database [' . $mysqli->connect_error . ']');
}
global $mysqli;
		echo '
		<!DOCTYPE html>
		<html lang="en">
		<head>

		<meta charset="utf-8" />
		<title>Delivery Calculator</title>
		<meta name="description" content="Driver form processing page" />
		<meta name="robots" content="index,follow" />
		<meta name="viewport" content="width=device-width, initial-scale=1" />
		<meta name="theme-color" content="#616161" />

		<link href=\'fonts.googleapis.com/css?family=Raleway:400,300,600\' rel=\'stylesheet\' type=\'text/css\' />
		<link rel="stylesheet" href="normalize.css" />
		<link rel="stylesheet" href="skeleton.css" />
		<link rel="stylesheet" href="custom.css" />

		<script src="ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" /></script>
		<!--[if lt IE 9]>
			<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
		<![endif]-->
		</head>
		<body style="color: white;">
		<script type="text/javascript">
		function confirm_alert(node) {
			return confirm("Are you sure you want to log out?");
		}
		
		function clockinconfirm() { 
			if (window.confirm("Are you sure you want to clock in?")) {
				window.location.href = ("timeclock.php?ca=clockin");
			} else {
				return false;
			}
		}
		
		function clockoutconfirm() { 
			if (window.confirm("Are you sure you want to clock out?")) {
				window.location.href = ("timeclock.php?ca=clockout");
			} else {
				return false;
			}
		}
		</script>
		<center><br><h1>Delivery Tip Tracker</h1><br>
';

date_default_timezone_set('America/Los_Angeles');

if($_SESSION["dominosdriver"]) {
	echo '<a href="index.php">New Order</a> | <a href="profile.php">Past Orders</a> | <a href="timeclock.php">Time Clock</a> | <a href="logout.php" onclick="return confirm_alert(this);">Logout</a><br><br>';
	$driverid = $_SESSION["dominosdriver"];
	$ca = $_GET["ca"];
	if ($ca == 'clockin') {
		$results4 = "SELECT clockedin FROM dominosdrivers WHERE id = ?";
		$statement4 = $mysqli->prepare($results4);
		$statement4->bind_param('s', $driverid);
		$statement4->execute();
		$statement4->bind_result($clockedin);
		mysqli_stmt_store_result($statement4);
		while($statement4->fetch()) {
			if ($clockedin == 'no') {
				$clockedin = 'yes';
				$results3 = "UPDATE dominosdrivers SET clockedin = ? WHERE id = ?";
				$statement3 = $mysqli->prepare($results3);
				$statement3->bind_param('ss', $clockedin, $driverid);
				$statement3->execute();
				
				$clockin = date('Y-m-d H:i:s');
				$current = 'yes';
				$query2 = "INSERT INTO dominostimeclock (driverid, clockin, current) VALUES(?, ?, ?)";
				$statement2 = $mysqli->prepare($query2);
				$statement2->bind_param('sss', $driverid, $clockin, $current);
				$statement2->execute(); 
			}
		}
		echo 'You have been successfully clocked in!<br>';
	}
	if ($ca == 'clockout') {
		$clockedin = 'no';
		$results3 = "UPDATE dominosdrivers SET clockedin = ? WHERE id = ?";
		$statement3 = $mysqli->prepare($results3);
		$statement3->bind_param('ss', $clockedin, $driverid);
		$statement3->execute(); 
		
		$clockout = date('Y-m-d H:i:s');
		$current = 'yes';
		$othercurrent = 'no';
		$results2 = "UPDATE dominostimeclock SET clockout = ?, current = ? WHERE current = ? AND driverid = ?";
		$statement2 = $mysqli->prepare($results2);
		$statement2->bind_param('ssss', $clockout, $othercurrent, $current, $driverid);
		$statement2->execute(); 
		echo 'You have been successfully clocked out!<br>';
	}
	
	$results = "SELECT clockedin FROM dominosdrivers WHERE id = ?";
	$statement = $mysqli->prepare($results);
	$statement->bind_param('s', $driverid);
	$statement->execute();
	$statement->bind_result($clockedin);
	mysqli_stmt_store_result($statement);
	while($statement->fetch()) {
		if ($clockedin == 'yes') {
			echo '<br><input type="button" onclick="clockoutconfirm();" value="Clock Out" /><br>';
			$current = 'yes';
			$results1 = "SELECT clockin FROM dominostimeclock WHERE driverid = ? AND current = ?";
			$statement1 = $mysqli->prepare($results1);
			$statement1->bind_param('ss', $driverid, $current);
			$statement1->execute();
			$statement1->bind_result($clockin);
			mysqli_stmt_store_result($statement1);
			while($statement1->fetch()) {
				$timenow = date('Y-m-d H:i:s');
				$timenow = strtotime($timenow);
				$clockin = strtotime($clockin);
				$secs = $timenow - $clockin;
				//minutes
				$dtotaltimeunround = $secs / 60;
				//hours
				$dtotaltimeunround2 = $dtotaltimeunround / 60;
				$dtotaltimeminutes = round($dtotaltimeunround, 2);
				$dtotaltimehours = round($dtotaltimeunround2, 2);
				
				if ($dtotaltimeunround < 60) {
					echo 'You have been clocked in for '.$dtotaltimeminutes.' minutes.<br><br>';
				} else {
					echo 'You have been clocked in for '.$dtotaltimehours.' hours.<br><br>';
				}
			}
		} else {
			echo '<br><input type="button" onclick="clockinconfirm();" value="Clock In" /><br><br>';
		}
	}
	echo '<hr>Pay periods:<br><br>';
	//set initial dates
	$date1 = DateTime::createFromFormat('Y-m-d', '2016-01-04');
	$date1 = $date1->format('Y-m-d');
	$date2 = DateTime::createFromFormat('Y-m-d', '2016-01-18');
	$date2 = $date2->format('Y-m-d'); 

	//only loops 5 years from the start date, beginning of 2016
	for ($x = 0; $x <= 2000; $x++) {
		$current = 'no';
		$results1 = "SELECT id, clockin, clockout, current FROM dominostimeclock WHERE clockin >= ? AND clockin < ? AND driverid = ? AND current = ? LIMIT 1";
		$statement1 = $mysqli->prepare($results1);
		$statement1->bind_param('ssss', $date1, $date2, $driverid, $current);
		$statement1->execute();
		$statement1->bind_result($cid, $clockin, $clockout, $current);
		mysqli_stmt_store_result($statement1);
		if ($statement1->num_rows > 0) {
		while($statement1->fetch()) {
				echo '<a href="paycheck.php?date='.$date1.'">Week of '. $date1 .'</a><br>';
			}
		}

		$date1 = DateTime::createFromFormat('Y-m-d', $date1);
		$date2 = DateTime::createFromFormat('Y-m-d', $date2);
		//add 2 weeks to dates
		date_add($date1, date_interval_create_from_date_string('14 days'));
		date_add($date2, date_interval_create_from_date_string('14 days'));
		$date1 = $date1->format('Y-m-d');
		$date2 = $date2->format('Y-m-d'); 
	}
	echo '<br>';
} else {
	print '<form action="login.php" method="post" id="driverform">
			Email:<br><input type="email" step="any" name="email" style="color: black;" size="27"><br />
			Password:<br><input type="password" step="any" name="password" style="color: black;" size="27"><br>
			<input type="submit" name="submit" value="Login"></form><br><a href="register.php">Click here to register</a>';
}

echo '		</center></body>
		</html>
		';
?>