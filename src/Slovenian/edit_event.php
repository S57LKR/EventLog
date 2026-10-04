<?php
    require_once("db.php");
    session_start();

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

    if(!isset($_GET['eid'])){
        die("Niste izbrali dogodka!");
    }

    $event_id = $_GET['eid'];

    $card_collum_names = ["DIPL_1_DATA", "DIPL_2_DATA", "DIPL_3_DATA"];

    $query = "SELECT * FROM `dogodki` WHERE `DOGODEK_ID` = '$event_id'";
    $res = mysqli_query($con, $query);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UREDI DIPLOME</title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>

    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>UREDI DIPLOME</h1> 
        </header>
    </a>
    <br>

    <?php 
        if(isset($_GET['mode']) && isset($_GET['dn'])){
            if($_GET['mode'] == "del"){
                $dipl_n_del = $_GET['dn'];
                if($dipl_n_del == 1){
                    $query = "UPDATE `dogodki` SET `DIPL_1_DATA` = `DIPL_2_DATA`, `DIPL_2_DATA` = `DIPL_3_DATA`, `DIPL_3_DATA` = NULL WHERE `DOGODEK_ID` = '$event_id';";
                }
                if($dipl_n_del == 2){
                    $query = "UPDATE `dogodki` SET `DIPL_2_DATA` = `DIPL_3_DATA`, `DIPL_3_DATA` = NULL WHERE `DOGODEK_ID` = '$event_id';";
                }
                if($dipl_n_del == 3){
                    $query = "UPDATE `dogodki` SET `DIPL_3_DATA` = NULL WHERE `DOGODEK_ID` = '$event_id';";
                }

                if(mysqli_query($con, $query)){
                    ?>
                        <div>
                            <p>Diploma uspešno izbrisana</p>
                            <a href="edit_event.php?eid=<?php echo $event_id; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                        </div>
                    <?php
                }
                else{
                    ?>
                        <div>
                            <p style="color: red;">Napaka pri brisanju diplome!</p>
                        </div>
                    <?php
                }
            }
        }
    ?>

    <div>
        <h2 style="text-align: center;">VNEŠENE DIPLOME</h2>
        <?php 
            $dipl_n = 0;
            $row = mysqli_fetch_assoc($res);
            for($i = 0; $i < 3; $i++){
                if(is_null($row[$card_collum_names[$i]])){
                    break;
                }
                $dipl_n++;
                $json = json_decode($row[$card_collum_names[$i]], true);
                ?>
                    <div>
                        <h3 style="text-align: center;">Diploma <?php echo $dipl_n; ?></h3>
                        <h3 style="text-align: center;"><?php echo $json['CARD']['NAME']; ?></h3>
                        <br>
                        <a href="card/<?php echo $json['CARD']['URL']; ?>" target="_blank">Diploma</a>
                        <p>Število točk: <?php echo $json['CARD']['POINT_N']; ?></p>
                        <br>
                        <a href="edit_event.php?mode=del&eid=<?php echo $event_id; ?>&dn=<?php echo $dipl_n; ?>" class="delite_dipl_link" style="text-decoration: none;" target="_self"><div class="admin_url">IZBRIŠI DIPLOMO</div></a>
                        <form action="get_qsl_card.php" method="post">
                            <input type="hidden" name="call" value="S59EKL">
                            <input type="hidden" name="points" value="10">
                            <input type="hidden" name="eid" value="<?php echo $event_id; ?>">
                            <input type="hidden" name="n" value="Test Imena">
                            <input type="hidden" name="mode" value="test">
                            <input type="hidden" name="card" value="<?php echo $dipl_n; ?>">
                            <input type="submit" value="PREDOGLED DIPLOME">
                        </form>
                    </div>
                <?php
            }
        ?>
    </div>

    <br>
    <div>
        <?php 
            if($dipl_n < 3){
                ?>
                    <h2 style="text-align: center;">DODAJ DIPLOME</h2>
                    <form action="card/edit_card.php" method="POST" enctype="multipart/form-data">
                        <div id="diplom_1_data" style="margin-top: 10px;">
                            <h3>Diploma <?php echo $dipl_n + 1; ?></h3>
                            <label for="name">Ime diplome</label>
                            <br>
                            <input type="text" name="card_name" id="name" style="width: 90%; height: 30px;">
                            <br>
                            <br>
                            <label for="min_p_id">Minimalno število točk</label>
                            <br>
                            <input type="number" name="min_p" id="min_p_id" style="width: 30%; height: 30px;">
                            <br>
                            <br>
                            <label for="empty_card_id">Diploma</label>
                            <br>
                            <input type="file" name="empty_card" id="empty_card_id">
                            <input type="hidden" name="eid" value="<?php echo $event_id; ?>"> 
                            <input type="hidden" name="card_n" value="<?php echo $dipl_n + 1; ?>">
                        </div>

                        <input type="submit" value="DODAJ" style="margin-top:10px;">
                    </form>
                <?php
            }
            else{
                ?>
                    <h2 style="text-align: center;">Dosegli ste najvišje število diplom za ta dogodek</h2>
                <?php
            }
        ?>

    </div>

    <div>
        <a href="add_event.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA DOGODKE</div></a>
    </div>

    <footer>
        <p>© 2025 <a href="https://lovro7.eu">Lovro Kočevar Ribič</a>, S57LKR</p>
    </footer>

        <script>
        document.querySelectorAll(".delite_dipl_link").forEach(link => {
            link.addEventListener("click", function(event) {
                if(!confirm("Ali ste prepričani, da želite spremeniti status izbranega uporabnika?")){
                    event.preventDefault();
                }

            });
        });

    </script>
    
</body>
</html>