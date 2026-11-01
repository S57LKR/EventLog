<?php
    session_start();
    ini_set('display_errors', 0);
    require_once("db.php");

    $username = $_SESSION['username'];
    $query = "SELECT `RIGHTS` FROM `users` WHERE `USERNAME` = '$username'";
    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);

    $rights = [
        "usl" => $row['RIGHTS'][0] ?? "0",
        "utl" => $row['RIGHTS'][1] ?? "0",
        "pbu" => $row['RIGHTS'][2] ?? "0",
        "vtl" => $row['RIGHTS'][3] ?? "0"
    ];


    if(!isset($_GET['uid'])){
        echo "Niste ozbrali uporabnika!";
        ?>
            <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }
    $uid = $_GET['uid'];

    $query = "SELECT * FROM `users` WHERE `USER_ID` = '$uid';";
    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);

    if(!isset($_SESSION['username']) || ($username != $row['USERNAME'] && $rights['pbu'] == "0")){
        echo "You don't have permition to accsess that page.";
        ?>
            <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }
    $old_username = $row['USERNAME'];

    function generateAPI($con){
        do {
            $tmp_c = generateCode();

            $query = "SELECT 1 FROM `users` WHERE `API` = ? OR `API_KEY` = ? LIMIT 1";
            $stmt = $con->prepare($query);
            $stmt->bind_param("ss", $tmp_c, $tmp_c);
            $stmt->execute();
            $result = $stmt->get_result();

            $exists = $result->num_rows > 0;

            $stmt->close();

        } while ($exists);

        return $tmp_c;
    }
    function generateCode() {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ#!';
        $code = '';

        for ($i = 0; $i < 10; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }

        return $code;
    }
    function checkSignInData($con, $n, $m, $ou) {
        $query = "SELECT 1 FROM `users` WHERE (`USERNAME` = ? AND `USERNAME` != ?) OR (`MAIL` = ? AND `USERNAME` != ?) LIMIT 1";

        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $n, $ou, $m, $ou);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_num_rows($result) > 0;
    }

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UPORABNIŠKI RAČUN - <?php echo $row['USERNAME']; ?></title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>UPORABNIŠKI RAČUN - <?php echo $row['USERNAME']; ?></h1>
        </header>
    </a>
    <?php 
        if(!isset($_POST['mode']) || $_POST['mode'] == "save" || $_POST['mode'] == "gapi"){
            if(isset($_POST['save_username']) && isset($_POST['save_mail']) && $_POST['mode'] == "save"){
                if(checkSignInData($con, $_POST['save_username'], $_POST['save_mail'], $old_username)){
                    ?>
                        <div>
                            <h3>Uporabnik z tem imenom ali emailom že obstaja!</h3>
                            <form action="user.php?uid=<?php echo $uid; ?>" method="post">
                                <input type="hidden" name="mode" value="edit">
                                <input type="submit" value="NAZAJ NA UREJANJE">
                            </form>
                        </div>
                    <?php
                }
                else{
                    $save_username = strtoupper($_POST['save_username']);
                    $save_mail = $_POST['save_mail'];
                    $query = "UPDATE `users` SET `USERNAME` = '$save_username', `MAIL` = '$save_mail' WHERE `USER_ID` = '$uid';";
                    if(mysqli_query($con, $query)){
                        $_SESSION['username'] = $save_username;
                        ?>
                            <div>
                                <h2>Uporabnik posodobljen!</h2>
                                <p>Če ste spremenili uporabniško ime se morate za delovanje spletne strani odjaviti in ponovno prijaviti!</p>
                                <a href="user.php?uid=<?php echo $uid; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <h2>Napaka pri shranjevanju podatkov!</h2>
                                <a href="user.php?uid=<?php echo $uid; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                    $query = "SELECT * FROM `users` WHERE `USER_ID` = '$uid';";
                    $res = mysqli_query($con, $query);
                    $row = mysqli_fetch_assoc($res);
                    if(!empty($_POST['save_pass_1'])){
                        $hash = password_hash($_POST['save_pass_1'], PASSWORD_DEFAULT);
                        $query = "UPDATE `users` SET `PASS` = ? WHERE `USER_ID` = ?";
                        $stmt = $con->prepare($query);
                        $stmt->bind_param("ss", $hash, $uid);
                        if(!$stmt->execute()){
                            ?>
                                <div>
                                    <h2>Napaka pri shranjevanju gesla!</h2>
                                    <a href="user.php?uid=<?php echo $uid; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                                </div>
                            <?php
                        }
                        $stmt->close();
                    }
                }
            }
            else{
                if($_POST['mode'] == "save"){
                    ?>
                        <div>
                            <h3 style="color: red;">Manjkajo podatki!</h3>
                        </div>
                    <?php
                }
                if($_POST['mode'] == "gapi"){
                    $new_api = generateAPI($con);
                    $new_api_key = generateAPI($con);
                    $query = "UPDATE `users` SET `API` = '$new_api', `API_KEY` = '$new_api_key' WHERE `USER_ID` = '$uid'";
                    if(!mysqli_query($con, $query)){
                        ?>
                            <div>
                                <h2>Napaka pri ustvarjanju APIja in API KAYja!</h2>
                                <a href="user.php?uid=<?php echo $uid; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <h2>API in API KEY uspešno generirana!</h2>
                                <a href="user.php?uid=<?php echo $uid; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                            </div>
                        <?php
                    }
                }
            }
            $query = "SELECT * FROM `users` WHERE `USER_ID` = '$uid';";
            $res = mysqli_query($con, $query);
            $row = mysqli_fetch_assoc($res);
            ?>
                <div>
                    <table>
                        <tr><th style="text-align: center;" colspan="2">PODATKI UPORABNIKA</th></tr>
                        <tr>
                            <th>Uporabniško ime</th>
                            <td><?php echo $row['USERNAME']; ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo $row['MAIL']; ?></td>
                        </tr>
                        <tr>
                            <th>Log</th>
                            <td><?php echo $row['LOG_NAME']; ?></td>
                        </tr>
                        <tr>
                            <th>LOG4OM API</th>
                            <td id="api_td"></td>
                        </tr>
                        <tr>
                            <th>LOG4OM API key</th>
                            <td id="api_key_td"></td>
                        </tr>
                        <tr>
                            <th>Geslo</th>
                            <td>*********</td>
                        </tr>
                        <tr>
                            <th style="text-align: center;" colspan="2">
                                <form action="user.php?uid=<?php echo $uid; ?>" method="post">
                                    <input type="hidden" name="mode" value="edit">
                                    <input type="submit" value="UREDI">
                                </form>
                            </th>
                        </tr>
                    </table>
                    <script>
                        let api_entry_vis = false;
                        let api_key_entry_vis = false;
                        const api_entry = document.getElementById('api_td');
                        const api_key_entry = document.getElementById('api_key_td');

                        const api = "<?php echo $row['API']; ?>";
                        const api_key = "<?php echo $row['API_KEY']; ?>";

                        api_entry.textContent = "*******" + api.slice(-3);
                        api_key_entry.textContent = "*******" + api_key.slice(-3);

                        api_entry.addEventListener("click", function() {
                            if(api_entry_vis == false){
                                api_entry.textContent = api;
                                api_entry_vis = true;
                            }
                            else{
                                api_entry.textContent = "*******" + api.slice(-3);
                                api_entry_vis = false;
                            }
                        });
                        api_key_entry.addEventListener("click", function() {
                            if(api_key_entry_vis == false){
                                api_key_entry.textContent = api_key;
                                api_key_entry_vis = true;
                            }
                            else{
                                api_key_entry.textContent = "*******" + api_key.slice(-3);
                                api_key_entry_vis = false;
                            }
                        });
                    </script>
                </div>
                
                <div>
                    <form action="user.php?uid=<?php echo $uid; ?>" method="post">
                        <input type="hidden" name="mode" value="gapi">
                        <input type="submit" value="GENERIRAJ NOV API">
                    </form>
                </div>
            <?php
        }
        if(isset($_POST['mode']) && $_POST['mode'] == "edit"){
            ?>
                <div>
                    <table>
                        <form action="user.php?uid=<?php echo $uid; ?>" method="post" id="edit_form">
                            <tr><th style="text-align: center;" colspan="2">PODATKI UPORABNIKA</th></tr>
                            <tr>
                                <th>Uporabniško ime</th>
                                <td><input type="text", name="save_username" value="<?php echo $row['USERNAME']; ?>"></td>
                            </tr>
                            <tr>
                                <th>Email</th>
                                <td><input type="text", name="save_mail" value="<?php echo $row['MAIL']; ?>"></td>
                            </tr>
                            <tr>
                                <th>Log</th>
                                <td><?php echo $row['LOG_NAME']; ?></td>
                            </tr>
                            <tr>
                                <th>LOG4OM API</th>
                                <td><?php echo $row['API']; ?></td>
                            </tr>
                            <tr>
                                <th>LOG4OM API key</th>
                                <td><?php echo $row['API_KEY']; ?></td>
                            </tr>
                            <tr>
                                <th>Geslo</th>
                                <td><input type="password", name="save_pass_1" id="pass_entry"></td>
                            </tr>
                            <tr>
                                <th>Potrdi Geslo</th>
                                <td><input type="password", name="save_pass_2" id="pass_entry_2"></td>
                            </tr>
                            <tr>
                                <td style="text-align: center;" colspan="2">
                                    <button type="button" onclick="togglePassword()" style="border: none; text-decoration: underline; color: #0000EE;">Pokaži geslo</button>
                                    <p id="passwordWarning"></p>
                                </td>
                            </tr>
                            <tr>
                                <th style="text-align: center;" colspan="2">
                                        <input type="hidden" name="mode" value="save">
                                        <input type="submit" value="SHRANI" id="submitBtn">
                                </th>
                            </tr>
                            <script>
                                function togglePassword() {
                                    const password_1 = document.getElementById("pass_entry");
                                    const password_2 = document.getElementById("pass_entry_2");
                                
                                    if (password_1.type === "password") {
                                        password_1.type = "text";
                                    } else {
                                        password_1.type = "password";
                                    }
                                    if (password_2.type === "password") {
                                        password_2.type = "text";
                                    } else {
                                        password_2.type = "password";
                                    }
                                }
                                const form = document.getElementById('edit_form')
                                const pass_entry = document.getElementById("pass_entry");
                                const pass_entry_2 = document.getElementById("pass_entry_2");
                                const warning = document.getElementById("passwordWarning");

                                form.addEventListener("submit", function(event) {
                                    if (pass_entry.value !== pass_entry_2.value) {
                                        event.preventDefault();

                                        warning.textContent = "Gesli se ne ujemata!";
                                        warning.style.color = "red";
                                    } else {
                                        warning.textContent = "";
                                    }
                                });
                            </script>
                        </form>
                    </table>
                </div>
            <?php
        }
    ?>

    <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA DOMAČO STRAN</div></a>
    </div>
    <br>
    <br>


    <footer>
        <p>© 2026 <a href="https://s59ekl.si">S59EKL</a></p>
    </footer>
    
</body>
</html>