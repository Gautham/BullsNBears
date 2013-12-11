<?php
require_once("../includes/bnbConfig.php");

if ($_GET['token'] == $token) {
	$mysqli = new mysqli($server, $sqlId, $sqlPass, $db);
	$mysqli->query("UPDATE player SET dayWorth = liquidCash + marketValue");
	$mysqli->query("DELETE FROM schedule WHERE pendingAmount = 0");
	echo(date("Y-m-d",time()));
} else echo "FAIL";
?>
