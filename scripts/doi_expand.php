<?php

require_once('../config.php');

// get a list of all
$response = $mysqli->query("SELECT distinct(doi) FROM kew.rod_dois where citation_full is null;");
$rows = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

foreach($rows as $row){

    $doi = $row['doi'];
    if(!$doi) continue;

    $uri = $doi; // no conversion needed here

    // curl -LH "Accept: text/x-bibliography; style=apa" https://doi.org/10.9735/0976-9889.5.1.35-38
    $ch = curl_init($uri);

    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
    curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        "Accept: text/x-bibliography; style=apa"
    ));

    //curl_setopt($ch, CURLOPT_HEADER, 1);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);
    $cite = $mysqli->real_escape_string($response);
    $doi_escaped = $mysqli->real_escape_string($doi);
    $mysqli->query("UPDATE kew.rod_dois SET response_code = $code, citation_full = '$cite' where doi = '$doi_escaped'");
    echo $mysqli->error;
    echo "\n-----\n";
    echo "$uri\t$code\n";
    echo $response;
    echo "\n";

}

