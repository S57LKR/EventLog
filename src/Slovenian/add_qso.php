<?php
    //error_reporting(0);
    //ini_set('display_errors', 0);
    session_start();
    require_once("db.php");
    
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
    
    if(!isset($_SESSION['username']) || $rights['usl'] != "1"){
        echo "You don't have permition to accsess that page.";
        ?>
            <a href="index.php" target="_self">BACK TO HOMEPAGE</a>
        <?php
        exit();
    }

    //Pridobi dogodke
    $query = "SELECT * FROM `dogodki`;";
    $res = mysqli_query($con, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VPIŠI ZVEZO</title>
    <link rel="stylesheet" href="style/style.css?v=3">
</head>
<body>
    <a href="index.php" target="_self" title="HOME">
        <header>
            <h1>VPIŠI ZVEZO</h1>
        </header>
    </a>

    <?php 
        if(isset($_GET['ok'])){
            if($_GET['ok'] == "1"){
                ?>
                    <div>
                        <h3>Zveza vpisana!</h3>
                    </div>
                <?php
            }
        }
        if(isset($_GET['msg'])){
            if($_GET['msg'] == "usk"){
                ?>
                    <div>
                        <h3 style="color: red;">Nimate dovoljenja za vpis zvez za sked</h3>
                    </div>
                <?php
            }     
        }   
        if(!empty($_GET['pm'])){
            $default_mode = $_GET['pm'];
        }
        else{
            $default_mode = "none";
        }
        if(!empty($_GET['pb'])){
            $default_band = $_GET['pb'];
        }
        else{
            $default_band = "none";
        }
        if(isset($_GET['pe'])){
            $default_event = $_GET['pe'];
        }
        else{
            $default_event = "none";
        }
    ?>

    <div>
        <form action="ad_qso_s.php" method="POST">
    <input type="text" name="callsign" id="callInput" placeholder="KLICNI ZNAK" required>

    <input type="text" name="date" id="dateInput" placeholder="DD/MM/YYYY" required>
    <input type="text" name="time" id="timeInput" placeholder="HH:MM" required>

    <input type="text" name="name" id="nameInput" placeholder="IME OPERATERJA">


    <select name="band" style="padding: 5px;">
        <option value="" <?= $default_band == "none" ? "selected" : "" ?> disabled>BAND</option>
        <option value="180M" <?= $default_band == "180M" ? "selected" : "" ?>>180 M</option>
        <option value="160M" <?= $default_band == "160M" ? "selected" : "" ?>>160 M</option>
        <option value="80M" <?= $default_band == "80M" ? "selected" : "" ?>>80 M</option>
        <option value="60M" <?= $default_band == "60M" ? "selected" : "" ?>>60 M</option>
        <option value="40M" <?= $default_band == "40M" ? "selected" : "" ?>>40 M</option>
        <option value="30M" <?= $default_band == "30M" ? "selected" : "" ?>>30 M</option>
        <option value="20M" <?= $default_band == "20M" ? "selected" : "" ?>>20 M</option>
        <option value="17M" <?= $default_band == "17M" ? "selected" : "" ?>>17 M</option>
        <option value="15M" <?= $default_band == "15M" ? "selected" : "" ?>>15 M</option>
        <option value="12M" <?= $default_band == "12M" ? "selected" : "" ?>>12 M</option>
        <option value="10M" <?= $default_band == "10M" ? "selected" : "" ?>>10 M</option>
        <option value="6M" <?= $default_band == "6M" ? "selected" : "" ?>>6 M</option>
        <option value="4M" <?= $default_band == "4M" ? "selected" : "" ?>>4 M</option>
        <option value="2M" <?= $default_band == "2M" ? "selected" : "" ?>>2 M</option>
        <option value="70CM" <?= $default_band == "70CM" ? "selected" : "" ?>>70 cm</option>
        <option value="23CM" <?= $default_band == "23CM" ? "selected" : "" ?>>23 cm</option>
    </select>

    <!-- MODE: dropdown -->
    <select name="mode" style="padding:5px;">
        <option value="" <?= $default_mode == "none" ? "selected" : "" ?> disabled>MODE</option>
        <option value="SSB" <?= $default_mode == "SSB" ? "selected" : "" ?>>SSB</option>
        <option value="USB" <?= $default_mode == "USB" ? "selected" : "" ?>>USB</option>
        <option value="LSB" <?= $default_mode == "LSB" ? "selected" : "" ?>>LSB</option>
        <option value="CW" <?= $default_mode == "CW" ? "selected" : "" ?>>CW</option>
        <option value="FM" <?= $default_mode == "FM" ? "selected" : "" ?>>FM</option>
        <option value="AM" <?= $default_mode == "AM" ? "selected" : "" ?>>AM</option>
        <option value="FT8" <?= $default_mode == "FT8" ? "selected" : "" ?>>FT8</option>
        <option value="FT4" <?= $default_mode == "FT4" ? "selected" : "" ?>>FT4</option>
        <option value="RTTY" <?= $default_mode == "RTTY" ? "selected" : "" ?>>RTTY</option>
        <option value="PSK31" <?= $default_mode == "PSK31" ? "selected" : "" ?>>PSK31</option>
        <option value="DIGI" <?= $default_mode == "DIGI" ? "selected" : "" ?>>DIGI</option>
    </select>
   
    <select name="event" id="izbira" required style="padding:5px;"> 
        <option value="" selected disabled>Izberi dogodek</option><?php echo "003"; ?>  
        <?php 
            function chackArray($str, $array){
                $tmp_str = implode(", ", $array);
                return str_contains($tmp_str, $str);
            }
            $need_to_select_logs_ids = [];
            $need_to_select_logs_log_names = [];
            while($row = mysqli_fetch_assoc($res)){
                $selection_id = $row['DOGODEK_ID'];
                if($row['DOGODEK_ID'] == $default_event){
                    $default_event_stm = "selected";
                }
                else{
                    $default_event_stm = "";
                }
                $controlers = json_decode($row['EVENT_CONTROLL'] ?? '[]', true);
                if(chackArray($_SESSION['username'], $controlers)){
                    if (!is_array($need_to_select_logs_ids)) {
                        $need_to_select_logs_ids = [];
                    }
                    if (!is_array($need_to_select_logs_log_names)) {
                        $need_to_select_logs_log_names = [];
                    }
                    $need_to_select_logs_ids[] = $selection_id;
                    $need_to_select_logs_log_names[] = $row['MAIN_LOG'];
                }

                ?>
                    <option value="<?php echo $row['DOGODEK_ID']; ?>" <?php /*echo $default_log_stm;*/ ?> ><?php echo $row['IME_DOGODKA']; ?></option>
                <?php
                
            }
            
            
        ?>
    </select>


    <select name="log_name" id="log_name_sel" required style="padding: 5px; display: none;">
        <option value="" id="dif_log"></option>
        <option value="<?php echo $_SESSION['log_name'] ?>">Osebni log</option>
    </select>

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



    <div style="display:flex; align-items:center; gap:5px; margin: 0;">
        <input type="text" name="rst_s" id="rst_s" placeholder="RST S" required>
        <button type="button" class="raport_btn" onclick="setRST('rst_s', '59')">59</button>
        <button type="button" class="raport_btn" onclick="setRST('rst_s', '599')">599</button>
    </div>

    <div style="display:flex; align-items:center; gap:5px; margin: 0;">
        <input type="text" name="rst_r" id="rst_r" placeholder="RST R" required>
        <button type="button" class="raport_btn" onclick="setRST('rst_r', '59')">59</button>
        <button type="button" class="raport_btn" onclick="setRST('rst_r', '599')">599</button>
    </div>

    <input type="submit" value="VPIŠI" style="margin-top:10px;">

</form>
    </div>

            <div>
        <a href="index.php" style="text-decoration: none;" target="_self"><div class="admin_url" >NAZAJ NA DOMAČO STRAN</div></a>
    </div>


    <footer>
        <p>© 2025 <a href="https://lovro7.eu">Lovro Kočevar Ribič</a>, S57LKR</p>
    </footer>

    <?php 
        $log_name = $_SESSION['log_name'];
        $query = "SELECT DISTINCT `callsign`, `name` FROM `s59ekl_log` WHERE `name` IS NOT NULL AND `name` != '';";
        $res = mysqli_query($con, $query);
        $name_list = [];

        while ($row = mysqli_fetch_assoc($res)) {
            $name_list[] = [$row['callsign'], $row['name']];
        }
    ?>


<script>




    // ---- Auto format DATE ----
    // ---- Auto format DATE ----
    const dateInput = document.getElementById('dateInput');
    const timeInput = document.getElementById('timeInput');
    const nameInput = document.getElementById('nameInput');
    const callInput = document.getElementById('callInput');

    const name_list = <?= json_encode($name_list) ?>;

    callInput.addEventListener('input', function () {
        const oseba = name_list.find(x => x[0].toUpperCase() === callInput.value.toUpperCase());

        nameInput.value = oseba ? oseba[1] : '';
    });

    let dateUserChanged = false;
    let timeUserChanged = false;

    // ---- Auto format DATE ----
    dateInput.addEventListener('input', function () {
        dateUserChanged = true;

        let v = this.value.replace(/\D/g, '');

        if (v.length >= 5) {
            this.value =
                v.substring(0, 2) + '/' +
                v.substring(2, 4) + '/' +
                v.substring(4, 8);
        } else if (v.length >= 3) {
            this.value =
                v.substring(0, 2) + '/' +
                v.substring(2, 4);
        } else {
            this.value = v;
        }
    });


    // ---- Auto format TIME ----
    timeInput.addEventListener('input', function () {
        timeUserChanged = true;

        let v = this.value.replace(/\D/g, '');

        if (v.length >= 3) {
            this.value =
                v.substring(0, 2) + ':' +
                v.substring(2, 4);
        } else {
            this.value = v;
        }
    });


    // ---- Set current UTC date and time ----
    function updateUTC() {
        const now = new Date();

        // Datum
        if (!dateUserChanged) {
            const day = String(now.getUTCDate()).padStart(2, '0');
            const month = String(now.getUTCMonth() + 1).padStart(2, '0');
            const year = now.getUTCFullYear();

            dateInput.value =
                day + '/' + month + '/' + year;
        }

        // Čas
        if (!timeUserChanged) {
            const hours = String(now.getUTCHours()).padStart(2, '0');
            const minutes = String(now.getUTCMinutes()).padStart(2, '0');

            timeInput.value =
                hours + ':' + minutes;
        }
    }


    // Nastavi takoj ob odprtju strani
    updateUTC();

    // Posodobi vsako minuto
    setInterval(updateUTC, 10000);






    // ---- Buttons to fill RST ----
    function setRST(field, value) {
        document.getElementById(field).value = value;
    }
    </script>



</body>
</html>