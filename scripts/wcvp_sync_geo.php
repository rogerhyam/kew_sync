<?php

require_once('../config.php');

$downloaded_file_path = "../data/wcvp/wcvp_geo.zip";

$wcvp_uri = KEW_SYNC_WCVP_GEO_URI;

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
$mysqli->query("DROP TABLE IF EXISTS `wcvp_geo`;");
echo "\ncreating table";
$create_sql = file_get_contents('../sql/wcvp_geo.sql');
$mysqli->query($create_sql);
echo "\ntable created";

/* coreid|locality|establishmentmeans|locationid|occurrencestatus|threatstatus */

// now for importing it to the db.
$statement =  $mysqli->prepare("
    INSERT INTO `wcvp_geo`
    (    
        `plant_name_id`,
        `locality`,
        `establishmentmeans`,
        `locationid`,
        `occurrencestatus`,
        `threatstatus`
    ) VALUES (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    );
");

$count = 0;

echo "\nOpening zip at $downloaded_file_path";

$zip = new ZipArchive;
$zip->open(realpath($downloaded_file_path));
$in = $zip->getStream('wcvp_distribution.csv');

// drop the header
echo "\nDropping header";
fgetcsv($in, null, '|', 0x00, 0x00);

echo "\nProcessing lines";
while($line = fgetcsv($in, null, '|', 0x00, 0x00)){
    
    $wfo_id = null;

     $statement->bind_param("isssss",
        $line[0],
        $line[1],
        $line[2],
        $line[3],
        $line[4],
        $line[5]
    );

    $statement->execute();

    if($statement->error){
        print_r($line);
        echo "\n" . $statement->error;
        exit;
    }

    $count++;

    // display count
    if($count % 10000 == 0){    
        echo "\n\t" . number_format($count, 0);
        //break; // debug
    } 

}
$zip->close();

echo "\nImport complete\n";