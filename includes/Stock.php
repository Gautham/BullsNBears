<?php
	class Stock {
		public $symbol;
		public $name;
		public $lastUpdated;
		public $change;
		public $changePerc;
		public $dayHigh;
		public $dayLow;
		public $weekHigh;
		public $weekLow;
		public $value;

		public function __construct($symbol, $value = "", $name = "", $lastUpdated = "", $change = "", $changePerc = "", $dayHigh = "", $dayLow = "", $weekHigh = "", $weekLow = "") {
			$this->symbol = $symbol;
			global $mysqli;
			if ($value) {
				$this->name = $name;
				$this->value = $value;
				$this->lastUpdated = $lastUpdated;
				$this->change = $change;
				$this->changePerc = $changePerc;
				$this->dayHigh = $dayHigh;
				$this->dayLow = $dayLow;
				$this->weekHigh = $weekHigh;
				$this->weekLow = $weekLow;
				$mysqli->query("INSERT INTO `stocks` (`symbol`, `value`, `name`, `time`, `change`, `changePerc`, `dayHigh`, `dayLow`, `weekHigh`, `weekLow`) VALUES ($symbol, $value, $name, $lastUpdated, $change, $changePerc, $dayHigh, $dayLow, $weekHigh, $weekLow)");
			} else {
				$r = $mysqli->query("SELECT * FROM stocks WHERE symbol = '$symbol'");
				if ($r->num_rows) {
					$r = $r->fetch_assoc();
					$this->name = $r['name'];
					$this->lastUpdated = $r['time'];
					$this->change = $r['change'];
					$this->changePerc = $r['changePerc'];
					$this->dayLow = $r['dayLow'];
					$this->dayHigh = $r['dayHigh'];
					$this->weekLow = $r['weekLow'];
					$this->weekHigh = $r['weekHigh'];
					$this->value = $r['value'];
				} else {
					$this->name = 0;
					$this->lastUpdated = 0;
					$this->change = 0;
					$this->changePerc = 0;
					$this->dayLow = 0;
					$this->dayHigh = 0;
					$this->weekLow = 0;
					$this->weekHigh = 0;
					$this->value = 0;		
				}
			}
		}
	}
?>