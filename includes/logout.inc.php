<?php
// you must make sure a session has been set before you destroy it
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}
// destroy all session variables
session_destroy(); 
// redirect the user to index page with successful logout message at the top of the page
header('Location: ../index.php?login=out');
exit();
?>