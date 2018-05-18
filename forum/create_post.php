<?php
// set session if one isn't already set
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

// check if user is logged in
// redirect to error page if they are not
if(!isset($_SESSION['username'])){
 	header("Location:../error.php");
}
?>

<?php
// verify that GET is numeric
if(is_numeric($_GET['id']) == FALSE){
	header("Location: ../error.php");
	exit();
}
?>

<?php
include_once "../header.php";
?>

<?php

// this php block redirects the user away from creating a post for a thread that does not exist
if (isset($_GET['id'])){
	// get the thread id that we wish to place this new post in from the url
	// typecast for security
	$thread_id = (int)$_GET['id'];

	// find the unique id for current thread
	$select_current_thread = "SELECT thread_id FROM threads WHERE thread_id = '$thread_id'";
	// execute query
	$result = mysqli_query($conn, $select_current_thread);
	// count how many rows were returned by the query
	$resultCheck = mysqli_num_rows($result);

	// redirect user if they try and create a post for a thread that does not exist
	// aka the query returned no results
	if ($resultCheck < 1){
		$path = $_SERVER['DOCUMENT_ROOT'];
		$path .= "/error.php";
		header("Location: $path");
	}
}

//  if the form to create a post has been submitted, then run this block of code
if (!empty($_POST['submit'])){

	// Attempt MySQL server connection
	include '../includes/dbh.inc.php';
	 
	// Check connection
	if($conn === false){
	    die("ERROR: Could not connect. " . mysqli_connect_error());
	}
	
	// date & time formatting for inserting post in to database
	$post_date = date('Y-m-d H:i:s');

	// sql injection / xss attack prevention by escaping input sent by form submit
	$post_content = htmlspecialchars (mysqli_real_escape_string($conn, $_REQUEST['post_content']));
	$post_creator = $_SESSION['username']; 

	// obtained the thread id number from the url
	// typecast for security purposes
	if (isset($_GET['id'])){
		$thread_id = (int)$_GET['id']; 
	} else {
		$message = "No thread ID obtained";
	}

	// do not let user submit if input fields are empty
	if (!empty($post_content || $post_creator)){ 
		// begin prepare statement insert to protect against sql injection
		$sql = "INSERT INTO posts (post_content, post_date, post_creator, post_thread_id) VALUES (?, ?, ?, ?)";
		$stmt = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			$message = "Failed to create post.";
		} else {
			// bind placeholders to data obtained from user submitted info from POST
			// i = integer / d = double / s = string
			mysqli_stmt_bind_param($stmt, "sssi", $post_content, $post_date, $post_creator, $thread_id);
			// run insert execution
			mysqli_stmt_execute($stmt);
			$message = "New post created!";

			// update reply time for the thread that the post belongs to
			$sql = "UPDATE threads SET thread_reply_date = ? WHERE thread_id = ?";
			$stmt = mysqli_stmt_init($conn);
			if (!mysqli_stmt_prepare($stmt, $sql)){
				$message = "Failed to edit parent thread reply date.<br />";
			} else {
				$now = date('Y-m-d H:i:s'); 
				// bind placeholders to data obtained from user submitted info from POST
			// i = integer / d = double / s = string
				mysqli_stmt_bind_param($stmt, "sd", $now, $thread_id);
				mysqli_stmt_execute($stmt);

				$message = "Update  of parent thread reply date successful.<br /><br />";
			}

			// the following is used to update the user's post count 
			// typecast for security purposes
			$user_id = (int)$_SESSION['user_id'];

			// query to select the users current post count from the users table
			$upd_posts = "SELECT post_count FROM users WHERE user_id = '$user_id'";
			// run query
			$result= mysqli_query($conn, $upd_posts);
			// if query returns a resulting post count for the user, then begin update on post count
			if ($result->num_rows > 0) {
				 while($row = $result->fetch_assoc()){
				 	// take the users current post count, add one to it, and reassign the new value to the variable
				 	$current_posts = $row['post_count'];
				 	$updated_posts = $current_posts + 1;

				 	// query for bound parameter update
				 	$query = "UPDATE users SET post_count = ? WHERE user_id = ?";
					$stmt = mysqli_stmt_init($conn);

					if (!mysqli_stmt_prepare($stmt, $query)){
						echo $message = "Failed to update.";
					} else {
						// bind placeholders to data obtained from user submitted info from POST
						// i = integer / d = double / s = string
						mysqli_stmt_bind_param($stmt, "ii", $updated_posts, $user_id);
						mysqli_stmt_execute($stmt);
					}
				 } // end while
			} // end if ($result->num_rows > 0)
			// update user post count end

			// generate a url to return the used back to the parent thread for the post
			if (isset($_GET['id'])){
			$thread_id = $_GET['id'];
			} 

			$message = "Return to thread
			<a href=./thread.php?id=".$thread_id.">here.</a>";
		}

		// close connection
		mysqli_close($conn);
	// display this message if user hits submit without inserting a post in to the form
	} else { $message = "Failed to submit post.";} 
// this is the message that is give when the page first loads before the user attempts to enter form information
} else $message = "Please enter post information."; 
?>
<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>CREATE POST</h2>
		<hr>
		<?php // obtained the thread id number from the url ... returns user to the thread they were on when they clicked 'create post' ?>
		<a href="./thread.php?id=<?php 
		if (isset($_REQUEST['id'])){
		$thread_id = $_REQUEST['id'];
		echo $thread_id; 
		} ?>">Back to thread</a><br/><br/>

		<?php
		// echo error and success messages to user that are triggered by submitting the form
		echo $message;
		?>

		<br><br>
		<form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST">
		    <p>
		        <label for="postContent">Post Content:</label><br><br>
		        <textarea type="textarea" name="post_content" id="postContent" maxlength="20000" style="width:100%; height: 100px;" placeholder="Max 20,000 characters"></textarea>
		    </p><br><br>
		    <input type="submit" name="submit" value="Submit">
		</form>

	</div>
</section>
<!-- end content actually shown to the user -->

<?php
include_once '../footer.php';
?>