<?php

require_once('../config.php');

/*
create table kew.ipni_doi SELECT 
	id,
    taxon_scientific_name_s_lower, 
    authors_t, 
    REGEXP_SUBSTR(remarks_s_lower, 'doi:10\.[0-9]{4,9}/[^ ]*') as doi,
    remarks_s_lower
FROM 
	kew.ipni 
where
	remarks_s_lower
like '%doi:%';

*/

// get a list of all
$response = $mysqli->query("SELECT distinct(doi) FROM kew.ipni_doi where apa_citation is null;");
$rows = $response->fetch_all(MYSQLI_ASSOC);
$response->close();

foreach($rows as $row){

    $doi = $row['doi'];
    if(!$doi) continue;

    $uri = preg_replace('/^doi:/', 'https://doi.org/', $doi );

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
    $mysqli->query("UPDATE kew.ipni_doi SET response_code = $code, apa_citation = '$cite' where doi = '$doi_escaped'");
    echo $mysqli->error;
    echo "\n-----\n";
    echo "$uri\t$code\n";
    echo $response;
    echo "\n";

}

