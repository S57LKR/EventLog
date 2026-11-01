<?php

require_once("db.php");

$search_call_sked = $sc;
$search_event_id = $se;

$query = "SELECT * FROM `s59ekl_log`
          WHERE `callsign` = '$search_call_sked'
          AND `DOGODEK` = '$search_event_id'
          ORDER BY `date`, `time`";

$sked_res = mysqli_query($con, $query);

$tocke_list = 1;
$event_search_res = [];

// Števca morata biti zunaj while zanke,
// da se njuna vrednost ohrani.
$_2m_qso = 0;
$_70cm_qso = 0;

while ($sked_row = mysqli_fetch_assoc($sked_res)) {

    $point_qso_20 = 0;

    // 2M - samo prvi QSO dobi točko
    if ($sked_row['band'] == "2M" && $_2m_qso < 1) {
        $point_qso_20 = 1;
        $_2m_qso++;
    }

    // 70CM - samo prvi QSO dobi točko
    if ($sked_row['band'] == "70CM" && $_70cm_qso < 1) {
        $point_qso_20 = 1;
        $_70cm_qso++;
    }

    $event_search_res[] = [
        'callsign' => "S59EKL",
        'date'     => $sked_row['date'],
        'time'     => $sked_row['time'],
        'band'     => $sked_row['band'],
        'mode'     => $sked_row['mode'],
        'rst_r'    => $sked_row['rst_r'],
        'rst_s'    => $sked_row['rst_s'],
        'tocke'    => $point_qso_20
    ];
}

$min_poits = 2;

?>
