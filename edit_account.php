<?php
   include_once 'country.php';
?>

<?php
	// Start session for all pages
	// Attempt MySQL server connection
	session_start();
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
		$conditions = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['conditions']));

		// error check to make sure form data has been entered
		if(!empty($first_name) && !empty($last_name) && !empty($email)){

			// begin prepare statement insert to protect against sql injection
			$query = "UPDATE users SET user_first = ?, user_last = ?, user_email = ?, penpal = ?, birthday = ?, country = ?, conditions = ? WHERE user_id = ?";
			$stmt = mysqli_stmt_init($conn);

			if (!mysqli_stmt_prepare($stmt, $query)){
				echo $message = "Failed to update.";
			} else {
				// bind placeholders to data obtained from user submitted info from POST
				mysqli_stmt_bind_param($stmt, "sssdsssd", $first_name, $last_name, $email, $penpal, $birthday, $country, $conditions, $id);
				mysqli_stmt_execute($stmt);
				
				// reload variables for display in form
				$first_name = $_POST['user_first']; 
				$last_name = $_POST['user_last'];
				$email = $_POST['user_email'];
				$penpal = $_POST['penpal'];
				$birthday = $_POST['birthday'];
				$country = $_POST['country'];
				$conditions = $_POST['conditions'];

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
		echo "<form class='form' action='edit_account.php?update=".$update."' method='post'>";
		echo "<hr/>";
		echo"<input class='input' type='hidden' name='user_id' value='{$row1['user_id']}' />";
		echo "<br />";
		echo "<label>" . "First Name:" . "</label>" . "<br />";
		echo"<input class='input' type='text' name='user_first' value='{$row1['user_first']}' />";
		echo "<font color='red'> ... required.</font><br /><br />";
		echo "<label>" . "Last Name:" . "</label>" . "<br />";
		echo"<input class='input' type='text' name='user_last' value='{$row1['user_last']}' />";
		echo "<font color='red'> ... required.</font><br /><br />";
		echo "<label>" . "Email:" . "</label>" . "<br />";
		echo "<input class='input' type='text' name='user_email' value='{$row1['user_email']}' />";
		echo "<font color='red'> ... required.</font><br /><br />";
		echo "<label>" . "Birthday:" . "</label>" . "<br />";
		echo "<input type='date' name='birthday' value='{$row1['birthday']}'><br /><br />";

		echo "<label>" . "Country:" . "</label>" . "<br />";
		echo "<select name='country' style='width:300px;'>";

		foreach($countries as $key => $value) {
		echo '<option value="'.$key.'" title="'. htmlspecialchars($value).'" name="country" >'.htmlspecialchars($value).'</option>';
		}
		echo "</select><br /><br />";

		echo "<label>" . "Primary condition:" . "</label><br/>";
		echo "
		<select name='conditions' style='width:300px;' selected='".$row1['conditions']."'>
		  <option value='Not Provided.'>SELECT OPTION</option>
		  <option value='ibsd'>IBS-D (diarrhea predominant)</option>
		  <option value='ibsc'>IBS-C (constipation predominant)</option>
		  <option value='ibsa'>IBS-A (alternating diarrhea/constipation)</option>
		  <option value='ibspi'>IBS-PI (post-infectious)</option>
		  <option value='pdvibs'>PDV-IBS (post-diverticulitis)</option>
		  <option value='bipolar'>Bipolar Disorder</option>
		  <option value='cancer'>Cancer</option>
		  <option value='chronicc'>Chronic Idiopathic Constipation</option>
		  <option value='celiac'>Celiac Disease</option>
		  <option value='fatigue'>Chronic Fatigue Syndrome</option>
		  <option value='chrones'>Crohns Disease</option>
		  <option value='depression'>Depression</option>
		  <option value='anxiety'>Anxiety</option>
		  <option value='lactose'>Lactose Intolerance</option>
		  <option value='ocd'>Obsessive Compulsive Disorder</option>
		  <option value='ptsd'>Post Traumatic Stress Disorder</option>
		  <option value='colitis'>Ulcerative Colitis</option>
		</select>";
		echo '<br><br>';

		echo "\nSelect Penpal Status: ";
		echo "
		<select name='penpal' selected='";if($row1['penpal']==1){echo 'YES';}else{echo 'NO';}echo"'>
			<option value='1'>Yes</option>
			<option value='NULL'>No</option>
		</select>
		";
		echo "<br/><br/>";

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