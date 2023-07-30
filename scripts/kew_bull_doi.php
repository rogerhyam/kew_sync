<?php

/*

    A run-once script to add DOI's and citations to a list of Kew Bull. 
    micro citations that lack them

*/

require_once('../config.php');

echo "\nDOIs for Kew Bull.\n";

//generate_articles_csv();
// imported into db

//work_out_dois();

expand_dois();

function expand_dois(){

    global $mysqli;

    $response = $mysqli->query("SELECT distinct(doi) FROM kew.kew_bull_doi where (length(citation_full) < 1 or citation_full is null) AND doi is not null;");
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    foreach($rows as $row){

        echo "\n{$row['doi']}";

        $uri = 'https://doi.org/' . $row['doi'];
        $full_citation = fetch_citation($uri, $row['doi']);
        echo "\n{$full_citation}";
        $full_citation = $mysqli->real_escape_string($full_citation);

        $sql = "UPDATE kew.kew_bull_doi SET citation_full = '$full_citation' WHERE `doi` = '{$row['doi']}'";
        $mysqli->query($sql);
        if($mysqli->error){
            echo $sql;
            echo "\n";
            echo $mysqli->error;
            exit;
        }

    }

}

function fetch_citation($uri, $doi){


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
    $citation = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

    // filter out things that don't look good    
    if(!$citation) return null;
    if($code == 200){

        // they send json we use the doi
        if(preg_match('/^{/', $citation)) return $doi;

        // they send HTML we use the doi
        if(preg_match('/<html/', $citation)) return $doi;

        // max length
        if(strlen($citation) > 1000) $citation = substr($citation, 0, 995) . " ...";
        
        // OK we have a string that looks good return that
        return $citation;
    
    }else{
        return null;
    }


}

function work_out_dois(){

    global $mysqli;

    // get all the not done ones
    $response = $mysqli->query("SELECT * FROM kew.kew_bull_doi where `volume` is not null and `page` is not null and `year` < 2012 and doi is null order by `volume`, `page`;");
    $rows = $response->fetch_all(MYSQLI_ASSOC);
    $response->close();

    foreach($rows as $row){

        echo "\n{$row['citation_micro']}";

        $response = $mysqli->query("SELECT * FROM kew.kew_bull_articles WHERE `volume` = {$row['volume']} order by `first`;");
        $articles = $response->fetch_all(MYSQLI_ASSOC);
        $response->close();

        // articles in this volume
        for ($i=0; $i < count($articles); $i++) { 
            
            $art = $articles[$i];
            $next = $i < count($articles) - 1 ? $articles[$i + 1] : null;

            $first_page = $art['first'];
            if($art['last']) $last_page = $art['last'];
            elseif($next) $last_page = $next['first'] -1;
            else $last_page = 1000000; // end of volume

            echo "\n\t{$art['volume']}: $first_page-$last_page";

            // we are on the start page or beyond it
            if($row['page'] >= $first_page && $row['page'] <= $last_page){
                    // must be in the page range!
                    $sql = "UPDATE kew.kew_bull_doi SET doi = '{$art['doi']}' WHERE `id` = {$row['id']}";
                    $mysqli->query($sql);
                    if($mysqli->error){
                        echo $sql;
                        echo "\n";
                        echo $mysqli->error;
                        exit;
                    }
                    echo "\n\t{$art['doi']}";
                    break;
            }
        }

    }


}



function generate_articles_csv(){
    
    $api_uri = "https://api.crossref.org/journals/0075-5974/works?rows=100&sort=published&order=asc&offset=";

    // repeatedly call the api till we get nothing back
    $offset = 0;

    $out = fopen('kew_bull_articles.csv', 'w');
    fputcsv($out, array('year', 'volume', 'first', 'last', 'doi'));
    while(true){

        $ch = curl_init($api_uri . $offset);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 300); //timeout in seconds
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //curl_setopt($ch, CURLOPT_HTTPHEADER, array( "Accept: text/x-bibliography; style=apa"));

        //curl_setopt($ch, CURLOPT_HEADER, 1);
        $response = json_decode(curl_exec($ch));
        $code = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);

        if(count($response->message->items) < 1) break;

        // work through the items returned and see if we have any names in them
        for ($i=0; $i < count($response->message->items); $i++) { 
            $item = $response->message->items[$i];

            $line = array();

            $year = $item->{'published-print'}->{'date-parts'}[0][0];
            if($year > 2011) break 2;

            $pages = $item->page;
            if(strpos($pages, "-")){
                $parts = explode('-', $pages);
                $page_first = $parts[0];
                $page_last = $parts[1];
            }else{
                $page_first = $pages;
                $page_last = '';
            }

            $line[] = $year;
            $line[] = $item->volume;
            $line[] = $page_first;
            $line[] = $page_last;
            $line[] = $item->DOI;
            print_r($line);
            fputcsv($out, $line);
        }

        $offset += 100;
    }
    fclose($out);
}



echo "\nAll done\n";