<?php
session_start();
require "db.php"; // vrne $con

    $rights_username = $_SESSION['username'];
    $query = "SELECT `RIGHTS` FROM `users` WHERE `USERNAME` = '$rights_username'";
    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);

    $rights = [
        "usl" => $row['RIGHTS'][0] ?? "0",
        "utl" => $row['RIGHTS'][1] ?? "0",
        "pbu" => $row['RIGHTS'][2] ?? "0",
        "vtl" => $row['RIGHTS'][3] ?? "0",
        "usk" => $row['RIGHTS'][5] ?? "0"
    ];

    if(!isset($_SESSION['username']) || $rights['usl'] != "1"){
        echo "You don't have permition to accsess that page.";
        ?>
            <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }



// Preberi POST vrednosti
$callsign = strtoupper(trim($_POST['callsign'] ?? ''));
$date     = trim($_POST['date'] ?? '');
$time     = trim($_POST['time'] ?? '');
$band     = trim($_POST['band'] ?? '');
$mode     = strtoupper(trim($_POST['mode'] ?? ''));
$rst_s    = intval($_POST['rst_s'] ?? 0);
$rst_r    = intval($_POST['rst_r'] ?? 0);
$event = $_POST['event'];
$name     = strtoupper($_POST['name'] ?? '');

$query = "SELECT `DOGODEK_ID` FROM `dogodki` WHERE `IME_DOGODKA` LIKE '%SKED%';";
$res = mysqli_query($con, $query);

while($row = mysqli_fetch_assoc($res)){
    if($event == $row['DOGODEK_ID'] && $rights['usk'] != "1"){
        header("Location: add_qso.php?msg=usk");
        exit();
    }
}

// Preveri obvezna polja
if ($callsign === "" || $date === "" || $time === "" || $band === "" || $mode === "" || $rst_s == 0 || $rst_r == 0) {
    die("Missing values.");
}


// SQL priprava
$query = "SELECT `IME_DOGODKA` FROM `dogodki` WHERE `DOGODEK_ID` = '$event';";
$res = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($res);
$event_name = $row['IME_DOGODKA'];

if(isset($_POST['log_name'])){
    $log_name = $_POST['log_name'];
    $opombe = $rights_username;
}
else{
    $log_name = $_SESSION['log_name'];
    $opombe = "";
}


$sql = "INSERT INTO `$log_name` (callsign, date, time, band, mode, rst_s, rst_r, DOGODEK, name, OPOMBE)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("sssssiisss", $callsign, $date, $time, $band, $mode, $rst_s, $rst_r, $event, $name, $opombe);





if ($stmt->execute()) {
    header("Location: add_qso.php?ok=1&pm=" . $mode . "&pb=" . $band . "&pe=" . $event);
} else {
    echo "MySQL Error: " . $stmt->error;
}

$stmt->close();
$con->close();
?>
