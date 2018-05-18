<?php
if (session_status() == PHP_SESSION_NONE) {
	session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:error.php");
}
?>

<?php
include_once ('header.php');
?>

<?php
include_once ('includes/conversations_functions.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
			<a href="inbox.php">Back to Inbox</a><br/><br/>

			<?php 
			if (isset($_POST['message'])){
				if (empty($_POST['message'])){
					$errors[] = 'You must enter a message.';
				}

				if (empty($errors)){
					$conversation_id = $_GET['id'];
					$message = $_POST['message'];
					add_conversation_message($conversation_id, $message);
				}
			}

			if(empty($errors) === false){
				foreach($errors as $error){
					echo '<div class="error_message">' . $error . '</div>';
				}
			}


			?>

			<?php
			$errors = array();

			$valid_conversation = (isset($_GET['id']) && validate_conversation_id($_GET['id']));

			if($valid_conversation === false){
				$errors[] = 'Invalid conversation ID.';
			}

			// check form submit
			if(empty($errors) === false){
				foreach($errors as $error){
					echo '<div class="error_message">' . $error . '</div>';
				}
			}

			// only show the messages if this is a valid message for this user
			if($valid_conversation){

			$messages = fetch_conversation_messages($_GET['id']);
			update_conversation_last_view($_GET['id']);

			// right now you can only view messages you have recieved, you can't view messages that you have sent.
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
