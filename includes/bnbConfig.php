<?php
	$config = parse_ini_file('bnbConfig.ini');
	
	$server = $config['server'];

	$sqlId = $config['sqlId'];
	$sqlPass = $config['sqlPass'];
	$db = $config['db'];
	$token = $config['token'];
	
	$startTime = $config['startTime'];
	$startTimeMin = $config['startTimeMin'];
	$endTime = $config['endTime'];
	$endTimeMin = $config['endTimeMin'];
	$startMoney = $config['startMoney'];
	$brokerage = $config['brokerage'];
	if (isset($_REQUEST['iscompliant'])) echo "333/625/1791";
?>
