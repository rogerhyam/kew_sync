<?php

require_once('../config.php');

/*

    This script copies across the name matches 
    from the last WCVP dump then runs through
    any that don't have matches and matches them using the
    SOLR based service

    This means that queries of WCVP for import to Rhakhis come
    partially pre-matched.

*/

echo "\n-- WCVP Name Updater --\n";

// does the wcvp_last table exist
$response = $mysqli->query("SELECT  count(*) as n FROM information_schema.TABLES WHERE `table_schema` = 'kew' AND `table_name` = 'wcvp_last'");
$row = $response->fetch_assoc();
$response->close();
if($row['n'] == 1){
    echo "\nLast WCVP import exists so copying WFO IDs over from it.";
    $response = $mysqli->query("UPDATE wcvp AS w JOIN wcvp_last as l on w.plant_name_id = l.plant_name_id SET w.wfo_id = l.wfo_id WHERE l.wfo_id IS NOT NULL AND l.wfo_id != 'SKIPPED';");
    echo "\nCompleted WFO ID copy over.";
}

echo "\nWorking through blank WFO IDs to see if we can match any.";

// set up a curl handle to use in the loops
$curl = curl_init(KEW_SYNC_GRAPHQL_URI);
curl_setopt($curl, CURLOPT_USERAGENT, 'WCVP Kew Sync');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POST, 1);
curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json')); 

$count = 0;
$page = 1;
while(true){

    echo "\nStarting page $page";
    $page++;

    $response = $mysqli->query("SELECT * from `wcvp` WHERE `wfo_id` IS NULL LIMIT 1000");

    // stop if we don't find any.
    if($response->num_rows == 0) break;

  
    while($row = $response->fetch_assoc()){

        $wfo_id = null;
        $name_string = $row['taxon_name'] . ' ' . $row['taxon_authors'];

        echo "\n\t$name_string";

        $graph_query = "
            query {
                taxonNameMatch(inputString: \"$name_string\"){
                    match{
                    id
                    identifiersOther {
                        kind
                        value
                    }
                    }
                    candidates{
                    id
                    identifiersOther {
                        kind
                        value
                    }
                    }

                }
            }
        ";

        $graphql_request = (object)array("query" => $graph_query);

        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($graphql_request));

        $curl_response = curl_exec($curl);  
        $curl_error = curl_errno($curl);
        
        if(!$curl_error){
            // no error
            $match_response = json_decode($curl_response);

            $match = $match_response->data->taxonNameMatch->match;
            $candidates = $match_response->data->taxonNameMatch->candidates;

            if($match){
                // straight match so just set it
                $wfo_id = $match->id;
            }else{
                // no straight match so work through the candidates.
                if($row['ipni_id']){
                    // we can only choose between candidates if we have an ipni id
                    $ipni_id = 'urn:lsid:ipni.org:names:' . $row['ipni_id'];
                    

                    // find a candidate with the same IPNI ID and use that
                    foreach($candidates as $candidate){
                        foreach($candidate->identifiersOther as $identifier){
                            if($identifier->kind == 'ipni' && $identifier->value == $ipni_id){
                               $wfo_id = $candidate->id;
                               break; 
                            }
                        }
                        if($wfo_id) break;
                    }
                }// we have an ipni_id

            }// end looking for wfo_id

            // default to 'SKIPPED'
            if(!$wfo_id) $wfo_id = 'SKIPPED';

            echo "\t$wfo_id";

            $mysqli->query("UPDATE `wcvp` SET `wfo_id` = '{$wfo_id}' WHERE `plant_name_id` = '{$row['plant_name_id']}'");

        }else{
            // we are in error
            echo curl_error($curl);
            exit;
        }
    
        $count++;
    
    } // l

    // call it with curl

    // if we have a match write in the wfo_id

} // paging loop

// clean up
curl_close($curl);



