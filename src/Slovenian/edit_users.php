<?php
    //error_reporting(0);
    ini_set('display_errors', 0);
    require_once("db.php");
    session_start();

    //Change Tables_in_s59ekl_priz to Tables_in_ + db name!!!
    $query = "SHOW TABLES WHERE Tables_in_s59ekl_priz NOT IN ('users', 'login_log', 'settings', 'dogodki');";
    $res_t_2 = mysqli_query($con, $query);

    $log_names = [];
    while($row = mysqli_fetch_assoc($res_t_2)){
        $log_names[] = $row['Tables_in_s59ekl_priz']; //Change Tables_in_s59ekl_priz to Tables_in_ + db name!!!
    }
    
    $rights_username = $_SESSION['username'];
    $query = "SELECT `RIGHTS` FROM `users` WHERE `USERNAME` = '$rights_username'";
    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);


    $rights = [
        "usl" => $row['RIGHTS'][0] ?? "0",
        "utl" => $row['RIGHTS'][1] ?? "0",
        "pbu" => $row['RIGHTS'][2] ?? "0",
        "vtl" => $row['RIGHTS'][3] ?? "0",
        "udo" => $row['RIGHTS'][4] ?? "0",
        "usk" => $row['RIGHTS'][5] ?? "0"
    ];

    function log_login($c, $u, $s,){
    $date = date("d/M/Y");
    $time = date("H:i:s");
    $uip = $_SERVER['REMOTE_ADDR'];
    $query = "INSERT INTO `login_log` (`date`, `time`, `username`, `status`, `ip`) VALUES ('$date', '$time', '$u', '$s', '$uip');";
    mysqli_query($c, $query);
}

    
    if(!isset($_SESSION['username']) || $rights['pbu'] != "1"){
        echo "You don't have permition to accsess that page.";
        ?>
            <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }

    function NameRightsState($r){
        return ($r === "1") ? "DA" : "NE";
    }
    function ChackRightsChackboy($r){
        return ($r === "1") ? "checked" : "";
    }
    function ConvetreGetToState($r){
        return ($r === "on") ? "1" : "0";
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UREJNAJE UPORABNIKOV | S59EKL</title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>UREJANJE UPORABNIKOV</h1>
        </header>
    </a>

    <br>
    <br>

    <?php 
        if(isset($_GET['mode']) || isset($_POST['mode'])){
            if(isset($_GET['uid'])){
                if($_GET['mode'] == "ban"){
                    $ban_user_id = $_GET['uid'];
                    $query = "SELECT `LOG_NAME`, `USERNAME` FROM `users` WHERE `USER_ID` = '$ban_user_id';";
                    $res = mysqli_query($con, $query);
                    $row = mysqli_fetch_assoc($res);
                    $log_name_to_delite = $row['LOG_NAME'];

                    $query = "DROP TABLE IF EXISTS `$log_name_to_delite`;";
                    if(!mysqli_query($con, $query)){
                        ?>
                            <div>
                                <h2 style="color: red;">Napaka pri brisanju loga uporabnika! Poizkusite ponovno ali kontaktirajte administratorja.</h2>
                            </div>
                        <?php
                    }
                    else{
                        $query = "DELETE FROM `users` WHERE `USER_ID` = '$ban_user_id'";
                        if(!mysqli_query($con, $query)){
                            ?>
                                <div>
                                    <h2 style="color: red;">Napaka pri brisanju uporabnika! Poizkusite ponovno ali kontaktirajte administratorja.</h2>
                                </div>
                            <?php
                        }
                        else{
                            log_login($con, $row['USERNAME'], "Uporabnik izbrisan");
                            ?>
                                <div>
                                    <h2>Brisanje uporabnika uspešno!</h2>
                                    <a href="edit_users.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                </div>
                            <?php 
                        }
                    }

                }
                if(($_GET['mode'] == "accept")){
                    $user_id_to_accept = $_GET['uid'];
                    $query = "UPDATE `users` SET `RIGHTS` = '100000' WHERE `USER_ID` = '$user_id_to_accept';";
                    if(mysqli_query($con, $query)){
                        $query = "SELECT `MAIL`, `USERNAME` FROM `users` WHERE `USER_ID` = '$user_id_to_accept';";
                        $res = mysqli_query($con, $query);
                        $row = mysqli_fetch_assoc($res);
                        $email_adr_accept = $row['MAIL'];
                        $to = $email_adr_accept;
                        $subject = "Vaš račun na S59EKL je aktiven";
                        $message = "Pozdravljeni!\n\nVaš uporabniški račun je aktiven in pripravljen za uporabo.\n\nZa pomoč in vprašanje me lahko kontaktirate na lovro.k.ribic@gmail.com.\n\n73 de ekipa S59EKL";
                        $headers = "From: system@lovro7.eu\r\n";
                        $headers .= "Reply-To: lovro.k.ribic@gmail.com\r\n";
                        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                        log_login($con, $row['USERNAME'], "Uporabnik sprejet");

                    //TODO: poslje email adminu

                    if(mail($to, $subject, $message, $headers));
                        ?>
                            <div>
                                <h2>Uporabnik Povabljen!</h2>
                                <a href="edit_users.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php 
                    }
                    else{
                        ?>
                            <div>
                                <h2 style="color: red;">Napaka pri brisanju uporabnika! Poizkusite ponovno ali kontaktirajte administratorja.</h2>
                            </div>
                        <?php
                    }
                }
                if($_GET['mode'] == "edit"){
                    $user_id_to_edit = $_GET['uid'];
                    $query = "SELECT `USER_ID`, `USERNAME`, `MAIL`, `LOG_NAME`, `RIGHTS` FROM `users` WHERE `USER_ID` = '$user_id_to_edit';";
                    $res = mysqli_query($con, $query);
                    if($row = mysqli_fetch_assoc($res)){
                        ?>
                            <form action="edit_users.php" method="get">
                                <table>
                                    <tr><th style="text-align: center;" colspan="11">UREDI UPORABNIKA</th></tr>
                                    <tr>
                                        <th>UPORABNISKO IME</th>
                                        <th>EMAIL</th>
                                        <th>LOG NAME</th>
                                        <th>UREJANJE<br>SVOJEGA<br>LOGA</th>
                                        <th>UREJANJE<br>TUJEGA<br>LOGA</th>
                                        <th>UREJANJE<br>UPORABNIKOV</th>
                                        <th>VPOGLED<br>V TUJE<br>LOGE</th>
                                        <th>UREJANJE<br>DOGODKOV</th>
                                        <th>VPIS SKEDA</th>
                                    </tr>
                                    <tr>
                                            <td><input type="text" name="edit_username" value="<?php echo $row['USERNAME']; ?>"></td>
                                            <td><input type="text" name="edit_mail" value="<?php echo $row['MAIL']; ?>"></td>
                                            <td>
                                                <select name="edit_log_name">
                                                    <option>>-Izberi LOG-<</option>
                                                    <?php 
                                                        for($i = 0; $i < count($log_names); $i++){
                                                            $log_n = $log_names[$i];
                                                            $selected_stm = ($log_n == $row['LOG_NAME']) ? "selected" : "";
                                                            ?>
                                                                <option <?php echo $selected_stm; ?> value="<?php echo $log_n; ?>"><?php echo $log_n; ?></option>
                                                            <?php
                                                        }
                                                    ?>
                                                </select>
                                            </td>
                                            <td><input type="checkbox" name="edit_usl" <?php echo ChackRightsChackboy($row['RIGHTS'][0] ?? "0"); ?>></td>
                                            <td><input type="checkbox" name="edit_utl" <?php echo ChackRightsChackboy($row['RIGHTS'][1] ?? "0"); ?>></td>
                                            <td><input type="checkbox" name="edit_pbu" <?php echo ChackRightsChackboy($row['RIGHTS'][2] ?? "0"); ?>></td>
                                            <td><input type="checkbox" name="edit_vtl" <?php echo ChackRightsChackboy($row['RIGHTS'][3] ?? "0"); ?>></td>
                                            <td><input type="checkbox" name="edit_udo" <?php echo ChackRightsChackboy($row['RIGHTS'][4] ?? "0"); ?>></td>
                                            <td><input type="checkbox" name="edit_usk" <?php echo ChackRightsChackboy($row['RIGHTS'][5] ?? "0"); ?>></td>
                                            <input type="hidden" name="mode" value="save">
                                            <input type="hidden" name="uid" value="<?php echo $user_id_to_edit ?>">
                                    </tr>
                                    <tr><th style="text-align: center;" colspan="11"><input type="submit" value="SHRANI"></th></tr>
                                </table>
                            </form>
                            <div>
                                <form action="edit_users.php" method="post">
                                    <input type="hidden" name="mode" value="cdb">
                                    <input type="hidden" name="uid" value="<?php echo $user_id_to_edit ?>">
                                    <input type="text" name="dbn" placeholder="IME LOGA">
                                    <input type="submit" value="USTVARI LOG">
                                </form>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <h2 style="color: red;">Ni mogoče poiskati podatkov uporabnika!</h2>
                            </div>
                        <?php
                    }
                }
                if($_GET['mode'] == "save"){
                    $user_id_to_save = $_GET['uid']; 
                    $username_to_save = $_GET['edit_username'];
                    $email_to_save = $_GET['edit_mail'];
                    $log_name_to_save = $_GET['edit_log_name'];
                    $usl_to_save = ConvetreGetToState($_GET['edit_usl'] ?? "0");
                    $utl_to_save = ConvetreGetToState($_GET['edit_utl'] ?? "0");
                    $pbu_to_save = ConvetreGetToState($_GET['edit_pbu'] ?? "0");
                    $vtl_to_save = ConvetreGetToState($_GET['edit_vtl'] ?? "0");
                    $udo_to_save = ConvetreGetToState($_GET['edit_udo'] ?? "0");
                    $usk_to_save = ConvetreGetToState($_GET['edit_usk'] ?? "0");
                    $rights_to_save = $usl_to_save . $utl_to_save . $pbu_to_save . $vtl_to_save . $udo_to_save . $usk_to_save;

                    $query = "UPDATE `users` SET `USERNAME` = '$username_to_save', `MAIL` = '$email_to_save',`LOG_NAME` = '$log_name_to_save',`RIGHTS` = '$rights_to_save' WHERE `USER_ID` = '$user_id_to_save';";
                    if(mysqli_query($con, $query)){
                        ?>
                            <div>
                                <h2>Uporabnik posodobljen!</h2>
                                <a href="edit_users.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <h2 style="color: red;">Napaka pri shranjevanju podatkov! Poizkusite ponovno ali kontaktirajte administratorja.</h2>
                            </div>
                        <?php
                    }
                }
            }
            else{
                ?>
                    <div>
                        <h2 style="color: red;">Niste izbrali uporabnika!</h2>
                    </div>
                <?php 
            }

        }
        if(isset($_POST['mode'])){
            if(isset($_POST['uid'])){
                if($_POST['mode'] === "cdb"){
                    if(isset($_POST['dbn'])){
                        $dbn = $_POST['dbn'];
                        if(preg_match('/^[a-zA-Z0-9_šŠđĐčČćĆžŽ]+$/u', $dbn)){
                            if(!in_array($dbn, $log_names, true)){
                                $query = "CREATE TABLE `$dbn` (
                                    `qso_num` int(11) NOT NULL AUTO_INCREMENT,
                                    `callsign` tinytext NOT NULL,
                                    `date` tinytext NOT NULL,
                                    `time` tinytext NOT NULL,
                                    `band` tinytext NOT NULL,
                                    `mode` varchar(8) NOT NULL,
                                    `rst_s` varchar(3) DEFAULT NULL,
                                    `rst_r` varchar(3) DEFAULT NULL,
                                    `DOGODEK` tinytext DEFAULT NULL,
                                    `OPOMBE` tinytext DEFAULT NULL,
                                    `name` tinytext DEFAULT NULL,
                                    PRIMARY KEY (`qso_num`)
                                )";

                                if (!mysqli_query($con, $query)) {
                                    die("Napaka pri ustvarjanju loga - Kontaktiraj administratorja: " . mysqli_error($con));
                                }

                                ?>
                                    <div>
                                        <h3>Log uspešno ustvarjen!</h3>
                                        <a href="edit_users.php?mode=edit&uid=<?php echo $_POST['uid']; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                    </div>
                                <?php 
                            }
                            else{
                                ?>
                                    <div>
                                        <h2 style="color: red;">Izbrano ime loga že obstaja!</h2>
                                        <a href="edit_users.php?mode=edit&uid=<?php echo $_POST['uid']; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                    </div>
                                <?php 
                            }
                        }
                        else{
                            ?>
                                <div>
                                    <h2 style="color: red;">Neveljavni znaki v imenu loga!</h2>
                                    <a href="edit_users.php?mode=edit&uid=<?php echo $_POST['uid']; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                </div>
                            <?php 
                        }
                    }
                    else{
                        ?>
                            <div>
                                <h2 style="color: red;">Manjka ime loga!</h2>
                                <a href="edit_users.php?mode=edit&uid=<?php echo $_POST['uid']; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php 
                    }
                }
            }
            else{
                ?>
                    <div>
                        <h2 style="color: red;">Niste izbrali uporabnika!</h2>
                    </div>
                <?php 
            }
        }
    ?>

    <table>
            <tr><th style="text-align: center;" colspan="11">VSI UPORABNIKI</th></tr>
            <tr>
                <th>UPORABNISKO IME</th>
                <th>EMAIL</th>
                <th>LOG NAME</th>
                <th>UREJANJE<br>SVOJEGA<br>LOGA</th>
                <th>UREJANJE<br>TUJEGA<br>LOGA</th>
                <th>UREJANJE<br>UPORABNIKOV</th>
                <th>VPOGLED<br>V TUJE<br>LOGE</th>
                <th>UREJANJE<br>DOGODKOV</th>
                <th>VPIS SKEDA</th>
                <th>UREDI</th>
                <th>STATUS</th>

            </tr>
            <?php
                $query = "SELECT `USER_ID`, `USERNAME`, `MAIL`, `LOG_NAME`, `RIGHTS` FROM `users`;";
                $res = mysqli_query($con, $query);
                while($row = mysqli_fetch_assoc($res)){
                    $email_link = "mailto:" . $row['MAIL'];
                    $edit_link = "edit_users.php?mode=edit&uid=" . $row['USER_ID'];
                    if($row['RIGHTS'] == "0"){
                        $status_link = "edit_users.php?mode=accept&uid=" . $row['USER_ID'];
                        $status_title = "POVABI";
                    }
                    else{
                        $status_link = "edit_users.php?mode=ban&uid=" . $row['USER_ID'];
                        $status_title = "IZBRIŠI";
                    }
                    
            ?>
                <tr>
                    <td><a href="user.php?uid=<?php echo $row['USER_ID']; ?>"><?php echo $row['USERNAME']; ?></a></td>
                    <td><a href="<?php echo $email_link; ?>" target="_blank"><?php echo $row['MAIL']; ?></a></td>
                    <td><?php echo $row['LOG_NAME']; ?></td>
                    <td><?php echo NameRightsState($row['RIGHTS'][0] ?? "0") ?></td>
                    <td><?php echo NameRightsState($row['RIGHTS'][1] ?? "0") ?></td>
                    <td><?php echo NameRightsState($row['RIGHTS'][2] ?? "0") ?></td>
                    <td><?php echo NameRightsState($row['RIGHTS'][3] ?? "0") ?></td>
                    <td><?php echo NameRightsState($row['RIGHTS'][4] ?? "0") ?></td>
                    <td><?php echo NameRightsState($row['RIGHTS'][5] ?? "0") ?></td>
                    <td><a href="<?php echo $edit_link; ?>" target="_self">UREDI</a></td>
                    <td><a href="<?php echo $status_link; ?>" target="_self" class="status_url"><?php echo $status_title; ?></a></td>
                </tr>
            <?php
                }
            ?>
        </table>
    <br>
    <br>    
        <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA GLAVNO STRAN</div></a>
    </div>
    <br>
    <br>    <br>
    <br>    <br>
    <br>    <br>
    <br>    <br>
    <br>    <br>
    <br>    <br>
    <br>    <br>
    <br>    <br>
    <br>
        <br>
            <br>
                <br>

    <footer>
        <p>© 2026 <a href="https://s59ekl.si" target="_blank">S59EKL</a></p>
    </footer>

    <script>
        document.querySelectorAll(".status_url").forEach(link => {
            link.addEventListener("click", function(event) {
                if(!confirm("Ali ste prepričani, da želite spremeniti status izbranega uporabnika?")){
                    event.preventDefault();
                }

            });
        });

    </script>


</body>
</html>