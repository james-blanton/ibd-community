<?php
// set session if one isn't already set
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

// check if user is logged in
// redirect to error page if they are not
if(!isset($_SESSION['username'])){
 	header("Location:error.php");
 	exit();
}

// check if logged in user is an admin
// redirect to error page if they are not
if($_SESSION['user_privilege'] != "admin"){
 	header("Location:error.php");
 	exit();
}
?>

<?php
// include universal header file
   $path = "header.php";
   include_once $path;
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>ADMIN CONTROL PANEL</h2>
		<hr/>
		• <a href="approve_pics.php">Approve profile pics</a>

	</div>
</section>


<?php
   $path = 'footer.php';
   include_once $path;
?>