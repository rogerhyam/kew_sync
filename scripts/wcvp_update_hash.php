<?php

/*
    This is a run-once to convert the hashes in wcvp so they only 
    include a subset of columns.
*/

require_once('../config.php');

convert_table('wcvp');
convert_table('wcvp_last');

function convert_table($table_name){

    global $mysqli;


    $offset = 0;
    while(true){

        $response = $mysqli->query("SELECT 
            plant_name_id,
            taxon_authors,
            place_of_publication,
            volume_and_page,
            first_published,
            accepted_plant_name_id,
            basionym_plant_name_id,
            parent_plant_name_id,
            nomenclatural_remarks,
            taxon_status
            FROM $table_name
            ORDER BY plant_name_id
            LIMIT 10000 OFFSET $offset");


        if($response->num_rows == 0){
            break;
        }else{
            $offset .= 10000;
        }

        $rows = $response->fetch_all(MYSQLI_ASSOC);

        
        foreach ($rows as $row) {
        
            // build the hash inelegantly.
            $hash_array = array();
            $hash_array[] = $row['taxon_authors'];
            $hash_array[] = $row['place_of_publication'];
            $hash_array[] = $row['volume_and_page'];
            $hash_array[] = $row['first_published'];
            $hash_array[] = $row['accepted_plant_name_id'];
            $hash_array[] = $row['basionym_plant_name_id'];
            $hash_array[] = $row['parent_plant_name_id'];
            $hash_array[] = $row['nomenclatural_remarks'];
            $hash_array[] = $row['taxon_status'];
            $hash = md5(implode('|', $hash_array));

            echo "\n{$row['plant_name_id']}\t$hash";

            $mysqli->query("UPDATE $table_name SET `hash` = '$hash' WHERE plant_name_id = {$row['plant_name_id']}" );

        }

    }


}