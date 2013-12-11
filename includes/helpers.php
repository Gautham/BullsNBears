<?php
	function getMarket() {
		global $mysqli;
		$Stocks = array();
		$r = $mysqli->query("SELECT * FROM stocks");
		while ($s = $r->fetch_assoc()) $Stocks[$s['symbol']] = $s;
		return $Stocks;
	}

	function getOverallRankings() {
		global $mysqli;
		$r = $mysqli->query("SELECT `id`, `name`,  `marketValue` + `liquidCash` AS total FROM `player` WHERE `rank` <> 0 ORDER BY `total` DESC LIMIT 15");
		$Rankings = array();
		while ($s = $r->fetch_assoc()) $Rankings[] = $s;
		return $Rankings;
	}
	function getDailyRankings() {
		global $mysqli;
		$r = $mysqli->query("SELECT `id`, `name`,  `marketValue` + `liquidCash` - `dayWorth` AS total FROM `player` WHERE `rank` <> 0 ORDER BY `total` DESC LIMIT 15");
		$Rankings = array();
		while ($s = $r->fetch_assoc()) $Rankings[] = $s;
		return $Rankings;
	}

	function getWeeklyRankings() {
		global $mysqli;
		$r = $mysqli->query("SELECT `id`, `name`,  `marketValue` + `liquidCash` - `weekWorth` AS total FROM `player` WHERE `rank` <> 0 ORDER BY `total` DESC LIMIT 15");
		$Rankings = array();
		while ($s = $r->fetch_assoc()) $Rankings[] = $s;
		return $Rankings;
	}

?>