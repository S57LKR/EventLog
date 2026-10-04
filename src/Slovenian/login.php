<?php
require_once("db.php");
session_start();



function log_login($c, $u, $s,){
    $date = date("d/M/Y");
    $time = date("H:i:s");
    $uip = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO `login_log` (`date`, `time`, `username`, `status`, `ip`) VALUES ('$date', '$time', '$u', '$s', '$uip');";
    mysqli_query($c, $query);
}

//No data in post
if (!isset($_POST['username']) || !isset($_POST['pass'])) {
    header("Location: index.php");
    exit;
}

//Get login data
$username = $_POST['username'];
$password = $_POST['pass'];

//Rest session
if(!isset($_SESSION['attempts'])){
    $_SESSION['attempts'] = 0;
}

//inc attempts
else{
    $_SESSION['attempts']++;
    if($_SESSION['attempts'] > 10){
        echo "Preveč napačnih poiuzkusov prijave. Prosim poizkusite kasneje!";
        log_login($con, $username, "Too many attempts");
        exit();
    }
}


// PRIPRAVLJENA POIZVEDBA (ščiti pred SQL injection)
$stmt = mysqli_prepare($con,
    "SELECT PASS FROM users WHERE USERNAME = ?");
mysqli_stmt_bind_param($stmt, "s", $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$status = "";

if ($row = mysqli_fetch_assoc($result)) {

    // Chack password
    if (password_verify($password, $row['PASS'])) {

        //get rights
        $query = "SELECT `RIGHTS` FROM `users` WHERE `USERNAME` = '$username'";
        $res = mysqli_query($con, $query);
        $row = mysqli_fetch_assoc($res);
        if($row['RIGHTS'] == "0"){
            $status = "VAŠ UPORABNIŠKI RAČUN TRENUTNO NI AKTIVEN.";
        }
        else{
            $rights = [
                "usl" => $row['RIGHTS'][0] ?? "0",
                "utl" => $row['RIGHTS'][1] ?? "0",
                "pbu" => $row['RIGHTS'][2] ?? "0",
                "vtl" => $row['RIGHTS'][3] ?? "0"
            ];
            $query = "SELECT `LOG_NAME` FROM `users` WHERE `USERNAME` = '$username'";
            $res = mysqli_query($con, $query);
            $row = mysqli_fetch_assoc($res);
            $_SESSION['username'] = $username;
            $_SESSION['log_name'] = $row['LOG_NAME'];
            $_SESSION['rights'] = $rights;
            $status = "PRIJAVA USPEŠNA!";
        }
        
    } else {
        $status = "NAPAČNO UPORABNIŠKO IME ALI GESLO!";
    }

} else {
    $status = "NAPAČNO UPORABNIŠKO IME ALI GESLO!";
}

log_login($con, $username, $status);



?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>STATUS PRIJAVE | S59EKL</title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>

<a href="index.php" target="_self" title="HOME">
    <header>
        <h1>STATUS PRIJAVE</h1>
    </header>
</a>

<div style="text-align:center;">
    <h1><?php echo htmlspecialchars($status); ?></h1>
</div>

<div>
    <a href="index.php" style="text-decoration:none;" target="_self">
        <div class="admin_url">NAZAJ NA GLAVNO STRAN</div>
    </a>
</div>

<br><br><br><hr><br><br><br>

<footer>
    <p>© 2026 <a href="https://s59ekl.si" target="_blank">S59EKL</a></p>
</footer>

</body>
</html>
