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
if($_SESSION['user_privilege'] != "admin" || $_SESSION['user_privilege'] != "mod"){
 	header("Location:error.php");
 	exit();
}
?>

<?php
// Include universal header file
include_once ('header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
			<?php
			if(isset($_GET['id'])){
				// Attempt MySQL server connection
				include 'includes/dbh.inc.php';

				// Get user id for who you wish to unban from url
				// typecast as integer datatype just incase (for security)
				$id = (int)$_GET['id'];

				// verify that GET is numeric
				if(is_numeric($_GET['id']) == FALSE){
					header("Location: error.php");
					exit();
				}

				// Query to get users ip from users table
				$sql_ip = "SELECT ip, username FROM users WHERE user_id = $id";
				// Run query
				$result = mysqli_query($conn, $sql_ip);
				// Count if any rows were returned by query
				$num_rows = mysqli_num_rows($result);

				// If query returns a users ip, then throw it in to a variable
				if ($result->num_rows > 0) {
					 while($row = $result->fetch_assoc()){
					 	 $current_user_ip = $row['ip'];
					 	 $username = $row['username'];
					}
				}

				// sql statement to delete a record from the banned users table
				$sql = "DELETE FROM banned WHERE ip='$current_user_ip'";

				// run query to delete provided users ip address from the banned users table
				// display whether the deletion was a success or not
				if ($conn->query($sql) === TRUE) {
				    echo "Record deleted successfully.<br/><br/>";
					echo "Navigate back to user <a href='forum/profile.php?id=".$id."'>".$username."</a>";
				} else {
				    echo "Error deleting record: " . $conn->error;
				}

				$conn->close();

			} else { echo 'No id set.';}
			?>
</section>


<?php
include_once 'footer.php';
?>
