<?php
    require_once("../db.php");
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
            <a href="../index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SISTEM</title>

    <link rel="stylesheet" href="../style/style.css?v=3">
</head>

<body>
    <?php 
        if(!isset($_POST['eid'])){
            ?>
                <div>
                    <p style="color: red;">Niste izbrali dogodka!</p>
                </div>
            <?php
        }
        else{
            if(!isset($_POST['txt']) || !isset($_POST['cardn'])){
                ?>
                    <div>
                        <p style="color: red;">Manjkajo podatki</p>
                    </div>
                <?php
            }
            else{
                $card_collum_names = ["DIPL_1_DATA", "DIPL_2_DATA", "DIPL_3_DATA"];

                $event_id = $_POST['eid'];
                $card_n = $_POST['cardn'];
                $json = $_POST['txt'];
                echo $json;
                $collum_name = $card_collum_names[((int)$card_n) - 1];
                $query = "UPDATE `dogodki` SET `$collum_name` = '$json' WHERE `DOGODEK_ID` = '$event_id';";
                if(mysqli_query($con, $query)){
                    ?>
                        <div>
                            <p>Diploma uspešno hranjena</p>
                            <a href="../edit_event.php?eid=<?php echo $event_id; ?>" target="_self" style="text-decoration: none;"><div class="admin_url">OK</div></a>
                        </div>
                    <?php
                }
                else{
                    ?>
                        <div>
                            <p style="color: red;">Shranjevanje diplome neuspešno!</p>
                        </div>
                    <?php
                }
            }
        }
    ?>

    <a href="../index.php" target="_self" title="HOME">
        <header>
            <h1>UREDI DIPLOMO</h1>
        </header>
    </a>

</body>