<?php

// configuration variables used across the scripts and applications
include_once("../../kew_secrets.php");

// ipni download is set to this which has been working for a while
define('KEW_SYNC_IPNI_URI', 'https://storage.googleapis.com/ipni-data/ipniWebName.csv.xz');

// here is the wcvp download location
define('KEW_SYNC_WCVP_URI', 'http://sftp.kew.org/pub/data-repositories/WCVP/wcvp.zip');

// where the matching URL is
define('KEW_SYNC_GRAPHQL_URI', 'https://rhakhis.rbge.info/gql.php');

// create and initialise the database connection
$mysqli = new mysqli($db_host, $db_user, $db_password, $db_database);  

// connect to the database
if ($mysqli->connect_error) {
  echo $mysqli->connect_error;
}

if (!$mysqli->set_charset("utf8")) {
  echo printf("Error loading character set utf8: %s\n", $mysqli->error);
}