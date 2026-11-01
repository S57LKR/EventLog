<?php
	require_once("db.php");
	$search_call_test = $sc;
	$search_event_test = $se;

	$query = "SELECT * FROM `s59ekl_log` WHERE `callsign` = '$search_call_test' AND `DOGODEK` = '$search_event_test';";
	$res_20 = mysqli_query($con, $query);
	while($sked_row = mysqli_fetch_assoc($res_20)){
		$event_search_res[] = [
			'callsign' => "S59EKL",
			'date'     => $sked_row['date'],
			'time'     => $sked_row['time'],
			'band'     => $sked_row['band'],
			'mode'     => $sked_row['mode'],
			'rst_r'    => $sked_row['rst_r'],
			'rst_s'    => $sked_row['rst_s'],
			'tocke'    => 1
		];
	}
?>