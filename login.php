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

<link href=\'fonts.googleapis.com/css?family=Raleway:400,300,600\' rel=\'stylesheet\' type=\'text/css\' />
<link rel="stylesheet" href="normalize.css" />
<link rel="stylesheet" href="skeleton.css" />
<link rel="stylesheet" href="custom.css" />
<meta name="theme-color" content="#616161" />

<script src="ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" /></script>
<!--[if lt IE 9]>
	<script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script>
<![endif]-->
</head>
<body style="color: white;"><center><br><h1>Delivery Tip Tracker</h1><br>
';

$mysqli = new mysqli('localhost', 'ab19320_admin', 'godsmile5', 'ab19320_atdmain');
if($mysqli->connect_errno > 0){
    die('Unable to connect to database [' . $mysqli->connect_error . ']');
}
global $mysqli;

$demail = $_POST['email'];
$dpassword = $_POST['password'];

$results = "SELECT id FROM dominosdrivers WHERE email=? AND password=?";
$statement = $mysqli->prepare($results);
$statement->bind_param('ss', $demail, $dpassword);
$statement->execute();
$statement->bind_result($driverid);
mysqli_stmt_store_result($statement);
if (mysqli_stmt_num_rows($statement)==0) {
	echo 'Your username and password don\'t match. <a href="index.php">Please try again</a>';
} else {
	while($statement->fetch()) {
	$_SESSION['dominosdriver'] = $driverid;
	}  
	echo 'You have successfully logged in!';
	echo '<script> location.replace("index.php"); </script>';
}
$statement->close();
echo '		</center></body>
		</html>
		';
?>