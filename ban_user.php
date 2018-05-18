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

// check if logged in user is an admin or mod
// redirect to error page if they are not
if ($_SESSION['user_privilege'] == "admin" || $_SESSION['user_privilege'] == "mod"){
	// let admin or mod access any account 
} else {
	// exclude anyone who is not the account owner, an admin, or a mod from editing the account
	// redirect user to error page if they try to account they don't own
	$path = "error.php";
	 header("Location: $path");
}
?>

<?php
// include universal header file
include_once ('header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
			<?php
			// get user id for user you wish to ban from url
			// display error message if no id is set
			if(isset($_GET['id'])){
				// Attempt MySQL server connection
				include 'includes/dbh.inc.php';

				// get user id for who you wish to ban from url
				$id = (int)$_GET['id'];
				// verify that GET is numeric
				if(is_numeric($_GET['id']) == FALSE){
					header("Location: error.php");
					exit();
				}

				// query to get users ip address from users table
				$sql_ip = "SELECT ip, username FROM users WHERE user_id = $id";
				// run query
				$result = mysqli_query($conn, $sql_ip);
				// check if any results were returned from database
				$num_rows = mysqli_num_rows($result);

				// if query returns a users ip, then throw it in to a variable along with their username
				// username is not being used right now, but it may be in the future to display
				// a full list of banned users to admins and mods
				if ($result->num_rows > 0) {
					 while($row = $result->fetch_assoc()){
					 	 $current_user_ip = $row['ip'];
					 	 $username = $row['username'];
					}
				}


				$sql_ban = "INSERT INTO banned (ip) VALUES (?)";
				$stmt = mysqli_stmt_init($conn);

				if(!mysqli_stmt_prepare($stmt, $sql_ban)){
					$message = "Failed to ban user.";
				} else {
					// bind placeholders to data obtained from database query that obtained the given users IP address
					// i = integer / d = double / s = string
					mysqli_stmt_bind_param($stmt, "s", $current_user_ip);
					mysqli_stmt_execute($stmt);
					// display message to mod / admin if user was submitted to banned user table successfully.
					echo "Banned user added to ban table.<br/>";
					echo "Navigate back to user <a href='forum/profile.php?id=".$id."'>".$username."</a>";
				}

			// alert mod / admin of the ban data insert failed
			} else { echo 'No id set.';}
			?>
</section>


<?php
include_once 'footer.php';
?>
