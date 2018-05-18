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
// include universal header file
include_once ('header.php');
?>

<?php
// This file includes the following functions:
// fetch_conversation_summary, fetch_conversation_messages, update_conversation_last_view, create_conversation, validate_conversation_id, add_conversation_message, delete_conversation. Check the file for a full explanation of each function
include_once ('includes/conversations_functions.php');

// Run function that grabs a display of each message title, delete option, last  reply date and a link to view the message and place the array data produced by the function in to a variable.
// We will loop over this variable later to display the information to the user.
$conversations = fetch_conversation_summary();
?>

<?php
// Display error if  the user attempts to delete a conversation that does not belong to them.
// Display any other errors provided by delete_conversation function (such as a failed delete query).
$errors = array();
if(isset($_GET['delete'])){
	if((validate_conversation_id($_GET['delete'])) !== 1){
		$error[] = 'Invalid conversation ID.';
	}


	if(empty($error)){
		delete_conversation($_GET['delete']);
	} else echo 'error';
}

?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
			<a href="account.php?update=<?php echo $_SESSION['user_id'];?> ">Return To Account</a><br/><br/>
			<a href="new_conversation.php">New Conversation</a><br/><br/>

			<?php foreach ($conversations as $conversation)
			{ 
				// If you are the reciever of this message and you don't have it marked as deleted (a boolean flag in the conversation_members table) , then display the conversation message.
				// Loop through the array returned by the fetch_conversation_summary function call above to get all of this data.
				if($conversation['user_type'] == 'reciever' && $conversation['receiver_delete'] == 0){
				?>

				<div class="conversations <?php if($conversation['unread_messages'] == 1) echo 'conversations-unread'; ?>">
					<?php
						$date = new DateTime($conversation['last_reply']);
						$new_date = $date->format('d-m-Y @ H:i:s');
					?>
					
						<font class = "caps">
							<a href="inbox.php?delete=<?php echo $conversation['id']; ?>">[x]</a>
							<a href="view_conversation.php?id=<?php echo $conversation['id']; ?>"><?php echo $conversation['subject']; ?></a><br/>
						</font>
						Last Reply: <?php echo $new_date ?>
				</div>

				<?php
				}

				// If you are the sender of this message and you don't have it marked as deleted (a boolean flag in the conversation_members table) , then display the conversation message.
				// Loop through the array returned by the fetch_conversation_summary function call above to get all of this data.
				if($conversation['user_type'] == 'sender' && $conversation['sender_delete'] == 0)
				{
				?>

			<div class="conversations <?php if($conversation['unread_messages'] == 1) echo 'conversations-unread'; ?>">
				<?php
					$date = new DateTime($conversation['last_reply']);
					$new_date = $date->format('d-m-Y @ H:i:s');
				?>
				
					<font class = "caps">
						<a href="inbox.php?delete=<?php echo $conversation['id']; ?>">[x]</a>
						<a href="view_conversation.php?id=<?php echo $conversation['id']; ?>"><?php echo $conversation['subject']; ?></a><br/>
					</font>
					Last Reply: <?php echo $new_date ?>
			</div>

			<?php

				}
			}
			?>
			
		</div>
	</div>
</section>


<?php
include_once 'footer.php';
?>
