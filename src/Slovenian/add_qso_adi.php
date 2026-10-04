<?php

// max upload size (v bajtih)
$maxFileSize = 5 * 1024 * 1024; // 5 MB

require_once("db.php");
session_start();

$table_name = $_SESSION['log_name'];

$rights_username = $_SESSION['username'];
$query = "SELECT `RIGHTS` FROM `users` WHERE `USERNAME` = '$rights_username'";
$res = mysqli_query($con, $query);
$row = mysqli_fetch_assoc($res);

$rights = [
    "usl" => $row['RIGHTS'][0] ?? "0",
    "utl" => $row['RIGHTS'][1] ?? "0",
    "pbu" => $row['RIGHTS'][2] ?? "0",
    "vtl" => $row['RIGHTS'][3] ?? "0",
    "udo" => $row['RIGHTS'][4] ?? "0",
    "usk" => $row['RIGHTS'][5] ?? "0"
];

if(!isset($_SESSION['username']) || $rights['usl'] != "1"){
echo "You don't have permition to accsess that page.";
?>
    <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
<?php
exit();
}

//Pridobi dogodke
$query = "SELECT * FROM `dogodki`;";
$res_evnt = mysqli_query($con, $query);






// formatiraj ADIF datum (YYYYMMDD -> DD/MM/YYYY)
function format_adif_date($d) {
    $d = preg_replace('/\D+/', '', $d);
    if (strlen($d) === 8) {
        $dd = substr($d, 6, 2);
        $mm = substr($d, 4, 2);
        $yy = substr($d, 0, 4);
        return $dd . "/" . $mm . "/" . $yy;
    }
    return $d;
}

// formatiraj čas (HHMM ali HHMMSS -> HH:MM)
function format_adif_time($t) {
    $t = preg_replace('/\D+/', '', $t);
    if (strlen($t) >= 4) {
        return substr($t, 0, 2) . ":" . substr($t, 2, 2);
    }
    return $t;
}

// ADIF parser: extrahe polja iz enega recorda
function parse_adif_record($rec) {
    $fields = [];
    if (preg_match_all('/<([A-Za-z0-9_]+):([0-9]+)(:[A-Za-z])?>([^<]*)/si', $rec, $m, PREG_SET_ORDER)) {
        foreach ($m as $match) {
            $tag = strtoupper($match[1]);
            $len = intval($match[2]);
            $val = trim(substr($match[4], 0, $len));
            if ($val !== '') $fields[$tag] = $val;
        }
    }
    return $fields;
}


// Vstavi record v DB
function insert_qso($con, $qso_num, $callsign, $date, $time, $band, $mode, $rst_s, $rst_r, $tn, $op, $ev) {
    $stmt = $con->prepare("INSERT INTO `$tn` (`qso_num`,`callsign`,`date`,`time`,`band`,`mode`,`rst_s`,`rst_r`, `OPOMBE`, `DOGODEK`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if (!$stmt) return "Prepare failed: " . $con->error;
    $stmt->bind_param("issssiiiss", $qso_num, $callsign, $date, $time, $band, $mode, $rst_s, $rst_r, $op, $ev);
    if (!$stmt->execute()) {
        $err = "Execute failed: " . $stmt->error;
        $stmt->close();
        return $err;
    }
    $stmt->close();
    return true;
}

$messages = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['adif_file'])) {
    $event = $_POST['event'];
    //spremeni table name ce je izbran sked
    //ce je sked vstavi operaterja v op
    $query = "SELECT `IME_DOGODKA` FROM `dogodki` WHERE `DOGODEK_ID` = '$event';";
    $res = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($res);
    $event_name = $row['IME_DOGODKA'];

    if(isset($_POST['user_st'])){
        if($_POST['user_st'] == "vd"){
            $log_name = $_POST['log_name'];
            $opombe = $rights_username;
        }
        else{
            $log_name = $_SESSION['log_name'];
            $opombe = "";
        }
    }





    if ($_FILES['adif_file']['error'] !== UPLOAD_ERR_OK) {
        $messages[] = "Napaka pri nalaganju datoteke.";
    } elseif ($_FILES['adif_file']['size'] > $maxFileSize) {
        $messages[] = "Datoteka je prevelika (max 5 MB).";
    } else {
        $tmp = $_FILES['adif_file']['tmp_name'];
        $origName = $_FILES['adif_file']['name'];
        $content = file_get_contents($tmp);

        if ($content === false) {
            $messages[] = "Datoteke ni mogoče prebrati.";
        } else {
            if ($con->connect_errno) {
                $messages[] = "Povezava z bazo ni uspela.";
            } else {
                $res = $con->query("SHOW TABLES LIKE '$table_name'");
                if (!$res || $res->num_rows === 0) {
                    $messages[] = "Tabela `$table_name` ne obstaja!";
                } else {

                    // preveri AUTO_INCREMENT
                    $autoInc = false;
                    $r = $con->query("SHOW COLUMNS FROM `$table_name` LIKE 'qso_num'");
                    if ($r) {
                        $row = $r->fetch_assoc();
                        if (stripos($row['Extra'], 'auto_increment') !== false) {
                            $autoInc = true;
                        }
                    }

                    $start_qso_num = 1;
                    if (!$autoInc) {
                        $r2 = $con->query("SELECT MAX(qso_num) AS mx FROM `$table_name`");
                        $ro = $r2->fetch_assoc();
                        $start_qso_num = ($ro['mx'] !== null) ? ((int)$ro['mx'] + 1) : 1;
                    }

                    // razbij na <EOR>
                    $parts = preg_split('/<eor>/i', $content);
                    $inserted = 0;
                    $skipped = 0;
                    $errors = [];

                    foreach ($parts as $part) {
                        $part = trim($part);
                        if ($part === '') continue;

                        $fields = parse_adif_record($part);

                        $callsign = $fields['CALL'] ?? ($fields['CALLSIGN'] ?? '');
                        $date = isset($fields['QSO_DATE']) ? format_adif_date($fields['QSO_DATE']) : '';
                        $time = isset($fields['TIME_ON']) ? format_adif_time($fields['TIME_ON']) : '';
                        $band = strtoupper($fields['BAND'] ?? '');
                        $rst_s = isset($fields['RST_SENT']) ? intval($fields['RST_SENT']) : 0;
                        $rst_r = isset($fields['RST_RCVD']) ? intval($fields['RST_RCVD']) : 0;
                        $mode = strtoupper($fields['MODE'] ?? '');
                        $op_name = $fields['NAME'] ?? '';

                        if ($callsign === '') {
                            $skipped++;
                            continue;
                        }

                        if ($autoInc) {
                            $stmt = $con->prepare("INSERT INTO `$log_name` (`callsign`,`date`,`time`,`band`,`mode`,`rst_s`,`rst_r`, `OPOMBE`, `DOGODEK`, `name`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmt->bind_param("sssssiisss", $callsign, $date, $time, $band, $mode, $rst_s, $rst_r, $opombe, $event, $op_name);
                            if (!$stmt->execute()) $errors[] = $stmt->error;
                            else $inserted++;
                            $stmt->close();

                        } else {
                            $result = insert_qso($con, $start_qso_num, $callsign, $date, $time, $band, $mode, $rst_s, $rst_r, $log_name, $opombe, $event);
                            if ($result === true) {
                                $inserted++;
                                $start_qso_num++;
                            } else {
                                $errors[] = $result;
                            }
                        }
                    }

                    $messages[] = "FILE: {$origName} — ADDED: {$inserted}, SKIPPED: {$skipped}.";
                    if (!empty($errors)) {
                        $messages[] = "ERROR: " . implode(' | ', array_slice($errors,0,10));
                    }

                }
                $con->close();
            }
        }
    }
}
?>
<!doctype html>
<html lang="sl">
<head>
    <meta charset="utf-8">
    <title>UPLOAD .adi / .adif</title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>

    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>UPLOAD .adi / .adif</h1>
        </header>
    </a>

<div  style="text-align: center;">
    <form method="post" enctype="multipart/form-data">
        <input id="adif_file" value="CHOSE .adi / .adif FILE" name="adif_file" type="file" accept=".adi,.adif,text/plain" required>
        <br>
        <br>

        <select name="event" id="izbira" required style="padding:5px;"> 
            <option value="" selected disabled>Izberi dogodek</option><?php echo "003"; ?>  
            <?php 
                $user_st = "";
                $query = "SELECT * FROM `dogodki`;";
                $res = mysqli_query($con, $query);
                function chackArray($str, $array){
                    $tmp_str = implode(", ", $array);
                    return str_contains($tmp_str, $str);
                }
                $need_to_select_logs_ids = [];
                $need_to_select_logs_log_names = [];
                while($row = mysqli_fetch_assoc($res)){
                    $selection_id = $row['DOGODEK_ID'];
                    $controlers = json_decode($row['EVENT_CONTROLL'] ?? '[]', true);
                    if(chackArray($_SESSION['username'], $controlers)){
                        $user_st = "vd";
                        if (!is_array($need_to_select_logs_ids)) {
                            $need_to_select_logs_ids = [];
                        }
                        if (!is_array($need_to_select_logs_log_names)) {
                            $need_to_select_logs_log_names = [];
                        }
                        $need_to_select_logs_ids[] = $selection_id;
                        $need_to_select_logs_log_names[] = $row['MAIN_LOG'];
                    }
                    else{
                        $user_st = "us";
                    }

                    ?>
                        <option value="<?php echo $row['DOGODEK_ID']; ?>" <?php /*echo $default_log_stm;*/ ?> ><?php echo $row['IME_DOGODKA']; ?></option>
                    <?php
                    
                }   
            ?>
        </select>
        <input type="hidden" name="user_st" value="<?php echo $user_st; ?>">
        <select name="log_name" id="log_name_sel" style="padding: 5px; display: none;">
            <option value="" id="dif_log"></option>
            <option value="<?php echo $_SESSION['log_name'] ?>">Osebni log</option>
        </select>

        <input type="submit" value="NALOŽI DTOTEKO">
    </form>
</div>

<script>
    const ecnts = <?php echo json_encode($need_to_select_logs_ids ?? []); ?>;
    const log_name_list = <?php echo json_encode($need_to_select_logs_log_names ?? []); ?>;

    const event_sel = document.getElementById("izbira");
    const log_sel = document.getElementById("log_name_sel");
    const diff_log = document.getElementById("dif_log");

    event_sel.addEventListener("change", function () {
        const index = ecnts.indexOf(this.value);
        console.log(this.value);

        if (index !== -1) {
            log_sel.style.display = "block";

            // Ime loga z istega indeksa kot ID dogodka
            diff_log.value = log_name_list[index];
            diff_log.textContent = log_name_list[index];
        } else {
            log_sel.style.display = "none";
        }
    });
</script>

<?php if (!empty($messages)): ?>
<div>
  <?php foreach ($messages as $m): ?>
    <div><?php echo htmlspecialchars($m); ?></div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >BACK TO HOMEPAGE</div></a>
    </div>
    
<footer>
        <p>© 2025 <a href="https://lovro7.eu">Lovro Kočevar Ribič</a>, S57LKR</p>
    </footer>

</body>
</html>
