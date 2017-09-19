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

date_default_timezone_set('America/Los_Angeles');

if($_SESSION["dominosdriver"]) {
	$mysqli = new mysqli('localhost', 'ab19320_admin', 'godsmile5', 'ab19320_atdmain');
	if($mysqli->connect_errno > 0){
		die('Unable to connect to database [' . $mysqli->connect_error . ']');
	}
	global $mysqli;
	
	
	echo '<a href="index.php">New Order</a> | <a href="profile.php">Past Orders</a> | <a href="timeclock.php">Time Clock</a> | <a href="logout.php" onclick="return confirm_alert2(this);">Logout</a><br><br>';

	echo 'All Orders:<br><table border="1"><tr><td style="background-color: white; color: black;"><div style="text-align: center;">Delivery Charge</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Cash Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Card Tip</div></td></tr>';

	$results = "SELECT deliverycharge, tip, cardtip, id FROM dominos WHERE driverid=? ORDER BY id ASC";
	$statement = $mysqli->prepare($results);
	$statement->bind_param('s', $_SESSION["dominosdriver"]);
	$statement->execute();
	$statement->bind_result($dcharge, $dtip, $dcardtip, $did);
	$tiptotal = 0;
	$chargetotal = 0;
	$cardtiptotal = 0;
	mysqli_stmt_store_result($statement);
	$dreim = 1.05 * $statement->num_rows;
	while($statement->fetch()) {
	
		echo '<tr><td>';
		if ($dcharge == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dcharge, 2, '.', '').'</div>';
		}
		echo '</td><td>';
		if ($dtip == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dtip, 2, '.', '').'</div>';
		}
		echo '</td><td>';
		if ($dcardtip == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dcardtip, 2, '.', '').'</div>';
		}
		echo '</tr>';
		$tiptotal = $tiptotal + $dtip;
		$cardtiptotal = $cardtiptotal + $dcardtip;
		$chargetotal = $chargetotal + $dcharge;
	}
	
	$totaltipscombined = $cardtiptotal + $tiptotal;
	echo '<tr><td style="background-color: white; color: black;"><div style="text-align: center;">Total charges</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Total Cash Tips</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Total Card Tips</div></td></tr>
		<tr><td><div style="text-align: center;">$'.number_format((float)$chargetotal, 2, '.', '').'</div></td><td><div style="text-align: center;">$'.number_format((float)$tiptotal, 2, '.', '').'</div></td><td><div style="text-align: center;">$'.number_format((float)$cardtiptotal, 2, '.', '').'</div></td></tr></table>
		Total Orders: ' . $statement->num_rows . '<br><br>';
		
	echo 'Cash Orders<br><table border="1"><tr><td style="background-color: white; color: black;"><div style="text-align: center;">Delivery Charge</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Cash Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Card Tip</div></td></tr>';
	$numba = 0;
	$results1 = "SELECT deliverycharge, tip, cardtip FROM dominos WHERE driverid=? AND deliverycharge > 0 ORDER BY id ASC";
	$statement1 = $mysqli->prepare($results1);
	$statement1->bind_param('s', $_SESSION["dominosdriver"]);
	$statement1->execute();
	$statement1->bind_result($dcharge, $dtip, $dcardtip);
	$tiptotal = 0;
	$chargetotal = 0;
	$cardtiptotal = 0;
	mysqli_stmt_store_result($statement1);
	$dreim = 1.05 * $statement1->num_rows;
	while($statement1->fetch()) {
	
		echo '<tr><td>';
		if ($dcharge == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dcharge, 2, '.', '').'</div>';
		}
		echo '</td><td>';
		if ($dtip == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dtip, 2, '.', '').'</div>';
		}
		echo '</td><td>';
		if ($dcardtip == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dcardtip, 2, '.', '').'</div>';
		}
		echo '</tr>';
		$tiptotal = $tiptotal + $dtip;
		$cardtiptotal = $cardtiptotal + $dcardtip;
		$chargetotal = $chargetotal + $dcharge;
	}
		
		
	echo '</table>';
	echo 'Toltal cash Orders: ' . $statement1->num_rows . '<br>';
	$alltipscombined1 = $cardtiptotal + $tiptotal;
	$tipsperdelivery1 = $alltipscombined1 / $statement1->num_rows;
	echo 'Toltal tips: ' . $alltipscombined1 . '<br>';
	echo 'Tip per order - cash: ' . number_format((float)$tipsperdelivery1, 2, '.', '') . '<br>';
	
	
	echo '<br><br>Credit Orders<br><table border="1"><tr><td style="background-color: white; color: black;"><div style="text-align: center;">Delivery Charge</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Cash Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Card Tip</div></td></tr>';
	$numba = 0;
	$results2 = "SELECT deliverycharge, tip, cardtip FROM dominos WHERE driverid=? AND deliverycharge = 0 ORDER BY id ASC";
	$statement2 = $mysqli->prepare($results2);
	$statement2->bind_param('s', $_SESSION["dominosdriver"]);
	$statement2->execute();
	$statement2->bind_result($dcharge, $dtip, $dcardtip);
	$tiptotal = 0;
	$chargetotal = 0;
	$cardtiptotal = 0;
	mysqli_stmt_store_result($statement2);
	$dreim = 1.05 * $statement2->num_rows;
	while($statement2->fetch()) {
	
		echo '<tr><td>';
		if ($dcharge == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dcharge, 2, '.', '').'</div>';
		}
		echo '</td><td>';
		if ($dtip == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dtip, 2, '.', '').'</div>';
		}
		echo '</td><td>';
		if ($dcardtip == 0) {
			echo '<div style="text-align: center;">-</div>';
		} else {
			echo '<div style="text-align: center;">$'. number_format((float)$dcardtip, 2, '.', '').'</div>';
		}
		echo '</tr>';
		$tiptotal = $tiptotal + $dtip;
		$cardtiptotal = $cardtiptotal + $dcardtip;
		$chargetotal = $chargetotal + $dcharge;
	}
		
		
	echo '</table>';
	echo 'Toltal credit Orders: ' . $statement2->num_rows . '<br>';
	$alltipscombined2 = $cardtiptotal + $tiptotal;
	$tipsperdelivery2 = $alltipscombined2 / $statement2->num_rows;
	echo 'Toltal tips: ' . $alltipscombined2 . '<br>';
	echo 'Tip per order - credit: ' . number_format((float)$tipsperdelivery2, 2, '.', '') . '<br><br>';
	
	$results3 = "SELECT deliverycharge, tip, cardtip FROM dominos WHERE tip = 0 AND cardtip = 0 ORDER BY id ASC";
	$statement3 = $mysqli->prepare($results3);
	$statement3->execute();
	$statement3->bind_result($dcharge, $dtip, $dcardtip);
	mysqli_stmt_store_result($statement3);
	echo 'Total Stiffs: ' . $statement3->num_rows .'<br>';
	
	$results3 = "SELECT deliverycharge, tip, cardtip FROM dominos WHERE tip = 0 AND cardtip = 0 AND deliverycharge = 0 ORDER BY id ASC";
	$statement3 = $mysqli->prepare($results3);
	$statement3->execute();
	$statement3->bind_result($dcharge, $dtip, $dcardtip);
	mysqli_stmt_store_result($statement3);
	echo 'Card Stiffs: ' . $statement3->num_rows .'<br>';
	
	$results3 = "SELECT deliverycharge, tip, cardtip FROM dominos WHERE tip = 0 AND cardtip = 0 AND deliverycharge > 0 ORDER BY id ASC";
	$statement3 = $mysqli->prepare($results3);
	$statement3->execute();
	$statement3->bind_result($dcharge, $dtip, $dcardtip);
	mysqli_stmt_store_result($statement3);
	echo 'Cash Stiffs: ' . $statement3->num_rows .'<br><br>';
	
	$mysqli->close();

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