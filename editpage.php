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
		</script>
		<center><br><h1>Delivery Tip Tracker</h1><br>
';

date_default_timezone_set('America/Los_Angeles');

if($_SESSION["dominosdriver"]) {
	echo '<a href="index.php">New Order</a> | <a href="profile.php">Past Orders</a> | <a href="timeclock.php">Time Clock</a> | <a href="logout.php" onclick="return confirm_alert(this);">Logout</a><br><br>';
	$driverid = $_SESSION["dominosdriver"];
	$payweek = $_GET['date'];
	
	echo 'Paycheck for week of '.$payweek.'<br><br>';
	//set initial dates
	$date1 = DateTime::createFromFormat('Y-m-d', $payweek);
	$date1 = $date1->format('Y-m-d');
	$date2 = DateTime::createFromFormat('Y-m-d', $payweek);
	date_add($date2, date_interval_create_from_date_string('14 days'));
	$date2 = $date2->format('Y-m-d'); 

	echo '<table border="1"><tr><td style="background-color: white; color: black;">Date</td><td style="background-color: white; color: black;">Hours Worked</td></tr>';
	$current = 'no';
	$totalhours = '0';
	$results1 = "SELECT id, clockin, clockout, current FROM dominostimeclock WHERE clockin >= ? AND clockin < ? AND driverid = ? AND current = ?";
	$statement1 = $mysqli->prepare($results1);
	$statement1->bind_param('ssss', $date1, $date2, $driverid, $current);
	$statement1->execute();
	$statement1->bind_result($cid, $clockin, $clockout, $current);
	mysqli_stmt_store_result($statement1);
	if ($statement1->num_rows > 0) {
		while($statement1->fetch()) {
			$date3 = new DateTime($clockin);
			$date3 = $date3->format('Y-m-d'); 
			$starttime = strtotime($clockin);
			$endtime = strtotime($clockout);
			$secs = $endtime - $starttime;
			$dtotaltimeunround = $secs / 3600;
			$hoursworked = round($dtotaltimeunround, 2);
			echo '<tr><td style="color: white;">'.$date3.'</td><td style="color: white; text-align: right;">'.$hoursworked.'</td></tr>';
			$totalhours = $totalhours + $hoursworked;
		}
	}
	$paybeforetaxes = $totalhours * 5;
	echo '</table><br>Total Hours: '.$totalhours.'<br>Pay before taxes: $'.$paybeforetaxes;
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	

	
	$results = "SELECT deliverycharge, tip, cardtip, id FROM dominos WHERE driverid=? AND date >= ? AND date < ? ORDER BY id ASC";
	$statement = $mysqli->prepare($results);
	$statement->bind_param('sss', $_SESSION["dominosdriver"], $date1, $date2);
	$statement->execute();
	$statement->bind_result($dcharge, $dtip, $dcardtip, $did);
	$tiptotal = 0;
	$chargetotal = 0;
	$cardtiptotal = 0;
	mysqli_stmt_store_result($statement);
	$dreim = 0.85 * $statement->num_rows;
	if ($statement->num_rows > 0) {
		while($statement->fetch()) {

			$tiptotal = $tiptotal + $dtip;
			$cardtiptotal = $cardtiptotal + $dcardtip;
			$chargetotal = $chargetotal + $dcharge;
		}
	
		$totaltipscombined = $cardtiptotal + $tiptotal;
		$amountmade = $totaltipscombined + $dreim;
		$amountowed =  20 + $chargetotal - $cardtiptotal - $dreim;
		
		//tips per hour
		$datee = $datee . '%';
		$current = 'no';
		$results1 = "SELECT clockin, clockout FROM dominostimeclock WHERE driverid = ? AND clockin LIKE ? AND current = ? LIMIT 1";
		$statement1 = $mysqli->prepare($results1);
		$statement1->bind_param('sss', $_SESSION["dominosdriver"], $datee, $current);
		$statement1->execute();
		$statement1->bind_result($clockin, $clockout);
		mysqli_stmt_store_result($statement1);
		while($statement1->fetch()) {
			$clockin = strtotime($clockin);
			$clockout = strtotime($clockout);
			$secs = $clockout - $clockin;
			//minutes
			$dtotaltimeunround = $secs / 60;
			//hours
			$dtotaltimeunround2 = $dtotaltimeunround / 60;
			$dtotaltimeminutes = round($dtotaltimeunround, 2);
			$dtotaltimehours = round($dtotaltimeunround2, 2);
			
			
			$resultsx = "SELECT tip, cardtip, id FROM dominos WHERE driverid=? AND date=? ORDER BY id ASC";
			$statementx = $mysqli->prepare($resultsx);
			$statementx->bind_param('ss', $_SESSION["dominosdriver"], $datee);
			$statementx->execute();
			$statementx->bind_result($dtip, $dcardtip, $did);
			$tiptotal = 0;
			$chargetotal = 0;
			$cardtiptotal = 0;
			mysqli_stmt_store_result($statementx);
			$dreim = 0.85 * $statementx->num_rows;
			if ($statementx->num_rows > 0) {
				while($statementx->fetch()) {
					$tiptotal = $tiptotal + $dtip;
					$cardtiptotal = $cardtiptotal + $dcardtip;
					$totaltipz = $tiptotal + $cardtiptotal + $dreim;
				}
				
				//check if it's less than an hour
				if ($dtotaltimeminutes < 60) {
					$totaltipz = round($totaltipz, 2);
				} else {
					//dont divide by zero bro
					if ($dtotaltimeminutes > 0) {
						$tph = $totaltipz / $dtotaltimeminutes;
					}
					$tph = $tph * 60;
					$tph = round($tph, 2);
				}
			}
		}
	}
	echo '<br>Total Tips: $'.$amountmade;
	
	
	
	
	
	
	
	
	
	
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