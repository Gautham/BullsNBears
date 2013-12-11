<?php
	class Player {
		public $id;
		public $name;
		public $email;
		public $liquidCash;
		public $marketValue;
		public $shortValue;
		private $rank;
		public $dayWorth;
		public $weekWorth;
		private $Portfolio;
		private $Schedules;

		public function __construct($id = "", $name = "", $email = "") {
			global $mysqli;
			$this->id = $id;
			$this->Portfolio = array();
			$this->Schedules = array();
			if ($name) {
				global $startMoney;
				$this->name = $name;
				$this->email = $email;
				$this->liquidCash = $startMoney;
				$this->marketValue = 0;
				$this->shortValue = 0;
				$this->rank = 0;
				$this->dayWorth = $startMoney;
				$this->weekWorth = $startMoney;
				$statement = $mysqli->prepare("INSERT INTO `player` (`id`, `name`, `email`, `liquidCash`, `marketValue`, `shortValue`, `rank`, `dayWorth`, `weekWorth`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
				$temp = 0;
				$statement->bind_param('dssdddddd', $id, $name, $email, $startMoney, $temp, $temp, $temp, $startMoney, $startMoney);
				$statement->execute();
			} else {
				$r = $mysqli->query("SELECT * FROM player WHERE id = '$id'");
				$r = $r->fetch_assoc();
				$this->name = $r['name'];
				$this->email = $r['email'];
				$this->liquidCash = $r['liquidCash'];
				$this->marketValue = $r['marketValue'];
				$this->shortValue = $r['shortValue'];
				$this->rank = $r['rank'];
				$this->dayWorth = $r['dayWorth'];
				$this->weekWorth = $r['weekWorth'];
			}
		}

		public function isActive() {
			return $rank;
		}

		public function getStockData($symbol) {
			global $mysqli;
			$stockData = array();
			$r = $mysqli->query("SELECT amount, avg from boughtStocks WHERE id = '$this->id' AND symbol = '$symbol'");
			if ($r->num_rows) {
				$r = $r->fetch_assoc();
				$stockData['boughtAmount'] = $r['amount'];
				$stockData['boughtValue'] = $r['avg'];
			} else {
				$stockData['boughtAmount'] = 0;
				$stockData['boughtValue'] = 0;
			}
			$stockData['shortedAmount'] = 0;
			$stockData['shortedValue'] = 0;
			$r = $mysqli->query("SELECT amount, value from shortedStocks WHERE id = '$this->id' AND symbol = '$symbol'");
			if ($r->num_rows) {
				$r = $r->fetch_assoc();
				$stockData['shortedAmount'] = $r['amount'];
				$stockData['shortedValue'] = $r['value'];
			} else {
				$stockData['shortedAmount'] = 0;
				$stockData['shortedValue'] = 0;
			}
			if (!isset($stockData['boughtAmount'])) {
				$stockData['boughtAmount'] = 0;
				$stockData['boughtValue'] = 0;
			}
			return $stockData;
		}

		public function getPortfolio() {
			global $mysqli;
			if ($this->Portfolio) return $this->Portfolio;
			$r = $mysqli->query("SELECT * FROM boughtStocks WHERE id = '$this->id'");
			if ($r->num_rows) {
				while ($s = $r->fetch_assoc()) {
					$this->Portfolio[$s['symbol']]['boughtAmount'] = $s['amount'];
					$this->Portfolio[$s['symbol']]['boughtValue'] = $s['avg'];
					$this->Portfolio[$s['symbol']]['shortedAmount'] = 0;
					$this->Portfolio[$s['symbol']]['shortedValue'] = 0;
				}
			}
			$r = $mysqli->query("SELECT * FROM shortedStocks WHERE id = '$this->id'");
			if ($r->num_rows) {
				while ($s = $r->fetch_assoc()) {
					if (!isset($this->Portfolio[$s['symbol']])) {
						$this->Portfolio[$s['symbol']]['boughtAmount'] = 0;
						$this->Portfolio[$s['symbol']]['boughtValue'] = 0;
					}
					$this->Portfolio[$s['symbol']]['shortedAmount'] = $s['amount'];
					$this->Portfolio[$s['symbol']]['shortedValue'] = $s['value'];
				}
			}
		}

		public function getSchedules($limit = "") {
			global $mysqli;
			if ($this->Schedules) return $this->Schedules;
			if ($limit) $p = $mysqli->query("SELECT * FROM schedule WHERE id = '$this->id' LIMIT $limit");
			else $p = $mysqli->query("SELECT * FROM schedule WHERE id = '$this->id'");
			while ($q = $p->fetch_assoc()) $Schedules[$q['skey']] = $q;
			return $Schedules;
		}

		public function getHistory($limit = 20) {
			$History = array();
			if (!$this->rank) return $History;
			global $mysqli;
			$p = $mysqli->query("SELECT * FROM history WHERE id = '$this->id' LIMIT $limit");
			while ($q = $p->fetch_assoc()) $History[] = $q;
			return $History;
		}

		public function Buy($symbol, $amount, $skey = -1) {
			global $brokerage;
			global $mysqli;
			if ($amount < 0) return -2;
			$this->getPortfolio();
			$s = new Stock($symbol);
			$maxAmount = floor(($this->liquidCash - ($this->shortValue / 4)) / ((1 + $brokerage) * $s->value));
			$maxAmount = min($maxAmount, floor(($this->liquidCash + $this->marketValue) / (6 * (1 + $brokerage) * $s->value)) - ((isset($this->Portfolio[$symbol])) ? $this->Portfolio[$symbol]['boughtAmount'] : 0));
			if ($skey != -1) {
				$this->getSchedules();
				$maxAmount = min($maxAmount, $this->Schedules[$skey]['pendingAmount']);
			}
			$maxAmount = max($maxAmount, 0);
			if ($amount > $maxAmount) return -1;
			$mysqli->autocommit(FALSE);
			$p = $mysqli->query("INSERT INTO `boughtStocks` VALUES ( '$this->id', '$symbol', '$amount', '$s->value' ) ON DUPLICATE KEY UPDATE `avg` = ((`avg` * `amount`) + ".($amount * $s->value)." ) / (`amount` + ".$amount." ), `amount` = `amount` + ".$amount);
			if ($p) {
				$p = $mysqli->query("UPDATE `player` SET `rank` = 1, `liquidCash` = `liquidCash` - ".round($amount * $s->value * (1 + $brokerage)).", `marketValue` = `marketValue` + ".round($amount * $s->value)." WHERE `id` = '$this->id'");
				if ($p) {
					$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '$this->id', 'B', '$symbol', '$amount', '$s->value', '$skey' )"); 
					if ($p) {
						if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `pendingAmount` = `pendingAmount` - $amount WHERE `skey` = '$skey'"); 
						if ($p) {
							$mysqli->commit();
							$mysqli->autocommit(TRUE);
							$this->liquidCash -= round($amount * $s->value * (1 + $brokerage));
							$this->marketValue += round($amount * $s->value);
							$this->rank = 1;
							if (isset($this->Portfolio[$symbol])) {
								$this->Portfolio[$symbol]['boughtValue'] = ($this->Portfolio[$symbol]['boughtValue'] * $this->Portfolio[$symbol]['boughtAmount'] + $s->value * $amount) / ($this->Portfolio[$symbol]['boughtAmount'] + $amount);
								$this->Portfolio[$symbol]['boughtAmount'] += $amount;
							} else {
								$this->Portfolio[$symbol]['boughtValue'] = $s->value;
								$this->Portfolio[$symbol]['boughtAmount'] = $amount;
								$this->Portfolio[$symbol]['shortedValue'] = 0;
								$this->Portfolio[$symbol]['shortedAmount'] = 0;
							}
							if ($skey) $this->Schedules[$skey]['pendingAmount'] -= $amount;
							return 0;
						}
					}
				}
			}
			$mysqli->rollback();
			$mysqli->autocommit(TRUE);
			return 1;
		}

			
		public function Sell($symbol, $amount, $skey = -1) {
			global $mysqli;
			global $brokerage;
			if ($amount < 0) return -2;
			$this->getPortfolio();
			$maxAmount = (isset($this->Portfolio[$symbol])) ? $this->Portfolio[$symbol]['boughtAmount'] : 0;
			if ($skey != -1) {
				$this->getSchedules();
				$maxAmount = min($maxAmount, $this->Schedules[$skey]['pendingAmount']);
			}
			if ($amount > $maxAmount) return -1;
			$s = new Stock($symbol);
			$mysqli->autocommit(FALSE);
			if ($amount != $maxAmount) $p = $mysqli->query("UPDATE `boughtStocks` SET `avg` = ((`avg` * `amount`) - ".($amount * $s->value)." ) / (`amount` - ".$amount." ), `amount` = `amount` - ".$amount." WHERE `id` = '$this->id' AND `symbol` = '$symbol'");
			else $p = $mysqli->query("DELETE FROM `boughtStocks` WHERE `id` = '$this->id' AND symbol = '$symbol'"); 
			if ($p) {
				$p = $mysqli->query("UPDATE `player` SET `liquidCash` = `liquidCash` + ".round($amount * $s->value * (1 - $brokerage)).", `marketValue` = Case When (`marketValue` - ".round($amount*$s->value).") < 0 THEN 0 ELSE (`marketValue` - ".round($amount*$s->value).") END WHERE `id` = '$this->id'");
				if ($p) {
					$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '$this->id', 'S', '$symbol', '$amount', '$s->value', '$skey' )"); 
					if ($p) {
						if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `amount` = `amount` - $amount WHERE `skey` = '$skey'"); 
						if ($p){
							$mysqli->commit();
							$mysqli->autocommit(TRUE);
							$this->liquidCash += round($amount * $s->value * (1 - $brokerage));
							$this->marketValue -= round($amount * $s->value);
							$this->marketValue = max($this->marketValue, 0);
							if ($amount != $maxAmount) $this->Portfolio[$symbol]['boughtValue'] = ($this->Portfolio[$symbol]['boughtValue'] * $this->Portfolio[$symbol]['boughtAmount'] - $s->value * $amount) / ($this->Portfolio[$symbol]['boughtAmount'] - $amount);
							else $this->Portfolio[$symbol]['boughtValue'] = 0;
							$this->Portfolio[$symbol]['boughtValue'] = max($this->Portfolio[$symbol]['boughtValue'], 0);
							$this->Portfolio[$symbol]['boughtAmount'] -= $amount;
							if ($skey) $this->Schedules[$skey]['pendingAmount'] -= $amount;
							return 0;
						}
					}
				}
			}
			$mysqli->rollback();
			$mysqli->autocommit(TRUE);
			return 1;
		}

		public function ShortSell($symbol, $amount, $skey = -1) {
			global $brokerage;
			global $mysqli;
			if ($amount < 0) return -2;
			$this->getPortfolio();
			$s = new Stock($symbol);
			$maxAmount = floor((4 * $this->liquidCash - $this->shortValue) / ( $s->value * (1 + 2 * $brokerage)));
			$maxAmount = min($maxAmount, floor(($this->liquidCash + $this->marketValue - $this->shortValue) / (6 * $s->value * (1 + 2 * $brokerage))) - (isset($this->Portfolio[$symbol]) ? $this->Portfolio[$symbol]['shortedAmount'] : 0));
			if ($skey != -1) {
				$this->getSchedules();
				$maxAmount = min($maxAmount, $this->Schedules[$skey]['pendingAmount']);
			}
			$maxAmount = max($maxAmount, 0);			
			if ($amount > $maxAmount) return -1;
			$mysqli->autocommit(FALSE);
			$p = $mysqli->query("INSERT INTO `shortedStocks` VALUES( '$this->id', '$symbol', '$amount', '$s->value' ) ON DUPLICATE KEY UPDATE `value` = ((`value` * `amount`) + ".$amount * $s->value." ) / (`amount` + ".$amount." ), `amount` = `amount` + ".$amount);
			if ($p) {
				$p = $mysqli->query("UPDATE `player` SET `rank` = 1, `liquidCash` = `liquidCash` - ".round($amount * $s->value * $brokerage).", `shortValue` = `shortValue` + ".round($amount * $s->value)." WHERE `id` = '$this->id'");
				if ($p) {
					$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '$this->id', 'SS', '$symbol', '$amount', '$s->value', '$skey' )"); 
					if ($p) {
						if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `pendingAmount` = `pendingAmount` - $amount WHERE `skey` = '$skey'"); 
						if ($p){
							$mysqli->commit();
							$mysqli->autocommit(TRUE);
							$this->liquidCash -= round($amount * $s->value * $brokerage);
							$this->marketValue += round($amount * $s->value);
							$this->rank = 1;
							if (isset($this->Portfolio[$symbol])) {
								$this->Portfolio[$symbol]['shortedValue'] = ($this->Portfolio[$symbol]['shortedValue'] * $this->Portfolio[$symbol]['shortedAmount'] + $s->value * $amount) / ($this->Portfolio[$symbol]['shortedAmount'] + $amount);
								$this->Portfolio[$symbol]['shortedAmount'] += $amount;
							} else {
								$this->Portfolio[$symbol]['shortedValue'] = $s->value;
								$this->Portfolio[$symbol]['shortedAmount'] = $amount;
								$this->Portfolio[$symbol]['boughtValue'] = 0;
								$this->Portfolio[$symbol]['boughtAmount'] = 0;
							}
							if ($skey) $this->Schedules[$skey]['pendingAmount'] -= $amount;
							return 0;
						}
					}
				}
			}
			$mysqli->rollback();
			$mysqli->autocommit(TRUE);
			return 1;
		}


		public function Cover($symbol, $amount, $skey = -1) {
			global $mysqli;
			global $brokerage;
			if ($amount < 0) return -2;
			$this->getPortfolio();
			$mysqli->autocommit(FALSE);
			$maxAmount = (isset($this->Portfolio[$symbol])) ? $this->Portfolio[$symbol]['shortedAmount'] : 0;
			if ($skey != -1) {
				$this->getSchedules();
				$maxAmount = min($maxAmount, $this->Schedules[$skey]['pendingAmount']);
			}
			if ($amount > $maxAmount) return -1;
			$s = new Stock($symbol);
			if ($amount != $maxAmount) $p = $mysqli->query("UPDATE `shortedStocks` SET `value` = ((`value` * `amount`) - ".($amount * $s->value)." ) / (`amount` - ".$amount." ), `amount` = `amount` - ".$amount." WHERE `id` = '$this->id' AND `symbol` = '$symbol'");
			else $p = $mysqli->query("DELETE FROM `shortedStocks` WHERE `id` = '$this->id' AND symbol = '$symbol'"); 
			if ($p) {
				$p = $mysqli->query("UPDATE `player` SET `liquidCash` = `liquidCash` + ".round(($this->Portfolio[$symbol]['shortedValue'] - $s->value * (1 + $brokerage)) * $amount).", `shortValue` = Case When (`shortValue` - ".round($amount * $s->value).") < 0 THEN 0 ELSE (`shortValue` - ".round($amount * $s->value).") END WHERE `id` = '$this->id'");
				if ($p) {
					$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '$this->id', 'C', '$symbol', '$amount', '$s->value', '$skey' )"); 
					if ($p) {
						if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `amount` = `amount` - $amount WHERE `skey` = '$skey'"); 
						if ($p){
							$mysqli->commit();
							$mysqli->autocommit(TRUE);
							$this->liquidCash += round(($this->Portfolio[$symbol]['shortedValue'] - $s->value * (1 + $brokerage)) * $amount);
							$this->shortValue -= round($amount * $s->value);
							$this->shortValue = max($this->shortValue, 0);
							if ($amount != $maxAmount) $this->Portfolio[$symbol]['shortedValue'] = ($this->Portfolio[$symbol]['shortedValue'] * $this->Portfolio[$symbol]['shortedAmount'] - $s->value * $amount) / ($this->Portfolio[$symbol]['shortedAmount'] - $amount);
							else $this->Portfolio[$symbol]['shortedValue'] = 0;
							$this->Portfolio[$symbol]['shortedAmount'] -= $amount;
							if ($skey) $this->Schedules[$skey]['pendingAmount'] -= $amount;
							return 0;
						}
					}
				}
				$mysqli->rollback();
				$mysqli->autocommit(TRUE);
				return 1;
			}
		}

		private function Schedule($symbol, $amount, $scheduledPrice, $type) {
			global $mysqli;
			if ($amount < 0) return -2;
			if ($amount > 5000) return -1;
			$s = new Stock($symbol);
			if ($scheduledPrice <= $s->value) $flag = "l";
			else $flag = "g";
			$t = $mysqli->query("INSERT INTO `schedule` ( `id`, `symbol`, `type`, `scheduledPrice`, `amount`, `pendingAmount`, `flag`) VALUES ( '$this->id', '$symbol', '$type', '$scheduledPrice', '$amount', '$amount', '$flag' )");
			if ($t) return 0;
			else return 1;			
		}

		public function ScheduleBuy($symbol, $amount, $scheduledPrice) {
			$this->Schedule($symbol, $amount, $scheduledPrice, 'B');
		}

		public function ScheduleSell($symbol, $amount, $scheduledPrice) {
			$this->Schedule($symbol, $amount, $scheduledPrice, 'S');
		}

		public function ScheduleShortSell($symbol, $amount, $scheduledPrice) {
			$this->Schedule($symbol, $amount, $scheduledPrice, 'SS');
		}

		public function ScheduleCover($symbol, $amount, $scheduledPrice) {
			$this->Schedule($symbol, $amount, $scheduledPrice, 'C');
		}

	}
?>