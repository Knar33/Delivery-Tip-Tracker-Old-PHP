<?php
session_start();
unset($_SESSION["dominosdriver"]);
header("Location:index.php");
?>