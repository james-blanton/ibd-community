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
if(is_numeric($_GET['category_id']) == FALSE){
	header("Location: ../error.php");
	exit();
}
?>

<?php
// this php block redirects the user away from creating a thread for a category that does not exist
if (isset($_GET['category_id'])){
	// Attempt MySQL server connection
	include '../includes/dbh.inc.php';

	// get the category id that we wish to place this new thread in from the url
	// typecast for security
	$category_id = (int)$_GET['category_id'];

	// quey to obtain the category information using the category id obtained from the url
	$select_current_category = "SELECT cat_id FROM category WHERE cat_id = '$category_id'";
	// execute query
	$result = mysqli_query($conn, $select_current_category);
	// count how many rows were returned by the query
	$resultCheck = mysqli_num_rows($result);

	// redirect user if they try and create a thread for a category that does not exist
	// aka the query returned no results
	if ($resultCheck != 1){
		$path = "../error.php";
		header("Location: $path");
	}
}
?>

<?php
// include universal header file
include_once "../header.php";
?>

<?php
//  if the form to create a thread has been submitted, then run this block of code
if (isset($_POST['submit'])){

	// Attempt MySQL server connection
	include '../includes/dbh.inc.php';
	 
	// Check connection
	if($conn === false){
	    die("ERROR: Could not connect. " . mysqli_connect_error());
	}

	// sql injection / xss attack prevention by escaping input sent by form submit
	$thread_title = htmlspecialchars (mysqli_real_escape_string($conn, $_REQUEST['thread_title']));
	$thread_desc = htmlspecialchars (mysqli_real_escape_string($conn, $_REQUEST['thread_desc']));
	$thread_creator = $_SESSION['username']; 
	$last_user_posted = $_SESSION['username'];
	date_default_timezone_set('US/Eastern');
	$thread_date = date('Y-m-d H:i:s');
	$thread_reply_date = date('Y-m-d H:i:s');

	// obtained the category id number from the url
	// typecast for security purposes
	if (isset($_GET['category_id'])){
		$category_id = (int)$_GET['category_id']; 
	} else {
		$message = "No category ID obtained";
	}
	
	// do not let user submit if input fields are empty
	if (!empty($thread_title || $thread_desc)){

		// begin prepare statement insert to protect against sql injection
		$sql = "INSERT INTO threads (thread_title, thread_desc, thread_creator, thread_date, thread_reply_date, last_user_posted, category_id) VALUES (?, ?, ?, ?, ?, ?, ?);";
		$stmt  = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
		} else {
			// bind placeholders to data obtained from user submitted info from POST
			// i = integer / d = double / s = string
			mysqli_stmt_bind_param($stmt, "ssssssi", $thread_title, $thread_desc, $thread_creator, $thread_date, $thread_reply_date, $last_user_posted, $category_id);
			// run insert execution
			mysqli_stmt_execute($stmt);

			// Grab the id for the thread that was just inserted and use it to direct the user to the new thread.
			$last_id = $conn->insert_id;
			
			$message = "Thread created successfully. Visit new thread <a href='./thread.php?id=".$last_id."'/>HERE</a>.";
		}

		// close connection
		mysqli_close($conn);
	} else { $message = "Failed to submit thread.";}
// user did not enter data in to the form
} else $message = "Please enter thread information.";

?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>CREATE THREAD</h2>
		<hr>
		<?php // obtained the category id number from the url ... returns user to the category they were on when they clicked 'create thread' ?>
		<a href="./category.php?id=<?php 
		if (isset($_REQUEST['category_id'])){
		$category_id = $_REQUEST['category_id'];
		echo $category_id;
		} ?>">Back</a><br/><br/>

		<?php
		// echo error and success messages to user that are triggered by submitting the form
		echo $message;
		?>

		<br><br>
		<form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST">
		    <p>
		        <label for="threadTitle" >Subject:</label><br><br>
		        <input type="text" name="thread_title" id="threadTitle" maxlength="60" placeholder="Max 60 characters">
		    </p><br><br>
		        <label for="threadDisc">Thread Description:</label><br><br>
		        <textarea type="textarea" name="thread_desc" id="threadDisc" maxlength="20000" onfocus="this.value=''" placeholder="Max 20,000 characters" ></textarea>
		    </p><br><br>
		    <input type="submit" name="submit" value="Submit">
		</form>

	</div>
</section>


<?php
include_once '../footer.php';
?>