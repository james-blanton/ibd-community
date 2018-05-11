<?php
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:error.php");
 	exit();
}

if($_SESSION['user_privilege'] != "admin"){
 	header("Location:error.php");
 	exit();
}
?>

<?php
// refresh variables after each click of the update submit button
if (isset($_POST['submit'])) {
	header("Refresh:0");
}
?>

<?php
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