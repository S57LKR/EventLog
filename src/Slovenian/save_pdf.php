<?php
$targetDir = __DIR__ . "/qsl_cards/";

if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

if (!empty($_FILES["pdf"])) {
    $fileName = basename($_FILES["pdf"]["name"]);
    $fileName = preg_replace("/[^a-zA-Z0-9_\-čžšČŽŠ\.]/u", "", $fileName);

    $targetFile = $targetDir . $fileName;

    if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $targetFile)) {
        echo "PDF shranjen kot: $fileName";
    } else {
        echo "Napaka pri shranjevanju PDF!";
    }
} else {
    echo "Datoteka ni prejeta.";
}
?>
