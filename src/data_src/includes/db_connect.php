<?php
require_once "db_config.php"; 


//Create database connection
$connection = new mysqli($host, $dbUsername, $dbPassword, $database, 3307);

//Check if the connection was successful
if ($connection->connect_error) {
    die("Connection failed: ".$connection->connect_error);
}


?>