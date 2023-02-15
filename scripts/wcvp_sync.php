<?php

require_once('../config.php');

$downloaded_file_path = "../data/wcvp/new.zip";
$wcvp_uri = 'http://sftp.kew.org/pub/data-repositories/WCVP/wcvp.zip';

# download the new one as new.csv
echo "\nDownloading now...";

$stream = @fopen($wcvp_uri , 'r');
if($stream === false){
    //(error_get_last());
    $message = $mysqli->real_escape_string('Caught exception whilst downloading: ' .  implode( "<br/>" , error_get_last())  . "<br/>" . $wcvp_uri ."\n");
    $mysqli->query("INSERT INTO `wcvp_log` (`message`) VALUES ('$message')");
    echo "\n$message";
    exit; 
}
file_put_contents($downloaded_file_path, $stream);

// we will import it into a new table
$mysqli->query("DROP TABLE IF EXISTS `wcvp_new`;");
$create_sql = file_get_contents('../sql/wcvp.sql');
$mysqli->query($create_sql);

// now for importing it to the db.
$statement =  $mysqli->prepare("
    INSERT INTO `wcvp_new`
    (
        `plant_name_id`,
        `ipni_id`,
        `taxon_rank`,
        `taxon_status`,
        `family`,
        `genus_hybrid`,
        `genus`,
        `species_hybrid`,
        `species`,
        `infraspecific_rank`,
        `infraspecies`,
        `parenthetical_author`,
        `primary_author`,
        `publication_author`,
        `place_of_publication`,
        `volume_and_page`,
        `first_published`,
        `nomenclatural_remarks`,
        `geographic_area`,
        `lifeform_description`,
        `climate_description`,
        `taxon_name`,
        `taxon_authors`,
        `accepted_plant_name_id`,
        `basionym_plant_name_id`,
        `replaced_synonym_author`,
        `homotypic_synonym`,
        `parent_plant_name_id`,
        `powo_id`,
        `hybrid_formula`,
        `reviewed`
    ) VALUES (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    );
");

$count = 0;

$zip = new ZipArchive;
$zip->open(realpath($downloaded_file_path));
$in = $zip->getStream('wcvp_names.csv');

// drop the header
fgetcsv($in, null, '|', 0x00, 0x00);

while($line = fgetcsv($in, null, '|', 0x00, 0x00)){

     $statement->bind_param("sssssssssssssssssssssssssssssss",
        $line[0],
        $line[1],
        $line[2],
        $line[3],
        $line[4],
        $line[5],
        $line[6],
        $line[7],
        $line[8],
        $line[9],
        $line[10],
        $line[11],
        $line[12],
        $line[13],
        $line[14],
        $line[15],
        $line[16],
        $line[17],
        $line[18],
        $line[19],
        $line[20],
        $line[21],
        $line[22],
        $line[23],
        $line[24],
        $line[25],
        $line[26],
        $line[27],
        $line[28],
        $line[29],
        $line[30]
    );

    $statement->execute();

    if($statement->error){
        print_r($line);
        echo "\n" . $statement->error;
        exit;
    }

    $count++;

}
$zip->close();

/*

// switch over the tables
$response = $mysqli->query("SELECT count(*) as n FROM `wcvp`;");
$rows = $response->fetch_all(MYSQLI_ASSOC);
$old_count = $rows[0]['n'];

$response = $mysqli->query("SELECT count(*) as n FROM `wcvp_new`;");
$rows = $response->fetch_all(MYSQLI_ASSOC);
$new_count = $rows[0]['n'];

// we only switch if we have more rows
if($new_count >= $old_count){
    $mysqli->query("RENAME TABLE IF EXISTS `wcvp` to `wcvp_old`;");
    $mysqli->query("RENAME TABLE `wcvp_new` to `wcvp`;");
    $mysqli->query("INSERT INTO `wcvp_log` (`message`) VALUES ('New row count ($new_count) is >= to old count ($old_count) so database switched.')");
}else{
    $mysqli->query("INSERT INTO `wcvp_log` (`message`) VALUES ('New row count ($new_count) is less than old count ($old_count) so database NOT switched.')");
}
*/