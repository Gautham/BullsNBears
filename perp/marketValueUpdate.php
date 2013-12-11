<?php
require_once("../includes/bnbConfig.php");
if ($_GET['token'] == $token) {
	$mysqli = new mysqli($server, $sqlId, $sqlPass, $db);
	$err_flag = FALSE;
	$mysqli->autocommit(FALSE);
	$mysqli->query("CREATE TEMPORARY TABLE MarketVal (id VARCHAR(15) NOT NULL, b_amount INT NOT NULL DEFAULT 0,  ss_amount INT NOT NULL DEFAULT 0, ss_value DECIMAL(15, 2) NOT NULL DEFAULT 0, value INT) ENGINE=MEMORY;") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("INSERT INTO MarketVal (SELECT b.id as ID, IFNULL((b.amount), 0) AS b_amount, IFNULL((ss.amount), 0), IFNULL((ss.value), 0) AS ss_value, s.value FROM shortedStocks AS ss RIGHT JOIN boughtStocks AS b ON b.symbol = ss.symbol AND b.id = ss.id LEFT JOIN stocks AS s ON s.symbol = b.symbol) UNION (SELECT ss.id as ID, IFNULL((b.amount), 0) AS b_amount, IFNULL((ss.amount), 0), IFNULL((ss.value), 0) AS ss_value, s.value FROM shortedStocks AS ss LEFT JOIN boughtStocks AS b ON ss.symbol = b.symbol AND ss.id = b.id LEFT JOIN stocks AS s ON s.symbol = ss.symbol)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE player SET marketValue = (SELECT SUM(MarketVal.b_amount * MarketVal.value) + SUM(MarketVal.ss_amount * (MarketVal.ss_value - MarketVal.value)) from MarketVal WHERE MarketVal.id = player.id) WHERE rank = 1") or $err_flag = TRUE;
	if (!$err_flag) {
		echo(date("Y-m-d",time()));
		$mysqli->commit();	
	} else {
		echo("Failure");
		$mysqli->rollback();
	}
	$mysqli->autocommit(TRUE);
} else echo "FAIL";
?>