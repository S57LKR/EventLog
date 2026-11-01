<?php
header("Content-Type: text/plain");
require_once("db.php");

// ===== API AUTH =====



// ===== CHECK POST =====
if (!isset($_POST['adif_file'], $_POST['API'], $_POST['API_KEY'], $_POST['EVENT'])) {
    http_response_code(400);
    exit("ERROR");
}
$query = "SELECT * FROM `users` WHERE `API` = ? AND `API_KEY` = ?;";
$stmt = $con->prepare($query);
$stmt->bind_param("ss", $_POST['API'], $_POST['API_KEY']);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    http_response_code(403);
    exit("ERROR");
}

$result = $result->fetch_assoc();
$stmt->close();

if(isset($_POST['LOG'])){
    $log_name = $_POST['LOG'];
}
else{
    $log_name = $result['LOG_NAME'];
}


$adif = $_POST['adif_file'];

// ===== ADIF PARSER =====
function adif_get($tag, $adif) {
    if (preg_match("/<$tag:\d+>([^<]+)/i", $adif, $m)) {
        return trim($m[1]);
    }
    return "";
}

$event = $_POST['EVENT'];
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

$query = "SELECT `EVENT_CONTROLL` FROM `dogodki` WHERE `DOGODEK_ID` = '$event'";
$res = mysqli_query($con, $query);
$controll_json = mysqli_fetch_assoc($res)['EVENT_CONTROLL'];
$controll = json_decode($controll_json, true);

if(!in_array($result['USERNAME'], $controll)){
    http_response_code(403);
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
    "INSERT INTO $log_name
     (callsign, date, time, band, mode, rst_s, rst_r, DOGODEK, OPOMBE)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

$stmt->bind_param(
    "sssssssss",
    $callsign,
    $date,
    $time,
    $band,
    $mode,
    $rst_s,
    $rst_r,
    $event,
    $result['USERNAME']
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

