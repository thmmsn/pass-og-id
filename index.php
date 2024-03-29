<?php

function date_range($first, $last, $step = '+1 day', $output_format = 'Y-m-d' ) {

    $dates = array();
    $current = strtotime($first);
    $last = strtotime($last);

    while( $current <= $last ) {

        $dates[] = date($output_format, $current);
        $current = strtotime($step, $current);
    }

    return $dates;
}

function url_get_contents ($Url) {
    if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}

// definerer datoene som skal søkes gjennom

if (!isset($_GET['dager'])) {
    $deltaDag = 7;
}
if (isset($_GET['dager'])) {
    $deltaDag = $_GET['dager'];
}

$date = date('Y-m-d');
$startDate = strtotime('today');
$endDate = strtotime('today + '. $deltaDag . ' day');
$datolisteJSON = array();
$f = date("Y-m-d", $startDate);
$s = date("Y-m-d", $endDate);

$periode = date_range($f, $s, '+1 day', $output_format = 'Y-m-d');

$bestillTime = "<br>Bestill time hos <a href='https://pass-og-id.politiet.no/timebestilling/'>Politiet</a><br>";

// Viser bare ledige tidspunkt

// PASS
// ID-KORT
//$PublicId = '8e859bd4c1752249665bf2363ea231e1678dbb7fc4decff862d9d41975a9a95a';
$PublicId = 'd1b043c75655a6756852ba9892255243c08688a071e3b58b64c892524f58d098';
$begynn_link = "https://pass-og-id.politiet.no/qmaticwebbooking/rest/schedule/branches/";
$slutt_link =";service" . "PublicId=" . $PublicId . ";customSlotLength=10";

$db = new SQLite3('politinorge.db');

// $db->exec("CREATE TABLE distrikt(id | distriktnavn | avdeling | avdeling_id

if(!isset($_GET['avdeling']) AND !isset($_GET['distrikt'])) {
    echo "<title>Politinorge | Finn ledige timer for pass og id</title>";
    echo "<h1>Politidistrikt</h1>";
    $result = $db->query('SELECT * FROM distrikt');
    while ($row = $result->fetchArray()) {
        echo "<a href=?dager=".$deltaDag."&distrikt=" . $row['distrikt_id'] . ">" . $row['distrikt_navn'] . "</a>";
        echo "<br>";
    }

/*
    echo "<br><br>";
    echo "Finn ledige timer for<br><br>";
    echo '<form action="./index.php">';
    echo '<input type="radio" name="PublicId" value="d1b043c75655a6756852ba9892255243c08688a071e3b58b64c892524f58d098">Pass eller ';
    echo '<input type="radio" name="PublicId" value="8e859bd4c1752249665bf2363ea231e1678dbb7fc4decff862d9d41975a9a95a">Nasjonalt ID-kort<br>';
    echo '<p></p>';
    echo '<input type="submit" value="Velg antall dager">';
    echo '</form>';

*/

}

if(isset($_GET['distrikt']) AND !isset($_GET['avdeling']) ) {
    $distrikt_id = $_GET['distrikt'];
    $result = $db->query("SELECT * FROM avdeling WHERE distrikt_id = $distrikt_id;");
    $distrikt_navn = $db->query("SELECT distrikt_navn FROM distrikt WHERE distrikt_id = $distrikt_id");
    $distrikt_navn = $distrikt_navn->fetchArray();
    $distrikt_navn = $distrikt_navn['distrikt_navn'];
    echo "<title>".$distrikt_navn."</title>";
    echo "<a href=?dager=".$deltaDag.">Tilbake til alle distrikt</a><br><br>";
    echo "<h2>".$distrikt_navn."</h2>";
    
    while ($row = $result->fetchArray()) {

        echo "<a href='?dager=".$deltaDag. "&distrikt=" . $distrikt_id . "&avdeling=" . $row['avdeling_id'] . "'>" . $row['avdeling_navn'] . "</a>";
        echo "<br>";

/* AVMARKER denne for a vise valg
        if ($_GET['bareledige']==0) {
            echo "<a href='?dager=".$deltaDag. "&distrikt=" . $distrikt_id . "&avdeling=" . $row['avdeling_id'] . "'>" . $row['avdeling_navn'] . "</a>";
            echo "<br>";
        }

        if ($_GET['bareledige']==1) {
            $avdeling_id = $row['avdeling_id'];
            $resultAvdeling = $db->query("SELECT * FROM avdeling WHERE avdeling_id = '$avdeling_id';");
            $urlAvdeling = $begynn_link . $avdeling_id . "/dates" . $slutt_link;
            $datesAvdeling = url_get_contents($urlAvdeling);
            $decoded_dates_avdeling = json_decode($datesAvdeling, true);

            $datolisteJSON = array();
            foreach ($decoded_dates_avdeling as $dato) {
                $datolisteJSON[] = $dato['date'];
            }

            $likeDager = array_intersect($datolisteJSON, $periode);

            if (count($likeDager) > 0) {
                echo "<a href='?dager=".$deltaDag. "&distrikt=" . $distrikt_id . "&avdeling=" . $row['avdeling_id'] . "'>" . $row['avdeling_navn'] . "</a>";
                echo "<br>";
                foreach ($likeDager as $dato) {
                    $url_tider = $begynn_link . $avdeling_id . "/dates/" . $dato . "/times" . $slutt_link;
                    $tider = url_get_contents($url_tider);
                    $decoded_tider = json_decode($tider, true);

                    echo "<a href=?dager=".$deltaDag."&avdeling=" . $avdeling_id . "&dato=" . $dato . ">" . $dato . "</a><br>";
                    echo "<b>".$dato."</b><br>";

                    // viser tidspunktene i tabellen
                    
                    foreach ($decoded_tider as $tid) {
                        $klokkeslett = $tid['time'];
                        echo $klokkeslett. "<br>";
                    }
                    
                }
            }
        }
*/
    }
}

if(isset($_GET['avdeling']) AND !isset($_GET['dato'])) {

    $avdeling_id = $_GET['avdeling'];
    $result = $db->query("SELECT * FROM avdeling WHERE avdeling_id = '$avdeling_id';");
    $result = $result->fetchArray();
    $avdeling_id = $result['avdeling_id'];
    $avdeling_navn = $result['avdeling_navn'];
    $distrikt_id = $result['distrikt_id'];
    $distrikt_navn = $result['distrikt_navn'];
    echo "<title>".$distrikt_navn." | ".$avdeling_navn."</title>";
    echo "<h2><a href=?dager=".$deltaDag."&distrikt=" . $distrikt_id . ">" . $distrikt_navn . "</a></h2>";
    echo "<h3>". $avdeling_navn ."</h3>";

    $url = $begynn_link . $avdeling_id . "/dates" . $slutt_link;
    $dates = url_get_contents($url);
    $decoded_dates = json_decode($dates, true);
    $datolisteJSON = array();
    foreach ($decoded_dates as $dato) {
        $datolisteJSON[] = $dato['date'];
    }

    $likeDager = array_intersect($datolisteJSON, $periode);


    if (count($likeDager) > 0) {
        foreach ($likeDager as $dato) {
            $url_tider = $begynn_link . $avdeling_id . "/dates/" . $dato . "/times" . $slutt_link;
            $tider = url_get_contents($url_tider);
            $decoded_tider = json_decode($tider, true);

            // echo "<a href=?dager=".$deltaDag."&avdeling=" . $avdeling_id . "&dato=" . $dato . ">" . $dato . "</a><br>";
            echo "<b>".$dato."</b><br>";

            // viser tidspunktene i tabellen
            
            foreach ($decoded_tider as $tid) {
                $klokkeslett = $tid['time'];
                echo $klokkeslett. "<br>";
            }
            
        }

    }
    elseif (count($likeDager)==0) {
        echo "Ingen ledige timer kommende ". $deltaDag . " dager<br>";
        echo "<br><br>";
        echo "Utvid søket til <br>";
        echo '<form action="./index.php">';
        echo '<input type="radio" name="dager" value="7">7 ';
        echo '<input type="radio" name="dager" value="30">30 eller ';
        echo '<input type="radio" name="dager" value="90">90';
        echo '<input type="submit" value="Dager">';
        echo '</form>';
    }

}

/*
if(isset($_GET['avdeling']) AND isset($_GET['dato'])) {
    $dato = $_GET['dato'];
    $avdeling_id = $_GET['avdeling'];
    $url = $begynn_link . $avdeling_id . "/dates/" . $dato . "/times" . $slutt_link;
    $tider = url_get_contents($url);
    $decoded_tider = json_decode($tider, true);

    echo "<a href=?dager=".$deltaDag."&avdeling=" . $avdeling_id . ">" . "Tilbake til avdeling" . "</a><br>";
    echo "<h3>".$dato."</h3>";

    foreach ($decoded_tider as $key) {
        $tid = $key['time'];
        echo $tid. "<br>";
    }

    echo $bestillTime;
}
*/

echo $bestillTime;

?>






