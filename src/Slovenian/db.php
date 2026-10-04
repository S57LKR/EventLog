

<?php
    $con = mysqli_connect("localhost", "root", "", "s59ekl_priz");
    if(!isset($con)){
        die("Napaka pri prijavi v račun" . $con->connect_error);
    }
    
?>

