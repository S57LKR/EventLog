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
            <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }

    $pdf = new Imagick();

    $pdf->setResolution(150, 150);
    $pdf->readImage('ozadje.pdf');

    $pdf->setImageFormat('png');
    $pdf->setImagePage(
        $pdf->getImageWidth(),
        $pdf->getImageHeight(),
        0,
        0
    );

    $pdf->writeImage('ozadje.png');

    $pdf->clear();
    $pdf->destroy();

?>