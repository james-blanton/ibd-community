<?php
// database connection file, currently set for local machine
$dbServername= "localhost"; // database host address
$db_username= "root"; // database login username
$db_password= ""; // database password
$dbname= "ibd-community"; // database name

$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

?>
