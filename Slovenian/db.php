

<?php
    //SERVER
    /*
    $con = mysqli_connect("localhost", "lovro_s59ekl_system", "9i.XLrkjuIW;Lx]D", "lovro_s59ekl");
    if(!isset($con)){
        die("Napaka pri prijavi v račun" . $con->connect_error);
    }
    */
    //TEST
    
    $con = mysqli_connect("localhost", "root", "", "s59ekl_priz");
    if(!isset($con)){
        die("Napaka pri prijavi v račun" . $con->connect_error);
    }
    
?>

