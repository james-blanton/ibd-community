<?php
// set session if one isn't already set
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

// check if user viewing the page is logged in
// redirect to error page if they are not
if(!isset($_SESSION['username'])){
 	header("Location:error.php");
}

// redirect user away from the page if they try to access it
// when a user id has not been obtained from the url
if (!isset($_GET['update']) || $_GET['update'] == "") {
	header("Location: error.php");
}
?>

<?php 
// file includes list of countries for user to select from
include_once 'country.php';
?>

<?php
	// Attempt MySQL server connection
	include_once "includes/dbh.inc.php";

	// typecast data obtained from url for inject protection
	$current_user = (int)$_GET['update'];

	// verify that GET is numeric
	if(is_numeric($_GET['update']) == FALSE){
		header("Location: error.php");
		exit();
	}

	// query to redirect user away if they attempt to edit an account that is not theirs
	$update_user = mysqli_query($conn, "
	SELECT 
	user_id
	FROM users
	WHERE user_id = $current_user
	");

	if(mysqli_num_rows($update_user)===0){
		header("Location: error.php");
		exit();
	}
	 
	// get the username for the account owner for as it relates to the user id obtained from the url       
	while($row = mysqli_fetch_array($update_user, MYSQLI_ASSOC)){
		$the_user = $row['user_id'];
	}

		// check user id for current user viewing the page
	if($_SESSION['user_id'] == $the_user){
		// let owner of account access edit account page for their own account
	} else if ($_SESSION['user_privilege'] == "admin" || $_SESSION['user_privilege'] == "mod"){
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
$path = "header.php";
include_once $path;
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>ACCOUNT</h2>
		<hr/>
<div class="profile-inbox">
<a href="inbox.php">View Inbox</a>
</div>

<?php
// query to obtain profile pic file name and whether it's approved for display on the forum or not
$profile_pic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $current_user");

while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
	$profilepic = $row['filename'];
	$approval = $row['approved'];
}
?>
<div class = "profile_pic">

<?php
// if the profile pic approved, then go ahead and display it to the user
// if not, then display the default profile pic file
if(isset($approval)){
	if($approval == true){
		echo '<img src="./img/user_pics/'.$profilepic.'" >';
	} else echo '<img src="./img/user_pics/default.jpg" >';
} else echo '<img src="./img/user_pics/default.jpg" >';
?>

<br/><br/><a href="edit_avatar.php?id=<?php echo $current_user ?>">Edit Profile Pic</a><br/>
<br/><i>Be aware that for the time being you can only upload a profile picture once and it can't be reverted. Choose wisely.</i><br/><br/>

<?php
// begin profile update process and query if form is submitted
if (isset($_POST['submit'])) {
	// initialize variable that will be used to provide feedback to the user
	$message = "";
		
	// sql injection / xss attack prevention by escaping input sent by form submit
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

	// error check to make sure form data has been entered for a select few variables
	// this fields are marked as 'required' to the user
	if(!empty($first_name) && !empty($last_name) && !empty($email)){

		// begin prepare statement insert to protect against sql injection
		// query
		$query = "UPDATE users SET user_first = ?, user_last = ?, user_email = ?, penpal = ?, birthday = ?, country = ?, condition1 = ?, condition2 = ?, condition3 = ?, introduction = ?  WHERE user_id = ?";
		$stmt = mysqli_stmt_init($conn);

		if (!mysqli_stmt_prepare($stmt, $query)){
			echo $message = "Failed to update.";
		} else {
			// bind placeholders to data obtained from user submitted info from POST
			// i = integer / d = double / s = string
			mysqli_stmt_bind_param($stmt, "sssissssssi", $first_name, $last_name, $email, $penpal, $birthday, $country, $condition1, $condition2, $condition3, $introduction, $id);
			mysqli_stmt_execute($stmt);
				
			// reload variables for display in form once the update is complete
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

	// this warning goes off if the user does not enter their first name, last name, or email
	} else {echo $message = "Please enter profile info.";}
	
}
?>

<?php
// if a user id for the profile that needs to be updated is obtained from the url, then display the appropriate information, otherwise redirect the user if a user_id is not obtained from the url (just a second check for good measure)
if (isset($_GET['update'])) {

	$update = $_GET['update'];
	$query1 = mysqli_query($conn, "SELECT * from users where user_id=$update");

	// post method so the data isn't shown in the url
	while ($row1 = mysqli_fetch_array($query1)) {

		?>

		<?php echo "<h2>" .$row1['username']. "</h2>"; ?>
		<form class='form' style='float:left;' action='account.php?update<?php echo "=".$update; ?>' method='post'>
		<input class='input' type='hidden' name='user_id' value='<?php echo $row1["user_id"]; ?>' />
		<br />
		<label>First Name:</label><br />
		<input class='input' type='text' name='user_first' maxlength='35' value='<?php echo $row1["user_first"]; ?>' />
		<font color='red'> ... required.</font><br /><br />
		<label>Last Name:</label><br />
		<input class='input' type='text' name='user_last' maxlength='35' value='<?php echo $row1["user_last"]; ?>' />
		<font color='red'> ... required.</font><br /><br />
		<label>Email:</label><br />
		<input class='input' type='text' name='user_email' maxlength='50' value='<?php echo $row1["user_email"]; ?>' />
		<font color='red'> ... required.</font><br /><br />
		<label>Birthday:</label><br />
		<input type='date' name='birthday' maxlength='75' value='<?php echo $row1["birthday"]; ?>' /><br /><br />

		<label>Country:</label><br />
		<select name='country' maxlength='75' style='width:300px;'>
		<option value='<?php echo $row1['country']; ?>'><?php echo $row1['country']; ?></option>
		
		<?php
		foreach($countries as $key => $value) {
		echo '<option value="'.$key.'" title="'. htmlspecialchars($value).'" name="country" >'.htmlspecialchars($value).'</option>';
		}
		echo "</select><br /><br />";
		?>

		<label>Main condition:</label><br />
		
		<select name='condition1' maxlength='75' style='width:300px;' selected="<?php echo $row1['condition1']; ?>">
		<option value='<?php echo $row1['condition1']; ?>'><?php echo $row1['condition1']; ?></option>
   		<?php include 'condition_dropdown.php'; ?>
   		</select>
		<br /><br />

		<label>Second condition:</label><br />
		
		<select name='condition2' maxlength='75' style='width:300px;' selected='<?php echo $row1['condition2']; ?>'>
		<option value='<?php echo $row1['condition2']; ?>'><?php echo $row1['condition2']; ?></option>
   		<?php include 'condition_dropdown.php'; ?>
   		</select>
		<br /><br />

		<label>Third condition:</label><br />
		
		<select name='condition3' maxlength='75' style='width:300px;' selected="<?php echo $row1['condition3']; ?>">
		<option value='<?php echo $row1['condition3']; ?>'><?php echo $row1['condition3']; ?></option>
   		<?php include 'condition_dropdown.php'; ?>
   		</select>
		<br /><br />

		Select Penpal Status:

		<select name='penpal' maxlength='75' selected='<?php if($row1['penpal']==1){echo 'YES';}else{echo 'NO';} ?>'>
			<option value='<?php if($row1['penpal']==1){echo '1';}else{echo '0';} ?>'><?php if($row1['penpal']==1){echo 'YES';}else{echo 'NO';} ?></option>
			<option value='1'>Yes</option>
			<option value='0'>No</option>
		</select>
		
		<br /><br />

		<label>Introduction (3,000 characters max):</label><br />
		<textarea type='text' name='introduction' maxlength='3000' style='width: 100%; height: 300px;' ><?php echo strip_tags(nl2br(stripcslashes($row1['introduction']))) ?></textarea><br /><br />

		<input class='submit' type='submit' name='submit' value='update' />
		</form>

		<?php
	}
} else {
	// redirect the user if a user id is not obtained from the url with superglobal GET
	header("Location: error.php");
}
?>

</div>
</section>

<?php
   $path = 'footer.php';
   include_once $path;
?>