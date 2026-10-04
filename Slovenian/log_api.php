<?php
header("Content-Type: text/plain");
require_once("db.php");

// ===== API AUTH =====
$API_USER = "log4om";
$API_PASS = "tst1234";


// ===== CHECK POST =====
if (!isset($_POST['user'], $_POST['pass'], $_POST['adif_file'])) {
    http_response_code(400);
    exit("ERROR");
}

if ($_POST['user'] !== $API_USER || $_POST['pass'] !== $API_PASS) {
    http_response_code(403);
    exit("ERROR");
}

$adif = $_POST['adif_file'];

// ===== ADIF PARSER =====
function adif_get($tag, $adif) {
    if (preg_match("/<$tag:\d+>([^<]+)/i", $adif, $m)) {
        return trim($m[1]);
    }
    return "";
}

$callsign = adif_get("CALL", $adif);
$date_raw = adif_get("QSO_DATE", $adif);
$time_raw = adif_get("TIME_ON", $adif);
$band = strtoupper(adif_get("BAND", $adif));
$mode = adif_get("MODE", $adif);
$rst_s = adif_get("RST_SENT", $adif);
$rst_r = adif_get("RST_RCVD", $adif);

if ($callsign == "") {
    http_response_code(400);
    exit("ERROR");
}

// ===== DB INSERT =====

$date = "";
$time = "";
if (preg_match("/^(\d{4})(\d{2})(\d{2})$/", $date_raw, $m)) {
    $date = $m[3] . "/" . $m[2] . "/" . $m[1]; // DD/MM/YYYY
}
if (preg_match("/^(\d{2})(\d{2})/", $time_raw, $m)) {
    $time = $m[1] . ":" . $m[2]; // HH:MM
}

$stmt = $con->prepare(
    "INSERT INTO s57lkr_qso
     (callsign, date, time, band, mode, rst_s, rst_r, qsl_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, 0)"
);

$stmt->bind_param(
    "sssssss",
    $callsign,
    $date,
    $time,
    $band,
    $mode,
    $rst_s,
    $rst_r
);

if ($stmt->execute()) {
    echo "OK";
} else {
    http_response_code(500);
    echo "ERROR";
}

$stmt->close();
$con->close();
?>

