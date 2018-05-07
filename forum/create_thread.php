
<?php
include_once "../header.php";
?>

<?php
if(!isset($_SESSION['username'])){
	$path = $_SERVER['DOCUMENT_ROOT'];
	$path .= "/error.php";
 	header("Location:$path");
}

// begin redirect user away from creating a thread for a category that does not exist
if (isset($_REQUEST['category_id'])){
	$category_id = $_REQUEST['category_id'];

	// get current category using the category id obtained from the url
	$select_current_category = "SELECT cat_id FROM category WHERE cat_id = '$category_id'";
	$result = mysqli_query($conn, $select_current_category);
	$resultCheck = mysqli_num_rows($result);

	// redirect user if they try and thread for a category that does not exist
	if ($resultCheck < 1){
		$path = $_SERVER['DOCUMENT_ROOT'];
		$path .= "/error.php";
		header("Location: $path");
	}
}
// end redirect user away from creating a thread for a category that does not exist

//  handle create new thread form submission START
if (!empty($_POST['submit'])){

	// Attempt MySQL server connection
	include '../includes/dbh.inc.php';
	 
	// Check connection
	if($conn === false){
	    die("ERROR: Could not connect. " . mysqli_connect_error());
	}
	
	// date & time formatting for insert
	$d=strtotime("10:30pm April 15 2014");

	// injection protection escape input
	$thread_title = htmlspecialchars (mysqli_real_escape_string($conn, $_REQUEST['thread_title']));
	$thread_desc = htmlspecialchars (mysqli_real_escape_string($conn, $_REQUEST['thread_desc']));
	$thread_creator = $_SESSION['username']; // do not real_escape_string this
	$last_user_posted = $_SESSION['username'];
	$thread_date = date('Y-m-d H:i:s');
	$thread_reply_date = date('Y-m-d H:i:s');

	// this kind of error handle is now required by newer versions of php
	// or else you will get an error of "Notice: Undefined index: category_id"
	if (isset($_REQUEST['category_id'])){
		$category_id = $_REQUEST['category_id']; // obtained the category id number from the url
	} else {
		$message = "No category ID obtained";
	}
	 
	if (!empty($thread_title || $thread_desc)){ // do not let user submit if input fields are empty

		// begin prepare statement insert to protect against sql injection
		$sql = "INSERT INTO threads (thread_title, thread_desc, thread_creator, thread_date, thread_reply_date, last_user_posted, category_id) VALUES (?, ?, ?, ?, ?, ?, ?);";
		$stmt  = mysqli_stmt_init($conn);
		if(!mysqli_stmt_prepare($stmt, $sql)){
			$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
		} else {
			// bind the data to the placeholders in order to prepare for insert in to database
			mysqli_stmt_bind_param($stmt, "sssssss", $thread_title, $thread_desc, $thread_creator, $thread_date, $thread_reply_date, $last_user_posted, $category_id);
			// run insert execution
			mysqli_stmt_execute($stmt);

			// This is REALLY cool. It lets me grab the url for the thread that was just created.
			$last_id = $conn->insert_id;
			
			$message = "Thread created successfully. Visit new thread <a href='./thread.php?id=".$last_id."'/>HERE</a>.";
		}
		// begin prepare statement insert to protect against sql injection

		// close connection
		mysqli_close($conn);
	} else { $message = "Failed to submit thread.";}

} else $message = "Please enter thread information."; // for when the user presses submit without entering thread information
//  handle create new thread form submission END
?>

<!-- content actually shown to the user -->
<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>CREATE THREAD</h2>
		<hr>

		<a href="./category.php?id=<?php 
		if (isset($_REQUEST['category_id'])){
		$category_id = $_REQUEST['category_id'];
		echo $category_id; // obtained the category id number from the url ... returns user to the category they were on when they clicked 'create thread'
		} ?>">Back</a><br/><br/>

		<?php
		// echo error and success messages to user that are triggered by submitting the form
		echo $message;
		?>

		<br><br>
		<form action="<?php $_SERVER['PHP_SELF'] ?>" method="POST">
		    <p>
		        <label for="threadTitle" >Subject:</label><br><br>
		        <input type="text" name="thread_title" id="threadTitle">
		    </p><br><br>
		        <label for="threadDisc">Thread Description:</label><br><br>
		        <textarea type="textarea" name="thread_desc" id="threadDisc"></textarea>
		    </p><br><br>
		    <input type="submit" name="submit" value="Submit">
		</form>

	</div>
</section>


<?php
include_once '../footer.php';
?>