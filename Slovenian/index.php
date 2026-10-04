<?php
    error_reporting(0);
    ini_set('display_errors', 0);

    require_once("db.php");

    //Pridobi dogodke
    $query = "SELECT * FROM `dogodki`;";
    $res = mysqli_query($con, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LOG | S59EKL</title>
    <meta name="description" content="Hve you made QSO with S57LKR? If yes GET YOUR QSL CARD HERE. 73 de S57LKR">
    <link rel="stylesheet" href="style/style.css?v=5">
</head>
<body>
    <header>
        <h1>S59EKL LOG</h1>
    </header>

    <br>

    <div style="text-align: center;">
        <ht>VPIŠI SVOJ KLICNI ZNAK</ht>
        <form action="statisic.php" method="get">
            <input type="text" name="guest_callsign" placeholder="KLICNI ZNAK">
            <select name="event" id="izbira" required style="width: 100%; height: 40px;">
                <option value="" selected disabled>-- Izberi dogodek --</option>
                <?php 
                    while($row = mysqli_fetch_assoc($res)){
                        ?>
                            <option value="<?php echo $row['DOGODEK_ID']; ?>"><?php echo $row['IME_DOGODKA']; ?></option>
                        <?php
                    }
                ?>
            </select>
            <input type="submit", value="PREVERI ZVEZE">
        </form>
    </div>

    
    <br>
    <br>
    <br>
    <hr>
    <br>
    <br>
    <br>

    <?php
        session_start();
        if(!isset($_SESSION['username'])){
    ?>
        <div id="login-toggle" class="admin-button" style="text-align: center;">
            VPIS / LOG IN
        </div>
        <div id="login-form" class="card hidden" style="align-items: center">
            <form action="login.php" method="post">
                    <input type="text" name="username" placeholder="UPORABNIŠKO IME">
                    <input type="password" name="pass" id="password" placeholder="GESLO">
                    <br>
                    <br>
                    <button type="button" onclick="togglePassword()" style="border: none; text-decoration: underline; color: #0000EE;">Pokaži geslo</button>
                    <br>
                    <br>
                    <input type="submit" value="VPIS!">
                    <script>
function togglePassword() {
    const password = document.getElementById("password");

    if (password.type === "password") {
        password.type = "text";
    } else {
        password.type = "password";
    }
}
</script>
            </form>
        </div>
        <a href="signin.php" target="_self" style="text-decoration: none; color: #222">
            <div id="signin-toggle" class="admin-button" style="text-align: center;">
            REGISTRACIJA / SIGN IN
        </div>
        </a>
    <?php
        }
    ?>
    <div>
    <?php
            
            $rights_username = $_SESSION['username'];
            $query = "SELECT `RIGHTS` FROM `users` WHERE `USERNAME` = '$rights_username'";
            $res = mysqli_query($con, $query);
            $row = mysqli_fetch_assoc($res);
        
            $rights = [
                "usl" => $row['RIGHTS'][0] ?? "0",
                "utl" => $row['RIGHTS'][1] ?? "0",
                "pbu" => $row['RIGHTS'][2] ?? "0",
                "vtl" => $row['RIGHTS'][3] ?? "0",
                "udo" => $row['RIGHTS'][4] ?? "0"
            ];

        if($rights['usl'] === "1"){
            ?>
                
                    <a href="<?php echo "logbook.php?call=" . $_SESSION['username']; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">MOJ LOG</div></a>
                    <a href="add_qso_adi.php" target="_self" style="text-decoration: none;"><div class="admin_url">NALOŽI .adi / .adif</div></a>
                    <a href="add_qso.php" target="_self" style="text-decoration: none;"><div class="admin_url">VPIŠI QSO</div></a>
            <?php
        }
        if($rights['pbu'] === "1"){
            ?>
                    <a href="edit_users.php" target="_self" style="text-decoration: none;"><div class="admin_url">UREDI UPORABNIKE</div></a>
            <?php
        }
        if($rights['udo'] === "1"){
            ?>
                    <a href="add_event.php" target="_self" style="text-decoration: none;"><div class="admin_url">UREDI DOGODKE</div></a>
            <?php
        }
        if($rights['utl'] === "1" || $rights['vtl'] === "1"){ //TODO: DODaj polje za vpis tujega znaka
            ?>
                    <div id="log-toggle" class="admin-button" style="text-align: center;">
                        TUJI LOG
                    </div>
                    <div id="log-form" class="card hidden">
                        <form action="logbook.php" method="get">
                                <input type="text" name="call" placeholder="KLICNI ZNAK">
                                <input type="submit" value="PREVERI!">
                        </form>
                    </div>
                    <script>
                        document.getElementById("log-toggle").onclick = () => {
                            document.getElementById("log-form").classList.toggle("hidden");
                        };
                    </script>
            <?php
        }

        if(isset($_SESSION['username'])){
            ?>
                    <a href="logout.php" target="_self" style="text-decoration: none;"><div class="admin_url">IZPIS</div></a>
            <?php
        }

    ?>
    </div>

    <br>
    <br>
    <br>
    <hr>
    <br>
    <br>
    
    <div>
        <h3>Dokumenti</h3>
        <a href="PRAVNO_OBVESTILO.pdf" target="_blank">PRAVNO OBVESTILO</a>
        <br>
        <a href="POGOJI_UPORABE.pdf" target="_blank">POGOJI UPORABE</a>
        <br>
        <a href="POLITIKA_ZASEBNOSTI.pdf" target="_blank">POLITIKA ZASEBNOSTI</a>
        <br>
        <a href="OBVESTILO_O_PISKOTKIH_IN_SEJAH.pdf" target="_blank">OBVESTILO O PIŠKOTKIH IN SEJAH</a>
    </div>
    <br>

    <footer>
        <p>© 2026 <a href="https://s59ekl.si" target="_blank">S59EKL</a></p>
    </footer>

    <script>
    document.getElementById("login-toggle").onclick = () => {
        document.getElementById("login-form").classList.toggle("hidden");
    };

</script>


</body>
</html>