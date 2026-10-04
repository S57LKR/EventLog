<?php
    //error_reporting(0);
    //ini_set('display_errors', 0);
    session_start();
    require_once("db.php");
    require_once("get_users.php");
    
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

    if($rights['udo'] != "1"){
        echo "Nimate dovoljenja za ogled strani";
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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DODAJ DOGODEK</title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>UREDI DOGODKE</h1>
        </header>
    </a>
    <br>

    <!--     UREJANJE,        -->
    <?php 
        if(isset($_GET['mode'])){
            if($_GET['mode'] === "edit"){
                if(empty($_GET['eid'])){
                    ?>
                        <div>
                            <h2 style="color: red;">Niste izbrali dogodka za urejanje!</h2>
                        </div>
                    <?php
                }
                else{
                    $event_id_to_edit = $_GET['eid'];
                    $query = "SELECT * FROM `dogodki` WHERE `DOGODEK_ID` = '$event_id_to_edit';";
                    if($res = mysqli_query($con, $query)){
                        $row = mysqli_fetch_assoc($res);
                        $edit_selected_controllers = json_decode($row['EVENT_CONTROLL'], true);
                        if (!is_array($edit_selected_controllers)) {
                            $selected_controllers = [];
                        }
                        ?>
                            <form action="add_event.php" method="POST" enctype="multipart/form-data">
                                <table>
                                    <tr><th style="text-align: center;" colspan="6">UREDI DOGODEK</th></tr>
                                    <tr>
                                        <th>Ime dogodka</th>
                                        <th>CALC datoteka</th>
                                        <th>Konec dogodka</th>
                                        <th>Zahtevaj log za <br>pridobitev diplome</th>
                                        <th>Glavni log</th>
                                        <th>Voditelji</th>
                                    </tr>
                                    <tr>
                                        <td><input type="text" name="edit_name" value="<?php echo $row['IME_DOGODKA']; ?>"></td>
                                        <td>
                                            <a href="event_calc/<?php echo trim($row['CALC_PHP'], " "); ?>"><?php echo $row['CALC_PHP']; ?></a>
                                            <br>
                                            <br>
                                            <input type="file" id="datoteka" name="calc_file" accept=".php" style="width: 100%; height: 40px;">
                                        </td>
                                        <td><input type="date" id="datum" name="edit_event_date" style="width: 100%; height: 40px;" value="<?php echo $row['KONC'] ?>"></td>
                                        <td><input type="checkbox" name="edit_rl" <?php echo ChackRightsChackboy($row['REQUIRE_LOG']); ?> value="on"></td>

                                        <td>
                                            <select name="save_controllers[]" id="edit_strl" multiple style="width: 100%;">
                                                <?php 
                                                    for ($i = 0; $i < count($usernames); $i++) {
                                                        $username = $usernames[$i];
                                                        $selected = in_array($username, $edit_selected_controllers) ? 'selected' : '';
                                                        ?>
                                                            <option value="<?php echo htmlspecialchars($username); ?>" <?php echo $selected; ?>><?php echo htmlspecialchars($username); ?></option>
                                                        <?php
                                                    }
                                                    ?>
                                            </select>
                                        </td>
                                        <td>
                                            <select name="edit_log" style="width: 100%;"> 
                                                <?php 
                                                    $log_selected = false;
                                                    for($i = 0; $i < count($log_names); $i++){
                                                        ?>
                                                            <option value="<?php echo $log_names[$i]; ?>" <?php if($log_names[$i] == $row['MAIN_LOG']){echo "selected"; $log_selected = true;} ?> ><?php echo $log_names[$i]; ?></option>
                                                        <?php
                                                    }
                                                ?>
                                                <option value="" <?php if(!$log_selected){echo "selected disabled"; $log_selected = true;} ?>>Osebni log</option>
                                            </select> 
                                        </td>

                                        <input type="hidden" name="mode" value="save">
                                        <input type="hidden" name="old_calc_file_name" value="<?php echo trim($row['CALC_PHP'], " "); ?>">
                                        <input type="hidden" name="eid" value="<?php echo $row['DOGODEK_ID'] ?>">
                                    </tr>
                                    <tr><th style="text-align: center;" colspan="6"><input type="submit" value="SHRANI"></th></tr>
                                </table>
                            </form>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <h2 style="color: red;">Dogodka za urejanje ni mogoče pridobiti.</h2>
                            </div>
                        <?php
                    }

                }
            }
            else{
                //ukazi
            }
                        /////////////  BRISANJE DOGODKA
            if($_GET['mode'] === "delete"){
                $event_id_to_delite = $_GET['eid'];
                $query = "SELECT `CALC_PHP` FROM `dogodki` WHERE `DOGODEK_ID` = '$event_id_to_delite';";
                if($res = mysqli_query($con, $query)){
                    $row = mysqli_fetch_assoc($res);
                    $calc_file_to_delete = $row['CALC_PHP'];
                    $event_save_file_path = "event_calc/";
                    $datoteka = trim("event_calc/" . trim($calc_file_to_delete, " "), " %20");
                    if (file_exists($datoteka)) {
                        if (unlink($datoteka)) {
                            $query = "DELETE FROM `dogodki` WHERE `DOGODEK_ID` = '$event_id_to_delite';";
                            if(!mysqli_query($con, $query)){
                                ?>
                                    <div>
                                        <h2 style="color: red;">Napaka pri brisanju dogodka</h2>
                                    </div>
                                <?php
                            }
                            else{
                                ?>
                                    <div>
                                        <h2>Dogodek uspešno izbrisan!</h2>
                                        <a href="add_event.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                    </div>
                                <?php
                            }
                        } 
                        else {
                            ?>
                                <div>
                                    <h2 style="color: red;">Napaka pri brisanju CALC datoteke</h2>
                                </div>
                            <?php
                        }
                    } 
                    else {
                        ?>
                            <div>
                                <h2 style="color: red;">Napaka pri brisanju CALC datoteke - CALC datoteka ne obstaja</h2>
                            </div>
                        <?php
                    }
                }
                else{
                    ?>
                        <div>
                            <h2 style="color: red;">Napaka pri brisanju CALC datoteke</h2>
                        </div>
                    <?php
                }

            }
        }


        if(isset($_POST['mode'])){
            if($_POST['mode'] === "add"){
                $event_save_file_path = "event_calc/";
                $event_name_to_add = $_POST['add_event_name'] ?? "";
                $event_calc_file_name_to_add = $_FILES['calc_file']['name'] ?? "napaka_pri_shranjevanju.txt";
                $event_date_to_add = $_POST['add_event_date'] ?? "";
                $event_calc_file_to_add = $_FILES['calc_file']['tmp_name'];
                $controllers = $_POST['controllers'] ?? [];
                $controllers_json = json_encode($controllers);
                $main_log_name = $_POST['add_log'];
                if(isset($_POST['add_rl'])){
                    $require_log = ($_POST['add_rl'] === "on") ? "1" : "0";
                }
                else{
                    $require_log = "0";
                }
                $add_file_name = substr($event_calc_file_name_to_add, 0, -4) . trim($event_name_to_add, " /%&?!") . ".php";
                $query = "INSERT INTO `dogodki` (`IME_DOGODKA`, `CALC_PHP`, `KONC`, `REQUIRE_LOG`, `MAIN_LOG`, `EVENT_CONTROLL`) VALUES ('$event_name_to_add', ' $add_file_name', '$event_date_to_add', '$require_log', '$main_log_name', '$controllers_json'); ";
                if(!mysqli_query($con, $query)){
                    ?>
                        <div>
                            <h2 style="color: red;">Napaka pri ustvarjanju dogodka!</h2>
                        </div>
                    <?php
                }
                else{
                    if(!move_uploaded_file($event_calc_file_to_add, "event_calc/" . $add_file_name)){
                        ?>
                            <div>
                                <h2 style="color: red;">Napaka pri nalaganju CALC datoteke!</h2>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <h2">Dogodek uspešno dodan!</h2>
                                <a href="add_event.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                }
            }
            if($_POST['mode'] === "save"){
                $save_event_id = $_POST['eid'];
                $event_save_file_path = "event_calc/";
                $event_name_to_add = $_POST['edit_name'] ?? "";
                $save_main_log = $_POST['edit_log'];
                $save_controllers_json = json_encode($_POST['save_controllers']);
                if(isset($_POST['edit_rl'])){
                    $require_log = ($_POST['edit_rl'] === "on") ? "1" : "0";
                }
                else{
                    $require_log = "0";
                }
                if(!empty($_FILES['calc_file']['tmp_name'])){
                    $event_calc_file_name_to_add = $_FILES['calc_file']['name'] ?? "napaka_pri_shranjevanju.txt";
                    $event_calc_file_to_add = $_FILES['calc_file']['tmp_name'];
                    $update_calc_file = "1";
                    $save_event_name_file = substr($event_calc_file_name_to_add, 0, -4) . trim($event_name_to_add, " /%&?!") . ".php";
                }
                else{
                    $save_event_name_file = trim($_POST['old_calc_file_name'], " ");
                }
                $event_date_to_add = $_POST['edit_event_date'] ?? "";
                $query = "UPDATE `dogodki` SET `IME_DOGODKA` = '$event_name_to_add', `CALC_PHP` = '$save_event_name_file', `KONC` = '$event_date_to_add', `REQUIRE_LOG` = '$require_log', `MAIN_LOG` = '$save_main_log', `EVENT_CONTROLL` = '$save_controllers_json' WHERE `DOGODEK_ID` = '$save_event_id'; ";
                if(!mysqli_query($con, $query)){
                    ?>
                        <div>
                            <h2 style="color: red;">Napaka pri shranjevanju podatkov!</h2>
                        </div>
                    <?php
                }
                else{
                    if(isset($update_calc_file)){
                        if(!move_uploaded_file($event_calc_file_to_add, trim($event_save_file_path . $save_event_name_file, " $%&=?"))){
                            ?>
                                <div>
                                    <h2 style="color: red;">Napaka pri nalaganju CALC datoteke!</h2>
                                </div>
                            <?php
                        }
                        else{
                            ?>
                                <div>
                                    <h2">Dogodek uspešno dodan!</h2>
                                    <a href="add_event.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                </div>
                            <?php
                        }
                    }
                    else{
                        ?>
                            <div>
                                <h2">Dogodek uspešno dodan!</h2>
                                <a href="add_event.php" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                }
            }
        }
    ?>




    <table>
        <tr><th style="text-align: center;" colspan="9">DOGODKI</th></tr>
        <tr>
            <th>Ime dogodka</th>
            <th>CALC datoteka</th>
            <th>Konec dogodka</th>
            <th>Zahtevaj log za <br>pridobitev diplome</th>
            <th>Glavni log</th>
            <th>Upravljatelji</th>
            <th>Diplome</th>
            <th>Uredi</th>
            <th>Izbriši</th>
        </tr>
        <?php 
            $query = "SELECT * FROM `dogodki`;";
            $res = mysqli_query($con, $query);
            while($row = mysqli_fetch_assoc($res)){
                $dipl_stm = "";
                if($row['DIPL_1_DATA'] == NULL){
                    $dipl_stm = "style=\"color: red;\"";
                }
                ?>
                    <tr>
                        <td><?php echo $row['IME_DOGODKA']; ?></td>
                        <td><a href="event_calc/<?php echo trim($row['CALC_PHP'], " "); ?>"><?php echo $row['CALC_PHP']; ?></a></td>
                        <td><?php echo $row['KONC']; ?></td>
                        <td><?php echo NameRightsState($row['REQUIRE_LOG'] ?? "0") ?></td>
                        <td><?php echo $row['MAIN_LOG']; ?></dh>
                        <td><?php echo is_array($c = json_decode($row['EVENT_CONTROLL'], true)) ? htmlspecialchars(implode(', ', $c)) : ''; ?></td>
                        <td><a href="edit_event.php?eid=<?php echo $row['DOGODEK_ID']; ?>" target="_self" <?php echo $dipl_stm; ?>>DIPLOME</a></td>
                        <td><a href="add_event.php?mode=edit&eid=<?php echo $row['DOGODEK_ID']; ?> " target="_self">UREDI</a></td>
                        <td><a href="add_event.php?mode=delete&eid=<?php echo $row['DOGODEK_ID']; ?> " target="_self">IZBRIŠI</a></td>
                    </tr>
                <?php
            }
        ?>
    </table>

   <?php 

   ?> 



    <div>
        <h2 style="text-align: center;">DODAJ DOGODEK</h2>
        <form action="add_event.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="add_event_name" placeholder="IME DOGODKA" required>
            <label for="datum">Rok za oddajo loga:</label>
            <br>
            <input type="date" id="datum" name="add_event_date" required style="width: 100%; height: 40px;">
            <br>
            <br>
            <label for="datoteka">Datoteka za izračin točk:</label>
            <br>
            <input type="file" id="datoteka" name="calc_file" accept=".php" required style="width: 100%; height: 40px;">
            <input type="hidden" name="mode" value="add">
            <br>
            <input type="checkbox" name="add_rl" id="require_log_add_form">
            <label for="require_log_add_form">Zahtevaj LOG za prenos diplome</label>
            <br>
            <input type="checkbox" name="udl" id="diffrent_log_form">
            <label for="diffrent_log_form">Vpisuj zveze v drugi log</label>

            <div id="different_log_options" style="display: none; margin-top: 10px;">
                <label for="log_select">Izberi log:</label> 
                <select name="add_log" id="log_select" style="width: 100%; height: 40px;"> 
                    <option value="" selected disabled>-- Izberi log --</option>
                    <?php 
                        for($i = 0; $i < count($log_names); $i++){
                            ?>
                                <option value="<?php echo $log_names[$i]; ?>"><?php echo $log_names[$i]; ?></option>
                            <?php
                        }
                    ?>
                </select> 
                <br>
                <br>
                <label for="event_select">Izberi vodje:</label> 
                <select name="controllers[]" id="event_select" multiple style="width: 100%;"> 
                    <option value="" selected disabled>-- Izberi voditelje --</option>
                    <?php 
                        for($i = 0; $i < count($usernames); $i++){
                            ?>
                                <option value="<?php echo $usernames[$i]; ?>"><?php echo $usernames[$i]; ?></option>
                            <?php
                        }
                    ?>
                </select> 
            </div>

            <script> 
                const checkbox = document.getElementById("diffrent_log_form"); 
                const options = document.getElementById("different_log_options"); 
                checkbox.addEventListener("change", function () { 
                    if (this.checked) { 
                        options.style.display = "block"; 
                    }
                    else { 
                        options.style.display = "none"; 
                    } 
                }); 
            </script>


            <input type="submit" value="VPIŠI" style="margin-top:10px;">
        </form>
    </div>

            <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA DOMAČO STRAN</div></a>
    </div>

    <footer>
        <p>© 2025 <a href="https://lovro7.eu">Lovro Kočevar Ribič</a>, S57LKR</p>
    </footer>

</body>
</html>