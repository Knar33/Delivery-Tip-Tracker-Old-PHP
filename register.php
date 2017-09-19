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
<body style="color: white;"><center><br><h1>Delivery Tip Tracker</h1><br>
';

if (isset($_POST['submit'])){
	$mysqli = new mysqli('localhost', 'ab19320_admin', 'godsmile5', 'ab19320_atdmain');
	if($mysqli->connect_errno > 0){
		die('Unable to connect to database [' . $mysqli->connect_error . ']');
	}
	global $mysqli;

	$dpassword = $_POST['dpassword'];
	$demail = $_POST['demail'];
	$dname = $_POST['dname'];
	$ddate = date('Y-m-d H:i:s');
	$driverid = $_SESSION["dominosdriver"];
	$clockedin = 'no';

	$results1 = "SELECT email FROM dominosdrivers WHERE email=?";
	$statement1 = $mysqli->prepare($results1);
	$statement1->bind_param('s', $demail);
	$statement1->execute();
	mysqli_stmt_store_result($statement1);
	if (mysqli_stmt_num_rows($statement1)==0) {		
		$query = "INSERT INTO dominosdrivers (name, email, password, clockedin) VALUES(?, ?, ?, ?)";
		$statement = $mysqli->prepare($query);
		$statement->bind_param('ssss', $dname, $demail, $dpassword, $clockedin);

		if($statement->execute()){
			print 'You have been successfully registered!<br><br><b>How to use</b><br>If the customer paid with cash, enter the amount from the delivery tag in the "Cash Charge" box and put the tip in the "Cash Tip" box. (ex: if the order was $25.64 and they give you $30, enter 25.64 in the "Cash Charge" box and 4.36 in the "cash Tip" box.) <br><br>If the customer paid with a credit card, you only have to enter the cash or credit card tip in the appropriate box. <br><br><a href="index.php">Click here to get started</a>';
		}else{
			die('Error : ('. $mysqli->errno .') '. $mysqli->error);
		}
		$statement->close();
	} else {
		echo 'A user is already registered with that E-mail address, ya big goof.';
	}
} else {
	print '<form action="register.php" method="post" id="registerform">
		Name: <br><input type="text" step="any" name="dname" style="color: black;" size="27"><br />
		Email Address: <br><input type="email" step="any" name="demail" style="color: black;" size="27"><br />
		Password: <br><input type="password" step="any" name="dpassword" style="color: black;" size="27"><br>
		<input type="submit" name="submit" value="Submit"></form>';
}
echo '		</center></body>
		</html>
		';
?>