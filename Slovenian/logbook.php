<?php
    $qsos_per_page = 15;
    $il = "log";
    //TODO: preverjanje zvez med logi


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
            "vtl" => $row['RIGHTS'][3] ?? "0"
        ];
    
    //ko klicni znak za urejanje ni moj in nimam vtl in utl
    if(!(($rights['vtl'] == "1" || $rights['utl'] == "1") && $_GET['call'] != $_SESSION['username'])){
        if($_GET['call'] != $_SESSION['username']){
            echo "Nimate dovoljenja za ogled strani";
            ?>
                <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
            <?php
            exit();
        }
    }
    ///// nadomesti z datoteko
    $search_call = $_GET['call'];
    $username = $_SESSION['username'];
    $curent_qso_page = $_GET['pn'] ?? 0;
    $query = "SELECT `LOG_NAME` FROM `users` WHERE `USERNAME` = '$search_call';";
    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);
    $search_table_name = $row['LOG_NAME'];
    $filter = $_GET['filter'] ?? "";

    //querys za iskanje imen tabel
    ///////


        function chackDate($d) {
        if (strtotime($d) > time()) {
            return 0;
        }
        return 1;
    }
?>

    <?php 
        if(isset($_GET['mode'])){
            if($_GET['mode'] == "save"){
                $edit_table_name = $search_table_name;
                $save_qso_num = $_GET['save_qso_n'];
                $edit_call    = $_GET['edit_call'];
                $edit_date    = $_GET['edit_date'];
                $edit_time    = $_GET['edit_time'];
                $edit_band    = $_GET['edit_band'];
                $edit_mode    = $_GET['edit_mode'];
                $edit_rst_s   = $_GET['edit_rst_s'];
                $edit_rst_r   = $_GET['edit_rst_r'];
                $edit_name    = $_GET['edit_name'];
                $curent_qso_page = $_GET['pn'] ?? 0;

                $query = "UPDATE `$edit_table_name`
                        SET `callsign` = '$edit_call',
                            `date`     = '$edit_date',
                            `time`     = '$edit_time',
                            `band`     = '$edit_band',
                            `mode`     = '$edit_mode',
                            `rst_r`    = '$edit_rst_r',
                            `rst_s`    = '$edit_rst_s',
                            `name`     = '$edit_name'
                        WHERE `qso_num` = '$save_qso_num'";

                mysqli_query($con, $query);
            }
                if($_GET['mode'] == "delete" && isset($_GET['qso'])){
                $qso_to_delete = $_GET['qso'];
                $query = "DELETE FROM `$search_table_name` WHERE `qso_num` = '$qso_to_delete'";
                mysqli_query($con, $query);
            }
        }
    ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MY LOGBOOK</title>
    <link rel="stylesheet" href="style/style.css">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1><?php echo strtoupper($search_call) ?> LOG</h1>
        </header>
    </a>

       
       <?php 
        if(!isset($_GET['event'])){
            $query = "SELECT * FROM `dogodki`;";
            $res = mysqli_query($con, $query);
            ?>
                <div>
                    <form>
                        <select name="event" id="izbira" required style="width: 100%; height: 40px;">
                            <option value="" selected disabled>-- Izberi dogodek --</option>
                            <?php 
                                while($row = mysqli_fetch_assoc($res)){
                                    ?>
                                        <option value="<?php echo $row['DOGODEK_ID']; ?>"><?php echo $row['IME_DOGODKA']; ?></option>
                                    <?php
                                }
                            ?>
                        </select>
                        <input type="submit", value="PREVERI ZVEZE">
                        <input type="hidden" name="call" value="<?php echo $search_call ?>">
                        <input type="hidden" name="pn" value="<?php echo $curent_qso_page; ?>">
                        <input type="hidden" name="filter" value="<?php echo $filter; ?>">

                    </form>
                </div>
            <?php
        }
        else{
            $search_event_id = $_GET['event'];
            $query = "SELECT * FROM `dogodki` WHERE `DOGODEK_ID` = '$search_event_id';";
            $res = mysqli_query($con, $query);
            $row_event = mysqli_fetch_assoc($res);
            $event_name = $row_event['IME_DOGODKA'];
            $event_date = $row_event['KONC'];
            $sc = $search_call;
            $se = $search_event_id;
            require_once(trim("event_calc/" . trim($row_event['CALC_PHP'], " "), " "));
            ?>
                <table>
                    <tr><th style="text-align: center;" colspan="8">POTRJENE ZVEZE</th></tr>
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
                        else{
                            ?>
                                <tr><th style="text-align: center;" colspan="8">Nimate potrjenih zvez</th></tr>
                            <?php
                        }
                    ?>
                    <tr><th style="text-align: center;" colspan="8">ŠTEVILO ZVEZ: <?php echo $qso_num; ?></th></tr>
                    <?php 
                        if($event_name === "SKED"){
                            ?>
                                <tr><th style="text-align: center;" colspan="8">ŠTEVILO TOČK: <?php echo $tocke_count; ?></th></tr>
                            <?php
                        }
                    ?>
                </table>
                <br>
                <?php 
                    if(chackDate($event_date)){
                        ?>
                            <div>
                                <a href="get_qsl_card.php?call=<?php echo strtoupper($search_call) ?>&points=<?php echo $tocke_count ?>" style="text-decoration: none;" target="_self"><div class="admin_url" >PRENSEI DIPLOMO</div></a>
                            </div>
                        <?php
                    }
                    else{
                        ?>
                            <div>
                                <p>Prenos diplome je možen po <?php echo $event_date; ?></p>
                            </div>
                        <?php
                    }
                ?>

            <?php //esle
        } //else
       ?>

                <?php 
                    if($filter == "") $query = "SELECT COUNT(*) AS `count` FROM `$search_table_name`;";
                    else $query = "SELECT COUNT(*) AS `count` FROM `$search_table_name` WHERE `callsign` LIKE '$filter';";
                    $res = mysqli_query($con, $query);
                    $row = mysqli_fetch_assoc($res);
                    $tot_qso_num = $row['count'];
                    $page_num_tot = intdiv($tot_qso_num, $qsos_per_page);
                    if(($tot_qso_num % $qsos_per_page) > 0) $page_num_tot++;
                    $start_qso_num = $curent_qso_page * $qsos_per_page;
                ?>
                <br>
                <div>
                    <?php 
                        for($i = 0; $i < $page_num_tot; $i++){
                            if($i == $curent_qso_page){
                                ?>
                                    <a href="logbook.php?call=<?php echo $search_call; ?>&pn=<?php echo $i; ?>&filter=<?php echo $filter; ?>" class="admin_url" style="display:inline-block; width: auto; background-color: gray;"><?php echo $i + 1; ?></a>
                                <?php
                            }
                            else{
                                ?>
                                    <a href="logbook.php?call=<?php echo $search_call; ?>&pn=<?php echo $i; ?>&filter=<?php echo $filter; ?>" class="admin_url" style="display:inline-block; width: auto;"><?php echo $i + 1; ?></a>
                                <?php
                            }
                        }
                    ?>
                    <form action="logbook.php" method="get">
                        <?php 
                        ?>
                        <input type="text" name="filter" id="filter_input" placeholder="KLICNI ZNAK" value="<?php echo $filter; ?>">
                        <input type="hidden" name="pn" value="<?php echo $curent_qso_page; ?>">
                        <input type="hidden" name="call" value="<?php echo $search_call; ?>">
                        <input type="submit" value="IŠČI" style="width: 48%;">
                        <input type="submit" id="clr_btn" value="POČISTI" style="width: 48%;">
                    </form>
                    <script>
                        const clear_btn = document.getElementById('clr_btn');
                        const filter_input = document.getElementById('filter_input');
                        clear_btn.addEventListener("click", function(){
                            filter_input.value = "";
                        })
                    </script>
                </div>
                <table>
                    <tr><th style="text-align: center;" colspan="11">OSEBNI LOG</th></tr>
                    <tr>
                        <th>KLICNI ZNAK</th>
                        <th>DATE dd/mm/yyyy</th>
                        <th>TIME UTC</th>
                        <th>BAND</th>
                        <th>MODE</th>
                        <th>RST S</th>
                        <th>RST R</th>
                        <th>IME<br>OPERATERJA</th>
                        <th>DOGODEK</th>
                        <th>UREDI</th>
                        <th>IZBRIŠI</th>
                    </tr>
                    
                    <?php
                        $qso_num = 0;
                            $query = "SELECT * FROM `dogodki`;";
                            $res = mysqli_query($con, $query);
                            $event_ids = [];
                            $event_names = [];
                            while($row = mysqli_fetch_assoc($res)){
                                $event_ids[] = $row['DOGODEK_ID'];
                                $event_names[] = $row['IME_DOGODKA'];
                            }
                            $event_ids[] = "-1";
                            $event_names[] = "Izbrisan";


                            $table_name = $_SESSION['log_name'];
                            if($filter == "") $query = "SELECT * FROM `$search_table_name` LIMIT $start_qso_num, $qsos_per_page;";
                            else $query = "SELECT * FROM `$search_table_name` WHERE `callsign` LIKE '$filter' LIMIT $start_qso_num, $qsos_per_page;";
                            $res = mysqli_query($con, $query);
                            while($row = mysqli_fetch_assoc($res)){
                                $qso_num++;
                                $index = array_search($row['DOGODEK'], $event_ids);
                                $index = ($index !== false) ? $index : array_search("-1", $event_ids);
                    ?>
                        <tr>
                            <td><?php echo $row['callsign']; ?></td>
                            <td><?php echo $row['date']; ?></td>
                            <td><?php echo $row['time']; ?></td>
                            <td><?php echo $row['band']; ?></td>
                            <td><?php echo $row['mode']; ?></td>
                            <td><?php echo $row['rst_s']; ?></td>
                            <td><?php echo $row['rst_r']; ?></td>
                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $event_names[$index]; ?></td>
                            <?php 
                                if($rights['utl'] == "1" || $rights['usl']) {
                                    ?>
                                        <td><a href="logbook.php?call=<?php echo $search_call ?>&mode=edit&qso=<?php echo $row['qso_num']; ?>&pn=<?php echo $curent_qso_page ?>&filter=<?php echo $filter; ?>" target="_self">UREDI</a></td>
                                        <td><a href="logbook.php?call=<?php echo $search_call ?>&mode=delete&qso=<?php echo $row['qso_num']; ?>&pn=<?php echo $curent_qso_page ?>&filter=<?php echo $filter; ?>" target="_self">IZBRIŠI</a></td>
                                    <?php
                                }
                            ?>
                        </tr>
                    <?php
                            }
                    ?>
                    <tr><th style="text-align: center;" colspan="11">ŠTEVILO ZVEZ: <?php echo $tot_qso_num; ?></th></tr>
                </table>


        <?php
            if(isset($_GET['mode'])){
                if($_GET['mode'] == "edit" && isset($_GET['qso'])){
                    $curent_qso_page = $_GET['pn'] ?? 0;
                    $qso_num_to_edit = $_GET['qso'];
                    $query = "SELECT * FROM `$search_table_name` WHERE `qso_num` = '$qso_num_to_edit';";
                    $res = mysqli_query($con, $query);
                    $row = mysqli_fetch_assoc($res);
                    ?>
                        <form action="logbook.php" method="get">
                            <table>
                                <tr><th style="text-align: center;" colspan="8">UREDI ZVEZO</th></tr>
                                <tr>
                                    <th>KLICNI ZNAK</th>
                                    <th>DATE dd/mm/yyyy</th>
                                    <th>TIME UTC</th>
                                    <th>BAND</th>
                                    <th>MODE</th>
                                    <th>RST S</th>
                                    <th>RST R</th>
                                    <th>IME<br>OPERATERJA</th>
                                    <th>SHRANI</th>
                                </tr>
                                <tr>
                                    <td><input type="text" name="edit_call" value="<?php echo $row['callsign']; ?>"></td>
                                    <td><input type="text" name="edit_date" value="<?php echo $row['date']; ?>"></td>
                                    <td><input type="text" name="edit_time" value="<?php echo $row['time']; ?>"></td>
                                    <td><input type="text" name="edit_band" style="width: 50%;" value="<?php echo $row['band']; ?>"></td>
                                    <td><input type="text" name="edit_mode" style="width: 50%;" value="<?php echo $row['mode']; ?>"></td>
                                    <td><input type="text" name="edit_rst_s" style="width: 50%;" value="<?php echo $row['rst_s']; ?>"></td>
                                    <td><input type="text" name="edit_rst_r" style="width: 50%;" value="<?php echo $row['rst_r']; ?>"></td>
                                    <td><input type="text" name="edit_name" value="<?php echo $row['name']; ?>"></td>
                                    <td><input type="submit" value="SHRANI"></td>
                                    <input type="hidden" name="call" value="<?php echo $search_call ?>">
                                    <input type="hidden" name="pn" value="<?php echo $curent_qso_page; ?>">
                                    <input type="hidden" name="save_qso_n" value="<?php echo $qso_num_to_edit ?>">
                                    <input type="hidden" name="mode" value="save">
                                    <input type="hidden" name="filter" value="<?php echo $filter; ?>">
                                </tr>
                                <tr><th style="text-align: center;" colspan="8">ŠTEVILO ZVEZ: <?php echo $qso_num; ?></th></tr>
                            </table> 
                        </form>
                    <?php
                }
            }
        ?>







        <div style="text-align: center;">
            <?php 
                $sql = "SELECT band, COUNT(*) AS count
                    FROM `$table_name`
                    WHERE 1
                    GROUP BY band
                    ORDER BY band;";

                $res = mysqli_query($con, $sql);

                $band_counts = [];

                while ($row = mysqli_fetch_assoc($res)) {
                    $band_counts[$row['band']] = $row['count'];
                }

                $sql = "SELECT mode, COUNT(*) AS count
                FROM `$table_name`
                WHERE 1
                GROUP BY mode
                ORDER BY mode;";

                $res = mysqli_query($con, $sql);

                $mode_counts = [];

                while($row = mysqli_fetch_assoc($res)){
                    $mode_counts[$row['mode']] = $row['count'];
                }
            ?>
            <br>
            <h2>BAND DATA</h2>
            <table>
                <tr>
                    <th>BAND</th>
                    <th>ŠTEVILO ZVEZ</th>
                </tr>
                <tr>
                    <td>160M</td>
                    <td><?php echo $band_counts['160M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>80M</td>
                    <td><?php echo $band_counts['80M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>60M</td>
                    <td><?php echo $band_counts['60M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>40M</td>
                    <td><?php echo $band_counts['40M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>30M</td>
                    <td><?php echo $band_counts['30M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>20M</td>
                    <td><?php echo $band_counts['20M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>17M</td>
                    <td><?php echo $band_counts['17M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>15M</td>
                    <td><?php echo $band_counts['15M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>12M</td>
                    <td><?php echo $band_counts['12M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>10M</td>
                    <td><?php echo $band_counts['10M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>6M</td>
                    <td><?php echo $band_counts['6M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>2M</td>
                    <td><?php echo $band_counts['2M'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>70CM</td>
                    <td><?php echo $band_counts['70CM'] ?? 0; ?></td>
                </tr>

            </table>
            <br>
            <h2>NAČINI DELA</h2>
            <table>
                <tr>
                    <th>NAČIN DELA</th>
                    <th>ŠTEVILO ZVEZ</th>
                </tr>
                <tr>
                    <td>SSB</td>
                    <td><?php echo $mode_counts['SSB'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>USB</td>
                    <td><?php echo $mode_counts['USB'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>LSB</td>
                    <td><?php echo $mode_counts['LSB'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>CW</td>
                    <td><?php echo $mode_counts['CW'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>AM</td>
                    <td><?php echo $mode_counts['AM'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>FM</td>
                    <td><?php echo $mode_counts['FM'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>FT8</td>
                    <td><?php echo $mode_counts['FT8'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>FT4</td>
                    <td><?php echo $mode_counts['FT4'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>RTTY</td>
                    <td><?php echo $mode_counts['RTTY'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>PSK31</td>
                    <td><?php echo $mode_counts['PSK31'] ?? 0; ?></td>
                </tr>
                <tr>
                    <td>DIGI</td>
                    <td><?php echo $mode_counts['DIGI'] ?? 0; ?></td>
                </tr>
            </table>

 
            
        </div>

        <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA DOMAČO STRAN</div></a>
    </div>

    <footer>
        <p>© 2026 <a href="https://s59ekl.si" target="_blank">S59EKL</a></p>
    </footer>

</body>
</html>










