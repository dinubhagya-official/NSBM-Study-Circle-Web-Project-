<?php
$host = "sql213.infinityfree.com";
$user = "if0_43346718";
$pass = "NSBM1234N";
$dbname = "if0_43346718_nsbmstudycycle";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Database Connection Failed: " . $conn->connect_error);
}
?>