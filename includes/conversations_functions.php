<?php include 'dbh.inc.php'; ?>

<?php
function fetch_conversation_summary(){
// this  function will fetch all of the message subjects and other neccessary information from proper display in the users inbox
	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$user_id = $_SESSION['user_id'];

	// verify that value is numeric
	if(is_numeric($_SESSION['user_id']) == FALSE){
		header("Location: ../error.php");
		exit();
	}


	$sql = "SELECT
		conversations.conversation_id,
		conversations.conversation_subject,
		conversation_members.from_id,
		conversation_members.user_id,
		conversation_members.receiver_delete,
		conversation_members.sender_delete,
		MAX(conversations_messages.message_date) AS conversation_last_reply,
		MAX(conversations_messages.message_date) > conversation_members.conversation_last_view AS conversation_unread
		FROM conversations
		LEFT JOIN conversations_messages ON conversations.conversation_id = conversations_messages.conversation_id
		INNER JOIN conversation_members ON conversations.conversation_id = conversation_members.conversation_id
		WHERE conversation_members.user_id = $user_id
		OR conversation_members.from_id = $user_id
		GROUP BY conversations.conversation_id
		ORDER BY conversation_last_reply DESC
	"; 

	
	$result = mysqli_query($conn, $sql);


	$conversations = array();

	while(($row = mysqli_fetch_assoc($result)) != false){
		if ($row['user_id'] == $_SESSION['user_id']){ $user_type = 'reciever';} else {$user_type = 'sender';}

		$conversations[] = array(
			'id' => $row['conversation_id'],
			'subject' => $row['conversation_subject'],
			'last_reply' => $row['conversation_last_reply'],
			'unread_messages' => ($row['conversation_unread'] ==1),
			'reciever' => $row['user_id'],
			'sender' => $row['from_id'],
			'receiver_delete'=> $row['receiver_delete'],
			'sender_delete' => $row['sender_delete'],
			'user_type' => $user_type,
		);

	}

	return $conversations;

}

function fetch_conversation_messages($conversation_id){
	$conversation_id = (int)$conversation_id;

	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$sql = "SELECT 
			conversations_messages.message_date,
			conversations_messages.message_text,
			users.username
			FROM conversations_messages
			INNER JOIN users ON conversations_messages.from_id = users.user_id
			WHERE conversations_messages.conversation_id = $conversation_id
			ORDER BY conversations_messages.message_date DESC
			";

	$result = mysqli_query($conn, $sql);

	$messages = array();

	while (($row = mysqli_fetch_assoc($result)) != false){
		$messages[] = array(
			'date' => $row['message_date'],
			'text' => $row['message_text'],
			'username' => $row['username'],
		);
	}

	return $messages;
}

function update_conversation_last_view($conversation_id){
// Sets the last view time to the current time for the given conversation
	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$conversation_id = (int)$conversation_id;

	// verify that value is numeric
	if(is_numeric($conversation_id) == FALSE){
		header("Location: ../error.php");
		exit();
	}

	date_default_timezone_set('US/Eastern');
	$current_time = date('Y-m-d H:i:s');
	$user =  $_SESSION['user_id'];

	$sql = "UPDATE conversation_members
	SET conversation_last_view = '$current_time'
	WHERE conversation_id = $conversation_id
	";

	mysqli_query($conn, $sql);
}


function create_conversation($to_user_id, $subject, $body){
// this function submits required information in to all three tables related to messages
	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$subject = mysqli_real_escape_string($conn, htmlentities($subject));
	$body = mysqli_real_escape_string($conn, htmlentities($body));

	$sql = "INSERT INTO conversations (conversation_subject) VALUES (?);";
	$stmt  = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
	} else {
		// bind the data to the placeholders in order to prepare for insert in to database
		mysqli_stmt_bind_param($stmt, "s", $subject);
		// run insert execution
		mysqli_stmt_execute($stmt);
		$conversation_id = mysqli_stmt_insert_id($stmt);
	}

	$sql = "INSERT INTO conversations_messages (conversation_id, from_id, user_id, message_date, message_text) VALUES (?, ?, ?, ?, ?);";
	$stmt  = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
	} else {
		if (session_status() == PHP_SESSION_NONE) {
			session_start();
		}
		date_default_timezone_set('US/Eastern');
		$date = date('Y-m-d H:i:s');
		$from_id = $_SESSION['user_id'];

		// bind the data to the placeholders in order to prepare for insert in to database
		mysqli_stmt_bind_param($stmt, "iiiss", $conversation_id, $from_id, $to_user_id, $date, $body);
		// run insert execution
		mysqli_stmt_execute($stmt);
	}

	$sql = "INSERT INTO conversation_members (conversation_id, from_id, user_id, conversation_last_view, receiver_delete, sender_delete) VALUES (?, ?, ?, ?, ?, ?)";
	$stmt  = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
	} else {
		date_default_timezone_set('US/Eastern');
		$date = date('Y-m-d H:i:s');
		$sender_delete = 0;
		$receiver_delete = 0;
		$from_id = $_SESSION['user_id'];

		// verify that value is numeric
		if(is_numeric($_SESSION['user_id']) == FALSE){
			header("Location: ../error.php");
			exit();
		}

		// bind the data to the placeholders in order to prepare for insert in to database
		mysqli_stmt_bind_param($stmt, "iiisii", $conversation_id, $from_id, $to_user_id, $date, $receiver_delete, $sender_delete);
		// run insert execution
		mysqli_stmt_execute($stmt);
	}

}

function validate_conversation_id($conversation_id){
// checks to make sure that the user is a member of the given conversation
	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$conversation_id = (int)$conversation_id;

	// verify that value is numeric
	if(is_numeric($conversation_id) == FALSE){
		header("Location: ../error.php");
		exit();
	}

	$user_id = $_SESSION['user_id'];
	// verify that value is numeric
	if(is_numeric($_SESSION['user_id']) == FALSE){
		header("Location: ../error.php");
		exit();
	}

	$sql = "SELECT COUNT(1)
			FROM conversation_members
			WHERE conversation_id = $conversation_id
			AND user_id = $user_id
			AND receiver_delete = 0
			";

	$result = mysqli_query($conn, $sql);

	return mysqli_num_rows($result);
}

function add_conversation_message($conversation_id, $text){
// adds a message to the given conversation
	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$conversation_id = (int)$conversation_id;
	$text = htmlspecialchars(mysqli_real_escape_string($conn, $text));
	$user_id = $_SESSION['user_id'];

	if(is_numeric($_SESSION['user_id']) == FALSE){
		header("Location: ../error.php");
		exit();
	}

	date_default_timezone_set('US/Eastern');
	$date = date('Y-m-d H:i:s');

	$sql = "INSERT INTO conversations_messages (conversation_id, from_id, message_date, message_text) VALUES (?, ?, ?, ?);";
	$stmt  = mysqli_stmt_init($conn);
	if(!mysqli_stmt_prepare($stmt, $sql)){
		$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
	} else {
		date_default_timezone_set('US/Eastern');
		$date = date('Y-m-d H:i:s');
		$from_id = $_SESSION['user_id'];
		// bind the data to the placeholders in order to prepare for insert in to database
		mysqli_stmt_bind_param($stmt, "iiss", $conversation_id, $from_id, $date, $text);
		// run insert execution
		mysqli_stmt_execute($stmt);
	}

	mysqli_query($conn, $sql);
}

function delete_conversation($conversation_id){
// marks logged in user as "deleted" when they delete a message
// the message will not be fully deleted from the database until both users
// have elected to delete the message, but the message will only be displayed for
// the user who has not marked the message as deleted yet
	$dbServername= "localhost";
	$db_username= "chblanton";
	$db_password= "J!anie11841";
	$dbname= "ibd_community";

	$conn = mysqli_connect($dbServername, $db_username, $db_password, $dbname);

	$conversation_id = (int)$conversation_id;
	$user_id = $_SESSION['user_id'];

	if(is_numeric($_SESSION['user_id']) == FALSE){
		header("Location: ../error.php");
		exit();
	}

	// this select and the following update statement set the receiver_delete column to 1
	// if the logged in user who clicked delete is the reciever of the message
	$sql = "SELECT receiver_delete
			FROM conversation_members
			WHERE user_id = $user_id
			AND conversation_id = $conversation_id";

	$results = mysqli_query($conn, $sql);
	$count = mysqli_num_rows($results);

	if ($count === 1){
		$sql = "
		UPDATE conversation_members
		SET receiver_delete = 1
		WHERE conversation_id = $conversation_id
		AND user_id = $user_id
		";

		mysqli_query($conn, $sql);
	}


	// this select and the following update statement set the sender_delete column to 1
	// if the logged in user who clicked delete is the sender of the message
	$sql = "SELECT sender_delete
			FROM conversation_members
			WHERE from_id = $user_id
			AND conversation_id = $conversation_id";

	$results = mysqli_query($conn, $sql);
	$count = mysqli_num_rows($results);

	if ($count === 1){
		$sql = "
		UPDATE conversation_members
		SET sender_delete = 1
		WHERE conversation_id = $conversation_id
		AND from_id = $user_id
		";

		mysqli_query($conn, $sql);
	}
	

	// This select statement, the while loop and the if statements below check if the delete column is marked
	// as 1 for both members of the conversation. If it is, then both members have elected to get rid of the conversation,
	// so the conversation is permanently deleted from the database
	$sql = "SELECT conversation_id, sender_delete, receiver_delete
			FROM conversation_members
			WHERE sender_delete = 1 
			AND receiver_delete = 1
			";

	$results = mysqli_query($conn, $sql);
	while($row = mysqli_fetch_array($results, MYSQLI_ASSOC)){
		$matching_rows = $row['conversation_id'];
	}

	if(isset($matching_rows)){
	if($matching_rows == $conversation_id){
		mysqli_query($conn, "DELETE FROM conversations WHERE conversation_id = $conversation_id");
		mysqli_query($conn, "DELETE FROM conversation_members WHERE conversation_id = $conversation_id");
		mysqli_query($conn, "DELETE FROM conversations_messages WHERE conversation_id = $conversation_id");
	}

	// need a page refresh here
}
	
}

?>