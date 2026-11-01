<?php
    require_once("db.php");
    $search_call_sked = $sc;
    $search_event_id = $se;

    $query = "SELECT * FROM `s59ekl_log` WHERE `callsign` = '$search_call_sked' AND `DOGODEK` = '$search_event_id';";
    $sked_res = mysqli_query($con, $query);
    $tocke_list = 1;
    $event_search_res = [];

    while($sked_row = mysqli_fetch_assoc($sked_res)){
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