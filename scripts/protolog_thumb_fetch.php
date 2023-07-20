<?php

// fetch protologs for names - if they have them.

require_once("../config.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$offset = 0;

while(true){

    $sql = "SELECT id, ref_url FROM kew.protologs WHERE thumb_url is NULL and ref_url like '%/page/%' ORDER BY id LIMIT 1000 OFFSET $offset";
    echo "\n$sql\n";
    $response = $mysqli->query($sql);
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    if(count($rows) == 0) break;
    $error_count = 0;
    
    foreach($rows as $row){

        $uri = trim($row['ref_url']);
        $uri = preg_replace('/^http:/', 'https:', $uri);
        $id = $row['id'];

        echo "\n$uri\n";

        $ch = curl_init();
        $timeout = 0;
        curl_setopt($ch, CURLOPT_URL, trim($uri) );
        curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HEADER, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        // Getting binary data
        $header = curl_exec($ch);
        $header = headers_to_array($header);

        if(isset($header['Location'])){
            $thumb_url = trim(preg_replace('/\/page\//', '/pagethumb/', $header['Location']));
            echo $thumb_url;
            echo "\n";
            $mysqli->query("UPDATE kew.protologs SET thumb_url = '$thumb_url' WHERE id = $id");
        }else{
            $mysqli->query("UPDATE kew.protologs SET thumb_url = '-' WHERE id = $id");
            echo "no location\n";
        }
        curl_close($ch);


 //       exit;
    }

    $offset = $offset + 1000;
}

function headers_to_array( $str )
{
    $headers = array();
    $headersTmpArray = explode( "\r\n" , $str );
    for ( $i = 0 ; $i < count( $headersTmpArray ) ; ++$i )
    {
        // we dont care about the two \r\n lines at the end of the headers
        if ( strlen( $headersTmpArray[$i] ) > 0 )
        {
            // the headers start with HTTP status codes, which do not contain a colon so we can filter them out too
            if ( strpos( $headersTmpArray[$i] , ":" ) )
            {
                $headerName = substr( $headersTmpArray[$i] , 0 , strpos( $headersTmpArray[$i] , ":" ) );
                $headerValue = substr( $headersTmpArray[$i] , strpos( $headersTmpArray[$i] , ":" )+1 );
                $headers[$headerName] = $headerValue;
            }
        }
    }
    return $headers;
}