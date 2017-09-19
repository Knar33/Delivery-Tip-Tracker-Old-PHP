<?php
session_start();

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
	if (isset($_POST['submit'])){
		$mysqli = new mysqli('localhost', 'ab19320_admin', 'godsmile5', 'ab19320_atdmain');
		if($mysqli->connect_errno > 0){
			die('Unable to connect to database [' . $mysqli->connect_error . ']');
		}
		global $mysqli;
		
		
		$dtip = $_POST['dtip'];
		$dcardtip = $_POST['dcardtip'];
		$dcharge = $_POST['dcharge'];
		$ddate = date('Y-m-d');
		$driverid = $_SESSION["dominosdriver"];

		$query = "INSERT INTO dominos (date, deliverycharge, tip, cardtip, driverid) VALUES(?, ?, ?, ?, ?)";
		$statement = $mysqli->prepare($query);
		$statement->bind_param('sssss', $ddate, $dcharge, $dtip, $dcardtip, $driverid);

		if($statement->execute()){
			print 'The order has been successfully entered';
		}else{
			die('Error : ('. $mysqli->errno .') '. $mysqli->error);
		}
		$statement->close();
		echo '<script> location.replace("profile.php"); </script>';

	} else {
		print '<a href="index.php">New Order</a> | <a href="profile.php">Past Orders</a> | <a href="timeclock.php">Time Clock</a> | <a href="logout.php" onclick="return confirm_alert(this);">Logout</a><form action="index.php" method="post" id="deliveryform"><br>
			<img src="cash.png">Cash Charge:<div style="width: 24px; height: 24px; display:inline-block;"> </div><br><input type="number" step="any" name="dcharge" style="color: black;" size="27"><br>
			<img src="cash.png">Cash Tip:<div style="width: 24px; height: 24px; display:inline-block;"> </div><br><input type="number" step="any" name="dtip" style="color: black;" size="27"><br>
			<img src="credit.png">Credit Tip:<div style="width: 24px; height: 24px; display:inline-block;"> </div><br><input type="number" step="any" name="dcardtip" style="color: black;" size="27">
			
			<br><input type="submit" name="submit" value="Submit"></form>';
			
	}
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