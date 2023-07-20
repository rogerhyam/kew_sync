<?php

// fetch protologs for names - if they have them.

require_once("../config.php");

$base_uri = "https://www.ipni.org/";

$offset = 0;

while(true){


    $sql = "SELECT ip.id as id FROM kew.protologs as p right JOIN kew.ipni as ip on p.name_id = ip.id WHERE p.name_id is NULL  AND ip.top_copy_b = 't' ORDER BY ip.id LIMIT 1000 OFFSET $offset";
    echo "\n$sql\n";
    $response = $mysqli->query($sql);
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    if(count($rows) == 0) break;
    $error_count = 0;
    
    foreach($rows as $row){


        $uri = $base_uri . $row['id'];

        echo "\n$uri";

        // Create a stream
        $opts = [
            "http" => [
                "method" => "GET",
                "header" => "Accept: application/rdf+xml"
            ]
        ];

        // DOCS: https://www.php.net/manual/en/function.stream-context-create.php
        $context = stream_context_create($opts);

        if($uri == "https://www.ipni.org/urn:lsid:ipni.org:names:1014036-1") continue; // issue with this one?

        $rdf = @file_get_contents($uri, false, $context);
        if($rdf === false){
            $error_count ++;
            echo "FAILED: $uri\n";
            if($error_count > 10){
                echo "10 errors so stopping.";
                exit;
            }else{
                continue;
            }
        }

        //echo $rdf;
        $xml = simplexml_load_string($rdf);

        $citation_elements = $xml->xpath("//rdf:RDF/tn:TaxonName/tcom:publishedInCitation");
        if($citation_elements){
            $cite_uri = $mysqli->real_escape_string($citation_elements[0]);
            $mysqli->query("INSERT INTO kew.protologs (name_id, ref_url) VALUES ('{$row['id']}', '$cite_uri') ");
            echo "\tFound";
        }else{
            $mysqli->query("INSERT INTO kew.protologs (name_id, ref_url) VALUES ('{$row['id']}', NULL) ");
            echo "\t -- ";
        }

    }

    $offset = $offset + 1000;
}
