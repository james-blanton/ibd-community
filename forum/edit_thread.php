<?php
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:../error.php");
}
?>

<?php
	// Attempt MySQL server connection
	include_once "../includes/dbh.inc.php";

	$current_thread = $_GET['id'];
	// redirect user away if they attempt to edit a thread that is not there's in this php block
	// writing the query like this gets rid of the rror where mysqli_fetch_array think it's a string
	$thread_owner = mysqli_query($conn, "
	SELECT 
	thread_id,
	thread_creator
	FROM threads
	WHERE thread_id = $current_thread
	");
	// run query
	//$the_thread_owner = mysqli_query($conn, $thread_owner);
	
	// get the username for the thread owner
	while($row = mysqli_fetch_array($thread_owner, MYSQLI_ASSOC)){
		$the_thread_owner = $row['thread_creator'];
	}

	if($_SESSION['username'] != $the_thread_owner){
		if($_SESSION['user_privilege'] != "admin"){
		$path = "../index.php";
	 	header("Location: $path");
	 	}
	}

?>

<?php
	include_once "../header.php";
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>EDIT THREAD</h2>

<?php

if (isset($_GET['id'])){
	$id_thread = $_GET['id'];
	// redirect user away from editing a thread that doesn't exist
	$select_current_thread = "SELECT thread_id FROM threads WHERE thread_id = '$id_thread'";
	$result = mysqli_query($conn, $select_current_thread);
	$resultCheck = mysqli_num_rows($result);

	if ($resultCheck < 1){
		$path = $_SERVER['DOCUMENT_ROOT'];
		$path .= "/error.php";
		header("Location: $path");
	}
}

// get thread information for echo in form
if(isset($_GET['id'])) {
	$current_thread = $_GET['id'];

	$query_content = "
	SELECT 
	thread_id,
	thread_title,
	thread_desc
	FROM threads
	WHERE thread_id = '$current_thread'
	";
		
	// run query
	$result_thread = mysqli_query($conn, $query_content);
		 
	// assign data for echo in to form          
	while($row = mysqli_fetch_array($result_thread, MYSQLI_ASSOC)){
		$thread_id = $row['thread_id'];
		$thread_title = $row['thread_title'];
		$thread_desc = $row['thread_desc'];
	}
}

// we want to keep this echo here so that the return link stays above the error message display
echo'
<a href="./thread.php?id=';
if (isset($thread_id))
{
$id = $thread_id;
echo $id; // return to the thread 
} 
echo'">Return</a><br/><br/>';

if(!empty($_POST['thread_edit'])) {
	if(isset($_GET['id'])){
		
		$thread_id = $_GET['id'];
		// injection protection escape input
		$thread_title = mysqli_real_escape_string($conn, $_POST['thread_title']);
		$thread_desc = mysqli_real_escape_string($conn, $_POST['thread_desc']);
		
		// begin prepare statement insert to protect against sql injection
		$sql = "UPDATE threads SET thread_title = ?, thread_desc = ? WHERE thread_id = ?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql)){
			echo $message = "Failed to edi thread.<br /><br />";
		} else {
			mysqli_stmt_bind_param($stmt, "ssd", $thread_title, $thread_desc, $thread_id);
			mysqli_stmt_execute($stmt);

			$thread_title = $_POST['thread_title'];
			$thread_title = nl2br(stripcslashes($thread_title));
			$thread_desc = $_POST['thread_desc'];
			$thread_desc = nl2br(stripcslashes($thread_desc));
			echo $message = "Update successful.<br /><br />";
		}
	}
} else echo 'Enter edit data in form and hit submit. <br/><br/>';  // message displayed when user has not hit submit on the form yet.
?>

<form method="post" action="<?php $_PHP_SELF ?>">
        Message [20,000 characters max]:<br />
        <textarea name="thread_title" rows = "1" maxlength="60" /><?php echo strip_tags(nl2br(stripcslashes($thread_title))) ?></textarea><br /><br />
        <textarea name="thread_desc" rows = "10" maxlength="20000" /><?php echo strip_tags(nl2br(stripcslashes($thread_desc))) ?></textarea><br /><br />
        <input type="submit" name="thread_edit" style = "float:left;" value="Submit Edit" />
</form>

</div>
</section>


<?php
	include_once '../footer.php';
?>
