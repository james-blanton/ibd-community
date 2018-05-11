<?php
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:../error.php");
}
?>

<?php
   include_once 'country.php';
?>

<?php
	// Attempt MySQL server connection
	include_once "includes/dbh.inc.php";

	$current_user = $_GET['update'];
	// redirect user away if they attempt to edit an account that is not theirs
	$update_user = mysqli_query($conn, "
	SELECT 
	user_id
	FROM users
	WHERE user_id = $current_user
	");
	// run query
	//$the_post_owner = mysqli_query($conn, $post_owner);
	 
	// get the username for the thread owner        
	while($row = mysqli_fetch_array($update_user, MYSQLI_ASSOC)){
		$the_user = $row['user_id'];
	}

	if($_SESSION['user_id'] != $the_user){
		$path = "index.php";
	 	header("Location: $path");
	}

?>

<?php
   $path = "header.php";
   include_once $path;
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>EDIT ACCOUNT</h2>
		<hr/>
<?php
// block for profile pic
$profile_pic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $current_user");
        
while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
	$profilepic = $row['filename'];
	$approval = $row['approved'];
}
?>
<div class = "profile_pic">

<?php
if(isset($approval)){
	if($approval == true){
		echo '<img src="./img/user_pics/'.$profilepic.'" >';
	} else echo '<img src="./img/user_pics/default.jpg" >';
} else echo '<img src="./img/user_pics/default.jpg" >';
?>

<br/><br/><a href="edit_avatar.php?id=<?php echo $current_user ?>">Edit Profile Pic</a><br/>
<br/><i>Be aware that for the time being you can only upload a profile picture once and it can't be reverted. Choose wisely.</i><br/><br/>

<?php
// submit update query if form is submit
if (isset($_POST['submit'])) {
	// includes error check in case form is empty
	if (isset($_POST['submit'])) {
		// initialize variable for feedback to use
		$message = "";
		
		// injection protection escape input
		$id = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['user_id']));
		$first_name = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['user_first']));
		$last_name = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['user_last']));
		$email = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['user_email']));
		$penpal = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['penpal']));
		$birthday = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['birthday']));
		$country = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['country']));
		$condition1 = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['condition1']));
		$condition2 = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['condition2']));
		$condition3 = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['condition3']));
		$introduction = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['introduction']));

		// error check to make sure form data has been entered
		if(!empty($first_name) && !empty($last_name) && !empty($email)){

			// begin prepare statement insert to protect against sql injection
			$query = "UPDATE users SET user_first = ?, user_last = ?, user_email = ?, penpal = ?, birthday = ?, country = ?, condition1 = ?, condition2 = ?, condition3 = ?, introduction = ?  WHERE user_id = ?";
			$stmt = mysqli_stmt_init($conn);

			if (!mysqli_stmt_prepare($stmt, $query)){
				echo $message = "Failed to update.";
			} else {
				// bind placeholders to data obtained from user submitted info from POST
				mysqli_stmt_bind_param($stmt, "sssdssssssd", $first_name, $last_name, $email, $penpal, $birthday, $country, $condition1, $condition2, $condition3, $introduction, $id);
				mysqli_stmt_execute($stmt);
				
				// reload variables for display in form
				$first_name = $_POST['user_first']; 
				$last_name = $_POST['user_last'];
				$email = $_POST['user_email'];
				$penpal = $_POST['penpal'];
				$birthday = $_POST['birthday'];
				$country = $_POST['country'];
				$condition1 = $_POST['condition1'];
				$condition2 = $_POST['condition2'];
				$condition3 = $_POST['condition3'];
				$introduction = $_POST['introduction'];

				echo $message = "Update successful.";
			}

		} else {echo $message = "Please enter profile info.";}

	}
}
?>

<?php

if (isset($_GET['update'])) {

	$update = $_GET['update'];
	$query1 = mysqli_query($conn, "SELECT * from users where user_id=$update");

	// post method so the data isn't shown in the url
	while ($row1 = mysqli_fetch_array($query1)) {
		echo "<form class='form' style='float:left;' action='edit_account.php?update=".$update."' method='post'>";
		echo"<input class='input' type='hidden' name='user_id' value='{$row1['user_id']}' />";
		echo "<br />";
		echo "<label>" . "First Name:" . "</label>" . "<br />";
		echo"<input class='input' type='text' name='user_first' maxlength='35' value='{$row1['user_first']}' />";
		echo "<font color='red'> ... required.</font><br /><br />";
		echo "<label>" . "Last Name:" . "</label>" . "<br />";
		echo"<input class='input' type='text' name='user_last' maxlength='35' value='{$row1['user_last']}' />";
		echo "<font color='red'> ... required.</font><br /><br />";
		echo "<label>" . "Email:" . "</label>" . "<br />";
		echo "<input class='input' type='text' name='user_email' maxlength='50' value='{$row1['user_email']}' />";
		echo "<font color='red'> ... required.</font><br /><br />";
		echo "<label>" . "Birthday:" . "</label>" . "<br />";
		echo "<input type='date' name='birthday' maxlength='75' value='{$row1['birthday']}'><br /><br />";

		echo "<label>" . "Country:" . "</label>" . "<br />";
		echo "<select name='country' maxlength='75' style='width:300px;'>";

		foreach($countries as $key => $value) {
		echo '<option value="'.$key.'" title="'. htmlspecialchars($value).'" name="country" >'.htmlspecialchars($value).'</option>';
		}
		echo "</select><br /><br />";

		echo "<label>" . "Primary condition:" . "</label><br/>";
		echo "
		<select name='condition1' maxlength='75' style='width:300px;' selected='".$row1['condition1']."'>";
   		include 'condition_dropdown.php';
   		echo "</select>";
		echo '<br><br>';

		echo "<label>" . "Second condition:" . "</label><br/>";
		echo "
		<select name='condition2' maxlength='75' style='width:300px;' selected='".$row1['condition2']."'>";
   		include 'condition_dropdown.php';
   		echo "</select>";
		echo '<br><br>';

		echo "<label>" . "Third condition:" . "</label><br/>";
		echo "
		<select name='condition3' maxlength='75' style='width:300px;' selected='".$row1['condition3']."'>";
   		include 'condition_dropdown.php';
   		echo "</select>";
		echo '<br><br>';

		echo "\nSelect Penpal Status: ";
		echo "
		<select name='penpal' maxlength='75' selected='";if($row1['penpal']==1){echo 'YES';}else{echo 'NO';}echo"'>
			<option value='1'>Yes</option>
			<option value='NULL'>No</option>
		</select>
		";
		echo "<br/><br/>";

		echo "<label>" . "Introduction (3,000 characters max):" . "</label>" . "<br />";
		echo "<textarea type='text' name='introduction' maxlength='3000'  style='width: 100%; height: 300px;'>".strip_tags(nl2br(stripcslashes($row1['introduction'])))."</textarea><br /><br />";

		echo "<input class='submit' type='submit' name='submit' value='update' />";
		echo "</form>";
	}
}
?>

</div>
</section>


<?php
   $path = 'footer.php';
   include_once $path;
?>