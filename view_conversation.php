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
?>

<?php
// verify that GET is numeric
if(is_numeric($_GET['id']) == FALSE){
	header("Location: error.php");
	exit();
}

?>

<?php
// include universal header file
include_once ('header.php');
?>

<?php
// This file includes the following functions:
// fetch_conversation_summary, fetch_conversation_messages, update_conversation_last_view, create_conversation, validate_conversation_id, add_conversation_message, delete_conversation. Check the file for a full explanation of each function
include_once ('includes/conversations_functions.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
			<a href="inbox.php">Back to Inbox</a><br/><br/>

			<?php 
			// add error to errors array if user tries to submit form within entering a private message
			if (isset($_POST['message'])){
				if (empty($_POST['message'])){
					$errors[] = 'You must enter a message.';
				}

				// if form is not empty, then go ahead and submit data in to database
				if (empty($errors)){
					$conversation_id = $_GET['id'];

					$message = $_POST['message'];
					add_conversation_message($conversation_id, $message);
				}
			}

			// if there's an error message, then loop through the errors array and display all errors to the user
			if(empty($errors) === false){
				foreach($errors as $error){
					echo '<div class="error_message">' . $error . '</div>';
				}
			}


			?>

			<?php
			// initialize array that will store error messages for validation conversation
			$errors = array();

			// check if the user belongs to the given conversation id
			$valid_conversation = (isset($_GET['id']) && validate_conversation_id($_GET['id']));

			if($valid_conversation === false){
				$errors[] = 'Invalid conversation ID.';
			}

			// check if any errors  occured and if they did, then loop through the errors array and display the errors to the user
			if(empty($errors) === false){
				foreach($errors as $error){
					echo '<div class="error_message">' . $error . '</div>';
				}
			}

			// only show the messages if this is a valid message for this user and if no errors occured while fetching the messages from the database
			// $valid conversation is just a boolean flag
			if($valid_conversation){
			// $messages will now become an array containing all of the private message information
			$messages = fetch_conversation_messages($_GET['id']);
			// Sets the last view time of the message to the current time for the given conversation
			update_conversation_last_view($_GET['id']);
			?>

			<form action="" method="POST">
				<div>
					<textarea name="message" rows="10" column="110"></textarea>
				</div>
				<div>
					<input type="submit" value="Send Reply">
				</div>
			</form>

			<div class="conversations">
			<?php
			foreach($messages as $message){
			?>
			<div class="message">
				<?php
				// display the full message to the user
				$date = new DateTime($message['date']);
				$new_date = $date->format('d-m-Y @ H:i:s');
				?>
				<p class="name"><b><?php echo $message['username']; ?></b> (<?php echo $new_date ?>)</p>
				<?php echo $message['text']; ?>
			</div>
			<?php
			}
			?>
			</div>

		</div>
	</div>
</section>


<?php
}

include_once 'footer.php';
?>
