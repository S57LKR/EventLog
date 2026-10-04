<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>REGISTRACIJA | S59EKL</title>
    <link rel="stylesheet" href="style/style.css?v=4">
</head>

<body>
    <header>
        <h1>REGISTRACIJA</h1>
    </header>

    <br>

<?php
    require_once("db.php");
    

    //Obrazec za podatke
    if(!isset($_POST['di'])){
        ?>
                <div style="text-align: center;">
                    <ht>VPIŠI ZAHTEVANE PODATKE</ht>
                    <form action="signin.php" method="post">
                        <input type="text" name="name_entry" placeholder="KLICNI ZNAK">
                        <input type="email" name="email_entry" placeholder="EMAIL">
                        <input type="password" name="pass_entry" id="pass_entry" placeholder="GESLO">
                        <input type="password" name="pass_entry_2" id="pass_entry_2" placeholder="PONOVNO VPIŠITE GESLO">
                        <br>
                        <br>
                        <button type="button" onclick="togglePassword()" style="border: none; text-decoration: underline; color: #0000EE;">Pokaži geslo</button>
                        <br>
                        <br>
                        <input type="submit" id="submitBtn" value="REGISTRACIJA">
                        <input type="hidden" name="di" value="1">
                        <p id="passwordWarning"></p>
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
                        </script>
                    </form>
                </div>

                <script>
                const pass_entry = document.getElementById("pass_entry");
                const pass_entry_2 = document.getElementById("pass_entry_2");
                const form = document.querySelector("form");
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
        <?php
    }
    else{
        //Mankajoci podatki
        if(empty($_POST['name_entry']) || empty($_POST['email_entry']) || empty($_POST['pass_entry'])){
            ?>
                <div>
                    <h2 style="color: red;">Manjkajo podatki za prijavo!</h2>
                    <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA GLAVNO STRAN</div></a>
                </div>
            <?php
        }
        else{
            $entry_name = strtoupper($_POST['name_entry']);
            $entry_mail = $_POST['email_entry'];

            //Obstojeci podatki
            if(checkSignInData($con, $entry_name, $entry_mail)){
                ?>
                    <div>
                        <h2 style="color: red;">Email ali uporabniško ime že obstaja!</h2>
                        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA GLAVNO STRAN</div></a>
                    </div>
                <?php
            }
            else{
                //Sign in

                //Create user
                $log_name = strtolower($entry_name) . "_log";
                $hash = password_hash($_POST['pass_entry'], PASSWORD_DEFAULT);

                $query = "INSERT INTO `users`
                        (`USERNAME`, `MAIL`, `LOG_NAME`, `RIGHTS`, `PASS`)
                        VALUES (?, ?, ?, ?, ?)";

                $stmt = mysqli_prepare($con, $query);
                $rights = "000000";
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssis",
                    $entry_name,
                    $entry_mail,
                    $log_name,
                    $rights,
                    $hash
                );

                if(!mysqli_stmt_execute($stmt)){
                    die("Napaka pri ustvarjanju računa - Kontaktiraj administratorja: " . mysqli_error($con));
                }

                //Create log
                $query = "CREATE TABLE `$log_name` (
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
                else{
                    $to = $entry_mail;
                    $subject = "Registracija v log S59EKL";
                    $message = "Pozdravljeni!\n\nUspešno ste se prijavili v S59EKL log.\n\nZa uporabo loga morate počakati da vam administrator oddobri račun. Za pomoč in vprašanje me lahko kontaktirate na lovro.k.ribic@gmail.com.\n\n73 de ekipa S59EKL";
                    $headers = "From: noreply@lovro7.eu\r\n";
                    $headers .= "Reply-To: lovro.k.ribic@gmail.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
                    
                    if(mail($to, $subject, $message, $headers));

                    $to = "lovro.k.ribic@gmail.com";
                    $subject = "Nova prijava v S59EKL log";
                    $message = "Pozdravljeni!\n\nNov uporabnik se je prijavil v log S59EKL.\n\nUporabnika lahko povabiš ali odstraniš na spletni strani pod <UREDI UPORABNIKE>\n\n73 de ekipa S59EKL";
                    $headers = "From: noreply@lovro7.eu\r\n";
                    $headers .= "Reply-To: lovro.k.ribic@gmail.com\r\n";
                    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                    mail($to, $subject, $message, $headers)
                    ?>
                        <div style="text-align: center;">
                            <ht>REGISTRACIJA USPEŠNA!</ht>
                            <p>Počakajte, da vam administrator oddobri uporabniški račun. O tem boste obveščeni na vaš email naslov.</p>
                            <p>Za pomoč me lahko kontaktirate na email: lovro.k.ribic@gmail.com</p>
                            <a href="index.php" target="_self" style="text-decoration: none; color: #222">
                                <div class="raport_btn">
                                    NAZAJ NA GLAVNO STRAN
                                </div>
                            </a>
                        </div>
                    <?php
                }

            
            }
        }
    }



    function checkSignInData($con, $n, $m) {
        $query = "SELECT 1 FROM `users` WHERE `USERNAME` = ? OR `MAIL` = ? LIMIT 1";

        $stmt = mysqli_prepare($con, $query);
        mysqli_stmt_bind_param($stmt, "ss", $n, $m);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        
        return mysqli_num_rows($result) > 0;
    }
?>



<br><br><br><hr><br><br><br>

<footer>
    <p>© 2026 <a href="https://s59ekl.si" target="_blank">S59EKL</a></p>
</footer>

    
</body>
</html>