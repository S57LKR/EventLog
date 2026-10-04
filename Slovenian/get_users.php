<?php
    require_once("db.php");

    $query = "SELECT `USERNAME`, `LOG_NAME` FROM `users` WHERE `USERNAME` != 'ADMIN';";
    $res = mysqli_query($con, $query);

    while ($row = mysqli_fetch_assoc($res)) {
        $usernames[] = $row['USERNAME'];
        $log_names[] = $row['LOG_NAME'];
    }
?>



