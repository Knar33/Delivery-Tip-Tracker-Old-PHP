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
<script type="text/javascript">
function confirm_alert2(node) {
    return confirm("Are you sure you want to log out?");
}
function confirm_alert(node) {
    return confirm("Are you sure you want to delete this entry?");
}
</script>
';

date_default_timezone_set('America/Los_Angeles');

if($_SESSION["dominosdriver"]) {
	$mysqli = new mysqli('localhost', 'ab19320_admin', 'godsmile5', 'ab19320_atdmain');
	if($mysqli->connect_errno > 0){
		die('Unable to connect to database [' . $mysqli->connect_error . ']');
	}
	global $mysqli;
	
	$datee = $_GET["date"];
	$fun = $_GET["fun"];
	$deleteid = $_GET["id"];
	
	echo '<a href="index.php">New Order</a> | <a href="profile.php">Past Orders</a> | <a href="timeclock.php">Time Clock</a> | <a href="logout.php" onclick="return confirm_alert2(this);">Logout</a><br><br>';
	
	if ($fun == 'viewall') {
		$results = "SELECT DISTINCT date FROM dominos WHERE driverid = ? ORDER BY date DESC";
		$statement = $mysqli->prepare($results);
		$statement->bind_param('s', $_SESSION["dominosdriver"]);
		$statement->execute();
		$statement->bind_result($ddate);
		mysqli_stmt_store_result($statement);
		if ($statement->num_rows > 0) {
			echo 'Recent Delivery Dates<br>';
			while($statement->fetch()) {
				echo '<a href="profile.php?date='.$ddate.'">'.$ddate.'</a><br>';
			}  
			echo '<br>';
		}
	} else {
		//delete an order
		if ($fun == 'delete') {
			$results2 = "DELETE FROM dominos WHERE id=?";
			$statement2 = $mysqli->prepare($results2);
			$statement2->bind_param('s', $deleteid);
			$statement2->execute(); 
		}
		//if a date is specified
		if (!empty($datee)) {
			echo $datee.'<br>';
			$results = "SELECT deliverycharge, tip, cardtip, id FROM dominos WHERE driverid=? AND date=? ORDER BY id ASC";
			$statement = $mysqli->prepare($results);
			$statement->bind_param('ss', $_SESSION["dominosdriver"], $datee);
			$statement->execute();
			$statement->bind_result($dcharge, $dtip, $dcardtip, $did);
			$tiptotal = 0;
			$chargetotal = 0;
			$cardtiptotal = 0;
			mysqli_stmt_store_result($statement);
			$dreim = 0.85 * $statement->num_rows;
			if ($statement->num_rows > 0) {
				echo '<table border="1" border-color="white"><tr><td style="background-color: white; color: black;"><div style="text-align: center;">Delivery Charge</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Cash Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Card Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Delete</div></td></tr>';
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
					echo '</td><td><div style="text-align: center;"><a href="profile.php?fun=delete&id='.$did.'" onclick="return confirm_alert(this);">X</a></div></td></tr>';
					$tiptotal = $tiptotal + $dtip;
					$cardtiptotal = $cardtiptotal + $dcardtip;
					$chargetotal = $chargetotal + $dcharge;
				}
			
				$totaltipscombined = $cardtiptotal + $tiptotal;
				echo '<tr><td style="background-color: white; color: black;"><div style="text-align: center;">Total charges</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Total Cash Tips</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Total Card Tips</div></td><td style="background-color: white; color: black;"></td></tr>
					<tr><td><div style="text-align: center;">$'.number_format((float)$chargetotal, 2, '.', '').'</div></td><td><div style="text-align: center;">$'.number_format((float)$tiptotal, 2, '.', '').'</div></td><td><div style="text-align: center;">$'.number_format((float)$cardtiptotal, 2, '.', '').'</div></td><td></td></tr></table>
					<br><table table border="1">
						<tr><td style="background-color: white; color: black;">Deliveries</td><td>'.$statement->num_rows.'</td></tr>
						<tr><td style="background-color: white; color: black;">Total Tips</td><td>$'.number_format((float)$totaltipscombined, 2, '.', '').'</td></tr>
						<tr><td style="background-color: white; color: black;">Driver reimbursement</td><td>$'.number_format((float)$dreim, 2, '.', '').'</td></tr>';
					$amountmade = $totaltipscombined + $dreim;
					$amountowed =  20 + $chargetotal - $cardtiptotal - $dreim;
					echo '<tr><td style="background-color: white; color: black;">Total Amount Made</td><td>$'.number_format((float)$amountmade, 2, '.', '').'</td></tr>';
					
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
								echo '<tr><td style="background-color: white; color: black;">Tips per hour</td><td>$'.number_format((float)$totaltipz, 2, '.', '').'</td></tr>';
							} else {
								//dont divide by zero bro
								if ($dtotaltimeminutes > 0) {
									$tph = $totaltipz / $dtotaltimeminutes;
								}
								$tph = $tph * 60;
								$tph = round($tph, 2);
								echo '<tr><td style="background-color: white; color: black;">Tips per hour</td><td>$'.number_format((float)$tph, 2, '.', '').'</td></tr>';
							}
						}
					}
					
					echo '<tr><td style="background-color: white; color: black;">Amount owed to store</td><td>$'.number_format((float)$amountowed, 2, '.', '').'</td></tr>';
					echo '</table>';
			} else {
				echo 'No orders yet. Click "New Order" to enter one.<br>';
			}		
			$results = "SELECT DISTINCT date FROM dominos WHERE driverid = ? ORDER BY date DESC LIMIT 5";
			$statement = $mysqli->prepare($results);
			$statement->bind_param('s', $_SESSION["dominosdriver"]);
			$statement->execute();
			$statement->bind_result($ddate);
			mysqli_stmt_store_result($statement);
			if ($statement->num_rows > 0) {
				echo '<br>Recent Delivery Dates<br><a href="profile.php?fun=viewall">(View All)</a><br>';
				while($statement->fetch()) {
					echo '<a href="profile.php?date='.$ddate.'">'.$ddate.'</a><br>';
				}  
				echo '<br>';
			}
		//if no date is specified, use today's date
		} else {
			$datetoday = date("Y-m-d");
			$results = "SELECT deliverycharge, tip, cardtip, id FROM dominos WHERE driverid=? AND date=? ORDER BY id ASC";
			$statement = $mysqli->prepare($results);
			$statement->bind_param('ss', $_SESSION["dominosdriver"], $datetoday);
			$statement->execute();
			$statement->bind_result($dcharge, $dtip, $dcardtip, $did);
			$tiptotal = 0;
			$chargetotal = 0;
			$cardtiptotal = 0;
			mysqli_stmt_store_result($statement);
			$dreim = 0.85 * $statement->num_rows;
			if ($statement->num_rows > 0) {
				echo 'Today\'s Orders:<br><table border="1"><tr><td style="background-color: white; color: black;"><div style="text-align: center;">Delivery Charge</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Cash Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Card Tip</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Delete</div></td></tr>';
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
					echo '</td><td><div style="text-align: center;"><a href="profile.php?fun=delete&id='.$did.'" onclick="return confirm_alert(this);">X</a></div></td></tr>';
					$tiptotal = $tiptotal + $dtip;
					$cardtiptotal = $cardtiptotal + $dcardtip;
					$chargetotal = $chargetotal + $dcharge;
				}
				
				$totaltipscombined = $cardtiptotal + $tiptotal;
				echo '<tr><td style="background-color: white; color: black;"><div style="text-align: center;">Total charges</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Total Cash Tips</div></td><td style="background-color: white; color: black;"><div style="text-align: center;">Total Card Tips</div></td><td style="background-color: white; color: black;"></td></tr>
					<tr><td><div style="text-align: center;">$'.number_format((float)$chargetotal, 2, '.', '').'</div></td><td><div style="text-align: center;">$'.number_format((float)$tiptotal, 2, '.', '').'</div></td><td><div style="text-align: center;">$'.number_format((float)$cardtiptotal, 2, '.', '').'</div></td><td></td></tr></table>
					<br><table table border="1">
						<tr><td style="background-color: white; color: black;">Deliveries</td><td>'.$statement->num_rows.'</td></tr>
						<tr><td style="background-color: white; color: black;">Total Tips</td><td>$'.number_format((float)$totaltipscombined, 2, '.', '').'</td></tr>
						<tr><td style="background-color: white; color: black;">Driver reimbursement</td><td>$'.number_format((float)$dreim, 2, '.', '').'</td></tr>';
					$amountmade = $totaltipscombined + $dreim;
					$amountowed =  20 + $chargetotal - $cardtiptotal - $dreim;
					echo '<tr><td style="background-color: white; color: black;">Total Amount Made</td><td>$'.number_format((float)$amountmade, 2, '.', '').'</td></tr>';
					
				
				//get timeclock information to determine tips per hour
				$driverid = $_SESSION["dominosdriver"];
				$results5 = "SELECT clockedin FROM dominosdrivers WHERE id = ?";
				$statement5 = $mysqli->prepare($results5);
				$statement5->bind_param('s', $driverid);
				$statement5->execute();
				$statement5->bind_result($clockedin);
				mysqli_stmt_store_result($statement5);
				while($statement5->fetch()) {
					if ($clockedin == 'yes') {
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
							
							$datetoday = date("Y-m-d");
							$resultsx = "SELECT tip, cardtip, id FROM dominos WHERE driverid=? AND date=? ORDER BY id ASC";
							$statementx = $mysqli->prepare($resultsx);
							$statementx->bind_param('ss', $_SESSION["dominosdriver"], $datetoday);
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
								
								
								//dont divide by zero bro
								if ($dtotaltimeminutes > 0) {
									$tph = $totaltipz / $dtotaltimeminutes;
								}
								$tph = $tph * 60;
								$tph = round($tph, 2);
								echo '<tr><td style="background-color: white; color: black;">Tips per hour</td><td>$'.number_format((float)$tph, 2, '.', '').'</td></tr>';

							}
						}
					} else {
						echo 'You are not clocked in<br><a href="timeclock.php">Click here to clock in</a><br><br>';
					}
				}
				$totalcash = $amountowed + $amountmade;
				echo '<tr><td style="background-color: white; color: black;">Total cash on hand</td><td>$'.number_format((float)$totalcash, 2, '.', '').'</td></tr>';
				echo '<tr><td style="background-color: white; color: black;">Amount owed to store</td><td>$'.number_format((float)$amountowed, 2, '.', '').'</td></tr>';
				echo '</table>';
			} else {
				echo 'No orders yet. Click "New Order" to enter one.<br>';
				$driverid = $_SESSION["dominosdriver"];
				$results5 = "SELECT clockedin FROM dominosdrivers WHERE id = ?";
				$statement5 = $mysqli->prepare($results5);
				$statement5->bind_param('s', $driverid);
				$statement5->execute();
				$statement5->bind_result($clockedin);
				mysqli_stmt_store_result($statement5);
				while($statement5->fetch()) {
					if ($clockedin == 'no') {
						echo '<br>You are not clocked in<br><a href="timeclock.php">Click here to clock in</a><br><br>';
					}
				}
			}
			$results = "SELECT DISTINCT date FROM dominos WHERE driverid = ? ORDER BY date DESC LIMIT 5";
			$statement = $mysqli->prepare($results);
			$statement->bind_param('s', $_SESSION["dominosdriver"]);
			$statement->execute();
			$statement->bind_result($ddate);
			mysqli_stmt_store_result($statement);
			if ($statement->num_rows > 0) {
				echo '<br>Recent Delivery Dates<br><a href="profile.php?fun=viewall">(View All)</a><br>';
				while($statement->fetch()) {
					echo '<a href="profile.php?date='.$ddate.'">'.$ddate.'</a><br>';
				}  
				echo '<br>';
			}
		}
		$mysqli->close();
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