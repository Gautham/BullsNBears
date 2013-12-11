<?php
require_once("../includes/bnbConfig.php");
if ($_GET['token'] == $token) {
	$mysqli = new mysqli($server, $sqlId, $sqlPass, $db);
	$mysqli->query("INSERT INTO history (id, type, symbol, amount, value) SELECT id, 'c', symbol, amount, val FROM shortedStocks");
	$mysqli->query("CREATE TEMPORARY TABLE ShortSell (id VARCHAR(15) NOT NULL, value DECIMAL(15, 2) NOT NULL DEFAULT 0) ENGINE=MEMORY;");
	$mysqli->query("INSERT INTO ShortSell (SELECT id, SUM(stocks.value * (1 - $brokerage) * amount - shortedStocks.value * amount) as S FROM shortedStocks LEFT JOIN stocks ON shortedStocks.symbol = stocks.symbol)");	
	$mysqli->query("UPDATE player, ShortSell SET player.liquidCash = player.liquidCash + ShortSell.value, shortValue = 0 WHERE player.id = ShortSell.id");
	$mysqli->query("DELETE FROM shortedStocks WHERE 1");
	echo $mysqli->error;
	echo(date("Y-m-d",time()));
} else echo "FAIL";
?>
