<?php
    session_start();
    require_once("db.php");

    $username = $_SESSION['username'];

    function log_login($c, $u, $s,){
    $date = date("d/M/Y");
    $time = date("H:i:s");
    $uip = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO `login_log` (`date`, `time`, `username`, `status`, `ip`) VALUES ('$date', '$time', '$u', '$s', '$uip');";
    mysqli_query($c, $query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IZPIS | LOG S59EKL</title>
    <link rel="stylesheet" href="style/style.css?v=6">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>STATUS ODJAVE</h1>
        </header>
    </a>
    <?php 
        if(session_destroy()){
            log_login($con, $username, "Odjava uspesna!");
            ?>
                <div style="text-align: center;">
                    <h1>ODJAVA USPEŠNA!</h1>
                </div>
            <?php
        }
        else{
            log_login($con, $username, "Odjava uspesna!");
            ?>
                <div style="text-align: center;">
                    <h1>ODJAVA NEUSPEŠNA!</h1>
                </div>
            <?php
        }
    ?>
    <br>
    <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA DOMAČO STRAN</div></a>
    </div>
    
    <footer>
        <p>© 2026 <a href="https://s59ekl.si" target="_blank">S59EKL</a></p>
    </footer>

    
</body>
</html>