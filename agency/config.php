<?php
$servername = "localhost";
$username = "root";
$password = "Zamokuhle31$";
$dbname = "travel_booking_db";
$connection = new mysqli($servername, $username, $password, $dbname);

if ($connection->connect_error) {
  die("Connection failed: " . $connection->connect_error);
}

?>