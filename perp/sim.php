<?php
require_once("../includes/bnbConfig.php");

	session_start();
	if ($_GET['token'] != $token) die();
	$err_flag = FALSE;
	$mysqli = new mysqli($server, $sqlId, $sqlPass, $db);
	$mysqli->autocommit(FALSE);
	$mysqli->query("DELETE FROM `boughtStocks` WHERE 1") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("DELETE FROM `shortedStocks` WHERE 1") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE `player` SET liquidCash = '{$startMoney}', marketValue = 0 WHERE rank = 1") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE `player` SET liquidCash = liquidCash - (SELECT SUM(amount * value * (1 + $brokerage)) FROM history WHERE history.id = player.id and type = 'B' GROUP BY history.id)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE `player` SET liquidCash = liquidCash + (SELECT SUM(amount * value * (1 - $brokerage)) FROM history WHERE history.id = player.id and type = 'S' GROUP BY history.id)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE `player` SET liquidCash = liquidCash - (SELECT SUM(amount * value * $brokerage) FROM history WHERE history.id = player.id and (type = 'C' OR type = 'SS') GROUP BY history.id)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("INSERT INTO boughtStocks (SELECT id, symbol, SUM(amount) as amt, (SUM(amount * value) / SUM(amount)) as value FROM history WHERE type = 'B' GROUP BY id, symbol)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE boughtStocks dest, (SELECT id, symbol, SUM(amount) as amt, (SUM(amount * value) / SUM(amount)) as value FROM history WHERE type = 'S' GROUP BY id, symbol) src SET dest.amount = dest.amount - src.amt, dest.avg = (dest.amount * dest.avg - src.amt * src.value) / (dest.amount - src.amt) WHERE dest.id = src.id AND dest.symbol = src.symbol") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("INSERT INTO shortedStocks (SELECT id, symbol, SUM(amount) as amt, (SUM(amount * value) / SUM(amount)) as value FROM history WHERE type = 'SS' GROUP BY id, symbol)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE player dest, (SELECT id, symbol, SUM(amount) as amt, (SUM(amount * value) / SUM(amount)) as value FROM history WHERE type = 'C' GROUP BY id, symbol) src, shortedStocks srcb SET dest.liquidCash = dest.liquidCash + (srcb.value - src.value) * src.amt WHERE dest.id = src.id AND src.symbol = srcb.symbol AND dest.id = srcb.id") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE shortedStocks dest, (SELECT id, symbol, SUM(amount) as amt, (SUM(amount * value) / SUM(amount)) as value FROM history WHERE type = 'C' GROUP BY id, symbol) src SET dest.amount = dest.amount - src.amt, dest.value = (dest.amount * dest.value - src.amt * src.value) / (dest.amount - src.amt) WHERE dest.id = src.id AND dest.symbol = src.symbol") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("DELETE FROM `boughtStocks` WHERE amount = 0") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("DELETE FROM `shortedStocks` WHERE amount = 0") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE `player` SET shortValue = (SELECT SUM(amount * value) FROM shortedStocks WHERE shortedStocks.id = player.id GROUP BY shortedStocks.id)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("CREATE TEMPORARY TABLE MarketVal (id VARCHAR(15) NOT NULL, b_amount INT NOT NULL DEFAULT 0,  ss_amount INT NOT NULL DEFAULT 0, ss_value DECIMAL(15, 2) NOT NULL DEFAULT 0, value INT) ENGINE=MEMORY;") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("INSERT INTO MarketVal (SELECT b.id as ID, IFNULL((b.amount), 0) AS b_amount, IFNULL((ss.amount), 0), IFNULL((ss.value), 0) AS ss_value, s.value FROM shortedStocks AS ss RIGHT JOIN boughtStocks AS b ON b.symbol = ss.symbol AND b.id = ss.id LEFT JOIN stocks AS s ON s.symbol = b.symbol) UNION (SELECT ss.id as ID, IFNULL((b.amount), 0) AS b_amount, IFNULL((ss.amount), 0), IFNULL((ss.value), 0) AS ss_value, s.value FROM shortedStocks AS ss LEFT JOIN boughtStocks AS b ON ss.symbol = b.symbol AND ss.id = b.id LEFT JOIN stocks AS s ON s.symbol = ss.symbol)") or $err_flag = TRUE;
	if (!$err_flag) $mysqli->query("UPDATE player SET marketValue = (SELECT SUM(MarketVal.b_amount * MarketVal.value) + SUM(MarketVal.ss_amount * (MarketVal.ss_value - MarketVal.value)) from MarketVal WHERE MarketVal.id = player.id) WHERE rank = 1") or $err_flag = TRUE;
	if (!$err_flag) {
		$mysqli->commit();
		echo "Success!";
	} else {
		$mysqli->rollback();
		echo "Failure!";
	}
	$mysqli->autocommit(TRUE);
?>