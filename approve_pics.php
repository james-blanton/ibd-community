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
		<h2>ADMIN APPROVE PROFILE PICS</h2>
		<hr/>
		<a href="admin.php" style="float:left;">Return to Admin CP</a><br/><br/>
<?php
// block for profile pic
$profile_pic = mysqli_query($conn, "SELECT 
	a.id,
	a.filename,
	a.user_id,
	a.approved,
	a.upload_date,
	b.user_id,
	b.username
	FROM profile_pics a, users b 
	WHERE a.user_id = b.user_id
	AND a.approved = 0
	ORDER BY a.upload_date ASC");
        
while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
	$resultset[] = $row;
}

if(!empty($resultset)){
	foreach($resultset as $row){
		$approval = "";
		$user_id = $row['user_id'];
		$username = $row['username'];
		$approval = $row['approved'];
		$filename = $row['filename'];
		$upload_date = $row['upload_date'];
		echo "<div id = 'pic_approval'>";
		echo "<b><a href = 'forum/profile.php?id=".$user_id."' />".$username."</a></b>";
		echo "<br/>".$upload_date;
		echo '<br/><br/><img src="./img/user_pics/'.$filename.'" ><br/><br/>';

		echo "<form class='form' style='float:left;' action='approve_pics.php' method='post'>";
		echo"<input class='input' type='hidden' name='user_id' value='{$row['user_id']}' />";
		echo "\nApproved: ";
			echo "
			<select name='approval'>
				<option value='' selected disabled hidden>";if($approval==1){echo 'YES';}else{echo 'NO';} echo "</option>
				<option value='1'>YES</option>
				<option value='NULL'>NO</option>
			</select>
			";
			
		echo "<input class='submit' type='submit' name='submit' value='update' />";
		echo "</form><br/><br/>";
		echo "</div>";
	}
}
?>

<?php
// Attempt MySQL server connection
include_once "includes/dbh.inc.php";

// submit update query if form is submit
if (isset($_POST['submit'])) {

	// initialize variable for feedback to use
	$message = "";
		
	// injection protection escape input
	$id = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['user_id']));
	$approved = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['approval']));

		// begin prepare statement insert to protect against sql injection
		$query = "UPDATE profile_pics SET approved = ?  WHERE user_id = ?";
		$stmt = mysqli_stmt_init($conn);

		if (!mysqli_stmt_prepare($stmt, $query)){
			echo $message = "Failed to update.";
		} else {
			// bind placeholders to data obtained from user submitted info from POST
			mysqli_stmt_bind_param($stmt, "dd", $approved, $id);
			mysqli_stmt_execute($stmt);
				
			// reload variables for display in form
			$approved = $_POST['approval'];
		}
}
?>

<?php
/*
while($row = mysqli_fetch_array($result_threads, MYSQLI_ASSOC)){
}
*/
?>

</div>
</section>


<?php
   $path = 'footer.php';
   include_once $path;
?>