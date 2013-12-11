<?php	
require_once("bnbConfig.php");

	function Buy($id, $symbol, $amount, $stock_data, $skey) {
		global $mysqli;
		$err_flag = TRUE;
		$mysqli->autocommit(FALSE);
		$p = $mysqli->query("INSERT INTO `boughtStocks` VALUES( '{$id}', '{$symbol}', '{$amount}', '{$stock_data['value']}' ) ON DUPLICATE KEY UPDATE `avg` = ((`avg` * `amount`) + ".($amount * $stock_data['value'])." ) / (`amount` + ".$amount." ), `amount` = `amount` + ".$amount);
		if ($p) {
			$p = $mysqli->query("UPDATE `player` SET `rank` = 1, `liquidCash` = `liquidCash` - ".round($amount * $stock_data['value'] * (1 + $brokerage)).", `marketValue` = `marketValue` + ".round($amount * $stock_data['value'])." WHERE `id` = '{$id}'");
			if ($p) {
				$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '{$id}', 'B', '{$symbol}', '{$amount}', '{$stock_data['value']}', '{$skey}' )"); 
				if ($p) {
					if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `pendingAmount` = `pendingAmount` - {$amount} WHERE `skey` = '{$skey}'"); 
					if (!$p) $mysqli->rollback();
					else {
						$mysqli->commit();
						$err_flag = FALSE;
					}
				} else $mysqli->rollback();
			} else $mysqli->rollback(); 
		} else $mysqli->rollback();
		$mysqli->autocommit(TRUE);
		return !$err_flag;
	}


	function Sell($id, $symbol, $amount, $stock_data, $skey) {
		global $mysqli;
		$err_flag = TRUE;
		$mysqli->autocommit(FALSE);
		if ($amount != $stock_data['bought_amount']) $p = $mysqli->query("UPDATE `boughtStocks` SET `avg` = ((`avg` * `amount`) - ".($amount * $stock_data['value'])." ) / (`amount` - ".$amount." ), `amount` = `amount` - ".$amount." WHERE `id` = '{$id}' AND `symbol` = '{$symbol}'");
		else $p = $mysqli->query("DELETE FROM `boughtStocks` WHERE `id` = '{$id}' AND symbol = '{$symbol}'"); 
		if ($p) {
			$p = $mysqli->query("UPDATE `player` SET `liquidCash` = `liquidCash` + ".round($amount * $stock_data['value'] * (1 - $brokerage)).", `marketValue` = Case When (`marketValue` - ".ceil($amount*$stock_data['value']).") < 0 THEN 0 ELSE (`marketValue` - ".ceil($amount*$stock_data['value']).") END WHERE `id` = '{$id}'");
			if ($p) {
				$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '{$id}', 'S', '{$symbol}', '{$amount}', '{$stock_data['value']}', '{$skey}' )"); 
				if ($p) {
					if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `pendingAmount` = `pendingAmount` - {$amount} WHERE `skey` = '{$skey}'"); 
					if (!$p) $mysqli->rollback();
					else {
						$mysqli->commit();
						$err_flag = FALSE;
					}
				} else $mysqli->rollback();
			} else $mysqli->rollback(); 
		} else $mysqli->rollback();
		$mysqli->autocommit(TRUE);
		return !$err_flag;
	}

	function Short($id, $symbol, $amount, $stock_data, $skey) {
		global $mysqli;
		$err_flag = TRUE;
		$mysqli->autocommit(FALSE);
		$p = $mysqli->query("INSERT INTO `shortedStocks` VALUES( '{$id}', '{$symbol}', '{$amount}', '{$stock_data['value']}' ) ON DUPLICATE KEY UPDATE `value` = ((`value` * `amount`) + ".($amount * $stock_data['value'])." ) / (`amount` + ".$amount." ), `amount` = `amount` + ".$amount);
		if ($p) {
			$p = $mysqli->query("UPDATE `player` SET `rank` = 1, `liquidCash` = `liquidCash` - ".round($amount * $stock_data['value'] * $brokerage).", `shortValue` = `shortValue` + ".round($amount * $stock_data['value'] )." WHERE `id` = '{$id}'");
			if ($p) {
				$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '{$id}', 'SS', '{$symbol}', '{$amount}', '{$stock_data['value']}', '{$skey}' )"); 
				if ($p) {
					if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `pendingAmount` = `pendingAmount` - {$amount} WHERE `skey` = '{$skey}'"); 
					if (!$p) $mysqli->rollback();
					else {
						$mysqli->commit();
						$err_flag = FALSE;
					}
				} else $mysqli->rollback();
			} else $mysqli->rollback(); 
		} else $mysqli->rollback();
		$mysqli->autocommit(TRUE);
		return !$err_flag;
	}

	function Cover($id, $symbol, $amount, $stock_data, $skey) {
		global $mysqli;
		$err_flag = TRUE;
		$mysqli->autocommit(FALSE);
		$x = $mysqli->query("SELECT value FROM `shortedStocks` WHERE `id` = '{$id}' AND `symbol` = '{$symbol}'");
		if ($x) {
			$x = $x->fetch_assoc();
			if ($amount != $stock_data['shorted_amount']) $p = $mysqli->query("UPDATE `shortedStocks` SET `val` = ((`value` * `amount`) - ".($amount * $stock_data['value'])." ) / (`amount` - ".$amount." ), `amount` = `amount` - ".$amount." WHERE `id` = '{$id}' AND `symbol` = '{$symbol}'");
			else $p = $mysqli->query("DELETE FROM `shortedStocks` WHERE `id` = '{$id}' AND symbol = '{$symbol}'"); 
			if ($p) {
				$p = $mysqli->query("UPDATE `player` SET `liquidCash` = `liquidCash` + ".round(($x['val'] - $stock_data['value'] * (1 + $brokerage)) * $amount).", `shortValue` = Case When (`shortValue` - ".ceil($amount * $stock_data['value']).") < 0 THEN 0 ELSE (`shortValue` - ".ceil($amount * $stock_data['value']).") END WHERE `id` = '{$id}'");
				if ($p) {
					$p = $mysqli->query("INSERT INTO `history` ( `id`, `type`, `symbol`, `amount`, `value`, `skey` ) VALUES ( '{$id}', 'C', '{$symbol}', '{$amount}', '{$stock_data['value']}', '{$skey}' )"); 
					if ($p) {
						if ($skey != -1) $p = $mysqli->query("UPDATE `schedule` SET `pendingAmount` = `pendingAmount` - {$amount} WHERE `skey` = '{$skey}'"); 
						if (!$p) $mysqli->rollback();
						else {
							$mysqli->commit();
							$err_flag = FALSE;
						}
					} else $mysqli->rollback();
				} else $mysqli->rollback(); 
			} else $mysqli->rollback();
		}
		$mysqli->autocommit(TRUE);
		return !$err_flag;
	}
?>
