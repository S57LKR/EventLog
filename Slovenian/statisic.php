<?php
    require_once("db.php");


    function chackDate($d) {
        if (strtotime($d) > time()) {
            return 0;
        }
        return 1;
    }

    $guest_callsign = strtoupper($_GET['guest_callsign']);
    session_start();
    $username = $guest_callsign;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STATISTIKA | <?php echo $guest_callsign; ?></title>
    <link rel="stylesheet" href="style/style.css?v=2">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1><?php echo $guest_callsign; ?> ZVEZE</h1>
        </header>
    </a>
        <div style="text-align: center;">
            <p style="color: red;">Za prenos diplome nekaterih dogodkov morate biti prijavljeni in oddati log</p>
            <p>Ti podatko so iz logov drugih uporabnikov!</p>
        </div>
        <br>
        <?php 
        $require_log = 0;
            if(isset($_GET['event']) && isset($_GET['guest_callsign'])){
                if(!empty($_GET['event']) && empty($_GET['guest_callsign'])){
                    ?>
                        <div>
                            <h2>Manjka klicni znak ali dogodek za iskanje!</h2>
                        </div>
                        <br>
                        <div>
                            <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA GLAVNO STRAN</div></a>
                        </div>
                    <?php
                    exit();
                }
                $search_event_id = $_GET['event'];
                $query = "SELECT * FROM `dogodki` WHERE `DOGODEK_ID` = '$search_event_id';";
                $res = mysqli_query($con, $query);
                $row_event = mysqli_fetch_assoc($res);
                $event_name = $row_event['IME_DOGODKA'];
                $event_date = $row_event['KONC'];
                $require_log = (int)$row_event['REQUIRE_LOG'];
                $sc = $guest_callsign;
                $se = $search_event_id;
                require_once(trim("event_calc/" . trim($row_event['CALC_PHP'], " "), " "));
            }
        ?>

        <table>
            <tr><th style="text-align: center;" colspan="8">VSE ZVEZE</th></tr>
            <tr>
                <th>KLICNI ZNAK</th>
                <th>DATE dd/mm/yyyy</th>
                <th>TIME UTC</th>
                <th>BAND</th>
                <th>MODE</th>
                <th>RST S</th>
                <th>RST R</th>
                <th>TOČKE</th>
            </tr>
            
            <?php
                $qso_num = 0;
                $tocke_count = 0;
                if(count($event_search_res) > 0){
                    for($i = 0; $i < count($event_search_res); $i++){
                        $qso_num++;
                        $table_row = $event_search_res[$i];
                        $tocke_count = $tocke_count + $table_row['tocke'];
            ?>
                <tr>
                    <td><?php echo strtoupper($table_row['callsign']); ?></td>
                    <td><?php echo $table_row['date']; ?></td>
                    <td><?php echo $table_row['time']; ?></td>
                    <td><?php echo $table_row['band']; ?></td>
                    <td><?php echo $table_row['mode']; ?></td>
                    <td><?php echo $table_row['rst_r']; ?></td>
                    <td><?php echo $table_row['rst_s']; ?></td>
                    <td><?php echo $table_row['tocke']; ?></td>
                </tr>
            <?php 
                    }
                }
            ?>
            <tr><th style="text-align: center;" colspan="8">ŠTEVILO ZVEZ: <?php //echo $qso_num; ?></th></tr>
            <?php 
                if($require_log == 1){
                    ?>
                        <tr><th style="text-align: center;" colspan="8">ŠTEVILO TOČK: <?php echo $tocke_count; ?></th></tr>
                    <?php
                }
            ?>
        </table> 
        <br>
        <br>
        <?php 
            if($require_log != 1){//
                if(chackDate($event_date)){
                    if($tocke_count >= $min_poits){
                        ?>
                            <div>
                                <p style="color: red;">Ne uporabljajte šumnikov!</p>
                                <form action="get_qsl_card.php", method="post">
                                    <input type="text" name="n" placeholder="IME IN PRIIMER">
                                    <input type="hidden" name="call" value="<?php echo strtoupper($guest_callsign) ?>">
                                    <input type="hidden" name="points" value="<?php echo $tocke_count ?>">
                                    <input type="hidden" name="eid" value="<?php echo $search_event_id ?>">
                                    <input type="submit" value="PRENESI DIPLOMO">
                                </form>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <p>Žal niste izpolnili pogojev za diplomo.</p>
                            </div>
                        <?php
                    }
                }
                else{
                ?>
                    <div>
                        <p>Prenos diplome je možen po <?php echo $event_date; ?></p>
                    </div>
                <?php
                }
            }
            else{
                ?>
                    <div>
                        <p>Za prenos diplome morate biti prijavljeni in oddati log</p>
                    </div>
                <?php
            }
        ?>

        <br><br><br><br><br><br><br><br><br><br><br><br>


    <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA GLAVNO STRAN</div></a>
    </div>
    
        <footer>
        <p>© 2025 <a href="https://lovro7.eu">Lovro Kočevar Ribič</a>, S57LKR</p>
    </footer>
    
</body>
</html>
