<?php
require_once("../includes/bnbConfig.php");
if ($_GET['token'] == $token && isset($_GET['symbol'])) {
	$mysqli = new mysqli($server, $sqlId, $sqlPass, $db);
	$r = $mysqli->query("SELECT value FROM stocks WHERE symbol = '{$_GET['symbol']}'");
	$r = $r->fetch_assoc();
	$r = $r['value'];
	$mysqli->query("INSERT INTO history ( id, type, symbol, amount, value, skey ) (SELECT id, 'S', '{$_GET['symbol']}', amount, '{$r}' as value, 0 FROM boughtStocks WHERE boughtStocks.symbol = '{$_GET['symbol']}')");
	$mysqli->query("INSERT INTO history ( id, type, symbol, amount, value, skey ) (SELECT id, 'C', '{$_GET['symbol']}', amount, '{$r}' as value, 0 FROM shortedStocks WHERE shortedStocks.symbol = '{$_GET['symbol']}')");
	$mysqli->query("UPDATE player, (SELECT id, amount, '{$r}' as value FROM boughtStocks WHERE boughtStocks.symbol = '{$_GET['symbol']}') B SET liquidCash = liquidCash + B.amount * B.value * (1 - $brokerage), marketValue = CASE WHEN marketValue - B.amount * B.value < 0 THEN 0 ELSE marketValue - B.amount * B.value END");
	$mysqli->query("UPDATE player, (SELECT id, amount, shortedStocks.value as val, '{$r}' as value FROM shortedStocks WHERE shortedStocks.symbol = '{$_GET['symbol']}') SS SET liquidCash = liquidCash + SS.amount * (SS.val - SS.value * (1 - $brokerage)), shortValue = CASE WHEN shortValue - SS.amount * SS.value < 0 THEN 0 ELSE shortValue - SS.amount * SS.value END");
	$mysqli->query("DELETE FROM boughtStocks WHERE symbol = '{$_GET['symbol']}'");
	$mysqli->query("DELETE FROM shortedStocks WHERE symbol = '{$_GET['symbol']}'");
	$mysqli->query("DELETE FROM schedule WHERE symbol = '{$_GET['symbol']}'");
	$mysqli->query("DELETE FROM stocks WHERE symbol = '{$_GET['symbol']}'");
} else echo "FAIL;