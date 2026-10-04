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

    <title>UREDI DIPLOMO</title>

    <link rel="stylesheet" href="../style/style.css?v=3">
</head>

<body>

<a href="../index.php" target="_self" title="HOME">
    <header>
        <h1>UREDI DIPLOMO</h1>
    </header>
</a>

<?php

    if(!isset($_POST['eid'])){
        ?>
            <div>
                <h3 style="color: red;">Niste izbrali dogodka!</h3>
            </div>
        <?php

        exit();
    }

    $event_id = $_POST['eid'];
    $min_points = $_POST['min_p'];
    $card_n = $_POST['card_n'];
    $card_name_title = $_POST['card_name'];

    $empty_card_file = $_FILES['empty_card']['tmp_name'];
    $empty_card_name = $_FILES['empty_card']['name'];

    $query = "SELECT * FROM `dogodki` WHERE `DOGODEK_ID` = '$event_id'";

    if(!($res = mysqli_query($con, $query))){
        ?>
            <div>
                <h3 style="color: red;">
                    Napaka pri pridobivanju podatkov o dogodku
                </h3>
            </div>
        <?php

        exit();
    }

    $row = mysqli_fetch_assoc($res);

    $event_name = $row['IME_DOGODKA'];

    // Shrani kartico
    if(!move_uploaded_file(
        $empty_card_file,
        "empty_cards/" . $empty_card_name
    )){
        ?>
            <div>
                <h3 style="color: red;">
                    Napaka pri shranjevanju kartice!
                </h3>
            </div>
        <?php

        exit();
    }

?>

<div>
    <label for="display_call">Prikaži Klicni znak</label>
    <input type="checkbox" id="display_call" checked>
    <br>

    <label for="display_name">Prikaži Ime in Priimek</label>
    <input type="checkbox" id="display_name" checked>
    <br>

    <label for="display_points">Prikaži Točke</label>
    <input type="checkbox" id="display_points" checked>
    <br>
</div>

<div id="canvas-container">

    <canvas id="background"></canvas>

    <div id="call" class="movable">
        S59EKL
    </div>

    <div id="name" class="movable">
        Ime in Priimek
    </div>

    <div id="point" class="movable">
        10
    </div>

</div>


<p>
    <b>Klicni znak:</b>
    X: <span id="posX_c">50</span>
    Y: <span id="posY_c">50</span>
</p>

<p>
    <b>Ime:</b>
    X: <span id="posX_n">50</span>
    Y: <span id="posY_n">100</span>
</p>

<p>
    <b>Točke:</b>
    X: <span id="posX_p">50</span>
    Y: <span id="posY_p">150</span>
</p>



<div>
    <form action="save_card.php" method="post">
        <input type="hidden" name="txt" id="txt" value="">
        <input type="hidden" name="eid" value="<?php echo $event_id; ?>">
        <input type="hidden" name="cardn" value="<?php echo $card_n; ?>">
        <input type="submit" id="text_pos" value="SHRANI">
    </form>
</div>


<script type="module">

import * as pdfjsLib from "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.min.mjs";
pdfjsLib.GlobalWorkerOptions.workerSrc = "https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.4.168/pdf.worker.min.mjs";

const pdfUrl = "<?php echo "empty_cards/" . $empty_card_name ?>";

const canvas = document.getElementById("background");

const ctx = canvas.getContext("2d");

const pdf = await pdfjsLib.getDocument(pdfUrl).promise;

const page = await pdf.getPage(1);

const scale = 2;

const viewport = page.getViewport({scale: scale});

canvas.width = viewport.width;

canvas.height = viewport.height;

await page.render({
    canvasContext: ctx,
    viewport: viewport
}).promise;


// --------------------------------------------------
// CHECKBOXI
// --------------------------------------------------

const display_call_btn = document.getElementById("display_call");

const display_name_btn = document.getElementById("display_name");

const display_points_btn = document.getElementById("display_points");


// --------------------------------------------------
// CONTAINER
// --------------------------------------------------

const container = document.getElementById("canvas-container");


// --------------------------------------------------
// ELEMENTI
// --------------------------------------------------

const elements = {
    call: {
        element:
            document.getElementById("call"),
        x:
            document.getElementById("posX_c"),
        y:
            document.getElementById("posY_c")
    },
    name: {
        element:
            document.getElementById("name"),
        x:
            document.getElementById("posX_n"),
        y:
            document.getElementById("posY_n")
    },
    point: {
        element:
            document.getElementById("point"),
        x:
            document.getElementById("posX_p"),
        y:
            document.getElementById("posY_p")
    }
};


// --------------------------------------------------
// CHECKBOX -> PRIKAZ ELEMENTA
// --------------------------------------------------

display_call_btn.addEventListener("change", function() {
        elements.call.element.style.display = this.checked ? "block" : "none";
    }
);

display_name_btn.addEventListener("change", function() {
        elements.name.element.style.display = this.checked ? "block" : "none";
    }
);

display_points_btn.addEventListener("change", function() {
        elements.point.element.style.display = this.checked ? "block" : "none";
    }
);


// --------------------------------------------------
// DRAGGING
// --------------------------------------------------

let draggingElement = null;

let offsetX = 0;
let offsetY = 0;


Object.values(elements).forEach(
    function(item) {
        item.element.addEventListener(
            "mousedown",
            function(e) {

                // Če element ni prikazan,
                // ga ni mogoče premikati.
                if(
                    getComputedStyle(
                        item.element
                    ).display === "none"
                ){
                    return;
                }

                draggingElement = item;

                const rect = item.element.getBoundingClientRect();

                offsetX =
                    e.clientX -
                    rect.left;
                offsetY =
                    e.clientY -
                    rect.top;

                e.preventDefault();
            }
        );
    }
);


// --------------------------------------------------
// PREMIKANJE
// --------------------------------------------------

document.addEventListener(
    "mousemove",
    function(e) {

        if (!draggingElement) {
            return;
        }

        const containerRect =
            container.getBoundingClientRect();

        // Velikost containerja v mm
        const containerWidthMM = 140;
        const containerHeightMM = 90;

        // Koliko px predstavlja 1 mm
        const pxPerMM_X =
            containerRect.width / containerWidthMM;

        const pxPerMM_Y =
            containerRect.height / containerHeightMM;


        // Položaj miške v px
        let xPx =
            e.clientX -
            containerRect.left -
            offsetX;

        let yPx =
            e.clientY -
            containerRect.top -
            offsetY;


        // Pretvorba px -> mm
        let x = xPx / pxPerMM_X;

        let y = yPx / pxPerMM_Y;

        // Položaj elementa
        draggingElement.element.style.left = x + "mm";

        draggingElement.element.style.top = y + "mm";

        // Prikaz koordinat
        draggingElement.x.textContent = Math.round(x * 0.6796);

        draggingElement.y.textContent = Math.round(y * 0.6796);
    }
);


// --------------------------------------------------
// KONEC PREMIKANJA
// --------------------------------------------------

document.addEventListener(
    "mouseup",
    function() {
        draggingElement = null;
    }
);


// --------------------------------------------------
// SHRANJEVANJE POZICIJ
// --------------------------------------------------

document.getElementById(
    "text_pos"
).addEventListener(
    "click",
    function() {
        const data = {
            CALL: {
                x:
                    display_call_btn.checked ? parseInt(elements.call.x.textContent) : "none",
                y:
                    display_call_btn.checked ? parseInt(elements.call.y.textContent) : "none"
            },
            NAME: {
                x:
                    display_name_btn.checked ? parseInt(elements.name.x.textContent) : "none",
                y:
                    display_name_btn.checked ? parseInt(elements.name.y.textContent) : "none"
            },
            POINTS: {
                x:
                    display_points_btn.checked ? parseInt(elements.point.x.textContent) : "none",
                y:
                    display_points_btn.checked ? parseInt(elements.point.y.textContent) : "none"
            },
            CARD: {
                URL: "<?php echo "empty_cards/" . $empty_card_name; ?>",
                POINT_N: "<?php echo $min_points ?>",
                NAME: "<?php echo $card_name_title; ?>"
            }
        };

        const json = JSON.stringify(data);
        document.getElementById("txt").value = json;
    }
);

</script>


<style>

#canvas-container {

    position: relative;

    display: inline-block;

    line-height: 0;

}


#background {

    display: block;

    z-index: 1;

}


.movable {
    position: absolute;
    z-index: 2;
    cursor: move;
    user-select: none;
    line-height: normal;
    background: none;
    padding: 0;
}


#call {

    left: 50px;

    top: 50px;

}


#name {

    left: 50px;

    top: 100px;

    width: 300px;

}


#point {

    left: 50px;

    top: 150px;

}

</style>



<footer>

    <p>
        © 2025
        <a href="https://lovro7.eu">
            Lovro Kočevar Ribič
        </a>,
        S57LKR
    </p>

</footer>

</body>

</html>
