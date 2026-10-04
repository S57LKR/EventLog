<?php 
    require_once("db.php");
    //require_once("fpdf/fpdf.php");
    require_once("src/autoload.php");
    require_once("rotation.php");
    

    class PDF extends PDF_Rotate
{
function RotatedText($x,$y,$txt,$angle)
{
    //Text rotated around its origin
    $this->Rotate($angle,$x,$y);
    $this->Text($x,$y,$txt);
    $this->Rotate(0);
}

function RotatedImage($file,$x,$y,$w,$h,$angle)
{
    //Image rotated around its upper-left corner
    $this->Rotate($angle,$x,$y);
    $this->Image($file,$x,$y,$w,$h);
    $this->Rotate(0);
}
}


    if(!isset($_POST['call']) || !isset($_POST['points']) || !isset($_POST['eid']) || !isset($_POST['n'])){
        die("Manjkajo podatki za prenos diplome!");
    }
    var_dump($_POST);
    $call = $_POST['call'];
    $points = $_POST['points'];
    $event_id = $_POST['eid'];
    $name = $_POST['n'];

    var_dump($_POST);

    $query = "SELECT * FROM `dogodki` WHERE `DOGODEK_ID` = '$event_id';";

    if ($res = mysqli_query($con, $query)) {
        $row = mysqli_fetch_assoc($res);
        // Pretvori JSON samo, če vrednost ni NULL
        $json1 = $row['DIPL_1_DATA'] !== null ? json_decode($row['DIPL_1_DATA'], true) : null;
        $json2 = $row['DIPL_2_DATA'] !== null ? json_decode($row['DIPL_2_DATA'], true) : null;
        $json3 = $row['DIPL_3_DATA'] !== null ? json_decode($row['DIPL_3_DATA'], true) : null;

        // Privzeto ni izbrane kartice
        $json = null;

        // Izberi kartico glede na število točk
        if ($json1 !== null && $points >= (int)$json1['CARD']['POINT_N']) {
            $json = $json1;
        }

        if ($json2 !== null && $points >= (int)$json2['CARD']['POINT_N']) {
            $json = $json2;
        }

        if ($json3 !== null && $points >= (int)$json3['CARD']['POINT_N']) {
            $json = $json3;
        }


        // Testni način
        $url_addition = "";
        if (isset($_POST['mode']) && $_POST['mode'] == "test") {
            $url_addition = "_TEST";
            if ($_POST['card'] == "1" && $json1 !== null) {
                $json = $json1;
            }
            if ($_POST['card'] == "2" && $json2 !== null) {
                $json = $json2;
            }
            if ($_POST['card'] == "3" && $json3 !== null) {
                $json = $json3;
            }
        }

        // Če ni primerne kartice
        if ($json === null) {
            die("Za dano število točk ni na voljo nobena diploma.");
        }

        // Podatki iz izbrane kartice
        $empty_card_name = $json['CARD']['URL'];

        $call_pos = $json['CALL'];
        $name_pos = $json['NAME'];
        $points_pos = $json['POINTS'];

        $event_name = $row['IME_DOGODKA'];

    }
    else {
        die("Dogodka ni bilo mogoče najti!");
    }

    //Get pdf size
    $size_pdf = new \setasign\Fpdi\Fpdi(); 
    $size_pdf->setSourceFile("card/" . $empty_card_name); 
    
    $page = $size_pdf->importPage(1); 
    $size = $size_pdf->getTemplateSize($page); 
    //echo "Širina: " . $size['width'] . " mm<br>"; 
    //echo "Višina: " . $size['height'] . " mm<br>"; 

    use setasign\Fpdi\Fpdi; 
    $pdf = new PDF(); 
    $pdf->setSourceFile("card/" . $empty_card_name); 

    $template = $pdf->importPage(1); //
    //$size = $pdf->getTemplateSize($template); // po rotaciji sta širina in višina zamenjani 
    $pdf->AddPage('L', [140, 90]); // template 
    $pdf->useTemplate($template, 0, 0, $size['width'], $size['height']); // tekst 

    $pdf->SetFont('Arial', 'B', 14); 
    $pdf->SetTextColor(0, 0, 0); 

    if($name_pos['x'] != "none"){
        $pdf->Text((int)$name_pos['x'] + 2, (int)$name_pos['y'] + 2, $name);  
    }

    if($call_pos['x'] != "none"){
        $pdf->Text((int)$call_pos['x'] + 2, (int)$call_pos['y'] + 2, $call);  
    }

    if($points_pos['x'] != "none"){
        $pdf->Text((int)$points_pos['x'] + 2, (int)$points_pos['y'] + 2, $points);  
    }

    $full_card_url = "/card/full_cards/Priznanje_" . $event_name . "-" . $call . $url_addition . ".pdf";


    $pdf->Output(
        'F',
        __DIR__ . $full_card_url
    );

    $full_card_url = "/s59ekl_v3/card/full_cards/Priznanje_" . $event_name . "-" . $call . $url_addition . ".pdf"; /////////////////////ODSTRANI VERZIJO

    header("Location: " . $full_card_url);
    /*


    s59ekl_v2
    |
    | full_cards
    |  | qsl kartica
    | 
    | get_qsl_card.php
*/
?>

