<?php
// set session if one isn't already set
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

// check if user is logged in
// redirect to error page if they are not
if(!isset($_SESSION['username'])){
 	header("Location:error.php");
}
?>

<?php 
// Attempt MySQL server connection
include 'includes/dbh.inc.php';

// This file includes the following functions:
// fetch_conversation_summary, fetch_conversation_messages, update_conversation_last_view, create_conversation, validate_conversation_id, add_conversation_message, delete_conversation. Check the file for a full explanation of each function
include_once 'includes/conversations_functions.php'; 

// sanatize the username obtained from the url 
// this username value typically comes from the user clicking "send pm" on another users profile.php page
$get_username = mysqli_real_escape_string($conn, htmlentities($_GET['uname']));
?>

<?php
// check if form data for new message has been filled in
// display error message if any form has not been filled
if(isset($_POST['to'], $_POST['subject'], $_POST['body'])){
	include 'includes/dbh.inc.php';
	$errors = array();

	if(empty($_POST['to'])){
		$errors[] = 'You must enter a name.';
	} else if (preg_match("/^[a-z0-9]+(?:[ _.-][a-z0-9]+)*$/", $_POST['to']) === 0){
		// The user can manually type in any name, so make sure it's of a valid format
		// before we even bother to run a database query
		$error[] = 'The username you gave is not valid.';
	} else {
		// Query database to see if user exists and to get their unique user_id if they do exist.
		// Prepare errorr  message if they don't exist. 
		$to = $_POST['to'];
		$user_id_query = "SELECT user_id FROM users WHERE username ='$to'";
		$result = mysqli_query($conn, $user_id_query);

		$resultCheck = mysqli_num_rows($result);

		if ($resultCheck < 1){
			$error[] = 'Target user does not exist in our database.';
		}  else {
			while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
				$to_user_id = $row['user_id'];
			}
		}

	}

	if(empty($_POST['subject'])){
		$errors[] = 'You must enter a subject.';
	}

	if(empty($_POST['body'])){
		$errors[] = 'You must enter a message.';
	}

	if(empty($errors)){
		// pass data to function that will insert the message in to the database
		// if no errors occured
		create_conversation($to_user_id, $_POST['subject'], $_POST['body']);
	}

}

?>

<?php
include_once ('header.php');
include_once ('includes/conversations_functions.php');
?>

<header>
	<link rel="stylesheet" href="conversations.css">
</header>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<a href="inbox.php">Return to Inbox</a><br/><br/>

		<?php
		if (isset($errors)){
			if (empty($errors)){
					echo '<div class="message_success">Your message has been sent! <a href="inbox.php">Return to inbox.</a></div>';
			}else{
				foreach ($errors as $error){
					// loop through errors array and display each error if the message did not get passed
					// to the create_conversation function
					echo '<div class="message_error">'. $error . '</div>'; 
				}
			}
		}
		?>

		<form action="" method="POST">
			<div>
				<label for="to">To:</label><br/>
				<?php // The inputs in this form places priority on the username value provided by the the url over the value from user input ?>
				<input type="text" name="to" id="to" class="form" 
				value="<?php if(isset($get_username)){ echo $get_username; }else if(isset($_POST['to'])) echo htmlentities($_POST['to']); ?>"/></input><br/><br/>
			</div>
			<div>
				<label for="subject">Subject:</label><br/>
				<input type="text" name="subject" id="subject" class="form" value="<?php if(isset($_POST['subject'])) echo htmlentities($_POST['subject']); ?>"/><br/><br/>
			</div>
			<div>
				<textarea name="body" rows="20" cols="110" class="form" /><?php if(isset($_POST['body'])) echo htmlentities($_POST['body']); ?></textarea><br/><br/>
			</div>

			<div>
				<input type="submit" value="Send" />
			</div>
		</form>

		</div>
	</div>
</section>


<?php
include_once 'footer.php';
?>