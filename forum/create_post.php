
<?php
include_once "../header.php";
?>

<?php
if(!isset($_SESSION['username'])){
	$path = $_SERVER['DOCUMENT_ROOT'];
	$path .= "/error.php";
 	header("Location:$path");
}

// begin redirect user away from creating a post for a thread that does not exist
if (isset($_REQUEST['id'])){
	$thread_id = $_REQUEST['id'];

	// find the unique id for current thread
	$select_current_thread = "SELECT thread_id FROM threads WHERE thread_id = '$thread_id'";
	$result = mysqli_query($conn, $select_current_thread);
	$resultCheck = mysqli_num_rows($result);

	// redirect user if they try and create a post for a thread that does not exist
	if ($resultCheck < 1){
		$path = $_SERVER['DOCUMENT_ROOT'];
		$path .= "/error.php";
		header("Location: $path");
	}
}
// end redirect user away from creating a post for a thread that does not exist

//  handle create new post form submission START
if (!empty($_POST['submit'])){

	// Attempt MySQL server connection
	include '../includes/dbh.inc.php';
	 
	// Check connection
	if($conn === false){
	    die("ERROR: Could not connect. " . mysqli_connect_error());
	}
	
	// date & time formatting for inserting post in to database
	$post_date = date('Y-m-d H:i:s');

	// injection protection escape input
	$post_content = htmlspecialchars (mysqli_real_escape_string($conn, $_REQUEST['post_content']));
	$post_creator = $_SESSION['username']; // do not my_real_escape_string this ... it will mess up post creation

	// this kind of error handle is now required by newer versions of php
	// or else you will get an error of "Notice: Undefined index: thread_id"
	if (isset($_REQUEST['id'])){
		$thread_id = $_REQUEST['id']; // obtained the thread id number from the url
	} else {
		$message = "No thread ID obtained";
	}

	 
	if (!empty($post_content || $post_creator)){ // do not let user submit if input fields are empty
		// begin prepare statement insert to protect against sql injection
		$sql = "INSERT INTO posts (post_content, post_date, post_creator, post_thread_id) VALUES (?, ?, ?, ?)";
		$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			$message = "Failed to create post.";
		} else {
			mysqli_stmt_bind_param($stmt, "sssd", $post_content, $post_date, $post_creator, $thread_id);
			mysqli_stmt_execute($stmt);
			$message = "New post created!";

			// update reply time for the thread that the post belongs to
			$sql = "UPDATE threads SET thread_reply_date = ? WHERE thread_id = ?";
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, $sql)){
				// This message will not be seen by the user before redirect
				// I have this here for testing purposes
				$message = "Failed to edit parent thread reply date.<br />";
			} else {
				$now = date('Y-m-d H:i:s'); // format date display
				mysqli_stmt_bind_param($stmt, "sd", $now, $thread_id);
				mysqli_stmt_execute($stmt);
				// This message will not be seen by the user before redirect
				// I have this here for testing purposes
				$message = "Update  of parent thread reply date successful.<br /><br />";
			}

			// update user post count begin
			$user_id = $_SESSION['user_id'];
			$upd_posts = "SELECT post_count FROM users WHERE user_id = '$user_id'";
			$result= mysqli_query($conn, $upd_posts);
			// if query returns a post count for user, begin update on post count
			if ($result->num_rows > 0) {
				 while($row = $result->fetch_assoc()){
				 	$current_posts = $row['post_count'];
				 	$updated_posts = $current_posts + 1;

				 	// query for bound parameter update
				 	$query = "UPDATE users SET post_count = ? WHERE user_id = ?";
					$stmt = mysqli_stmt_init($conn);

					if (!mysqli_stmt_prepare($stmt, $query)){
						echo $message = "Failed to update.";
					} else {
						// bind placeholders to data obtained from user submitted info from POST
						mysqli_stmt_bind_param($stmt, "dd", $updated_posts, $user_id);
						mysqli_stmt_execute($stmt);
					}
				 } // end while
			} // end if ($result->num_rows > 0)
			// update user post count end

			// the current thread
			if (isset($_REQUEST['id'])){
			$thread_id = $_REQUEST['id'];
			} 

			// link to return to thread
			$message = "<br/> Return to thread
			<a href=./thread.php?id=".$thread_id.">here.</a>";
		}
		// end prepare statement insert to protect against sql injection

		// close connection
		mysqli_close($conn);
	} else { $message = "Failed to submit post.";} // display this message if user hits submit without inserting a post in to the form

} else $message = "Please enter post information."; // this is the message that is give when the pag first loads before the user attempts to enter form information
//  handle create new post form submission END
?>

<!-- begin content actually shown to the user -->
<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>CREATE POST</h2>
		<hr>

		<a href="./thread.php?id=<?php 
		if (isset($_REQUEST['id'])){
		$thread_id = $_REQUEST['id'];
		echo $thread_id; // obtained the thread id number from the url ... returns user to the thread they were on when they clicked 'create post'
		} ?>">Back to thread</a><br/><br/>

		<?php
		// echo error and success messages to user that are triggered by submitting the form
		echo $message;
		?>

		<br><br>
		<form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST">
		    <p>
		        <label for="postContent">Post Content:</label><br><br>
		        <textarea type="textarea" name="post_content" id="postContent" style="width:100%; height: 100px;"></textarea>
		    </p><br><br>
		    <input type="submit" name="submit" value="Submit">
		</form>

	</div>
</section>
<!-- end content actually shown to the user -->

<?php
include_once '../footer.php';
?>