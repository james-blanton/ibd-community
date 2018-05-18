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
	// Attempt MySQL server connection
	include_once "../includes/dbh.inc.php";

	// typecast data obtained from url for inject protection
	$current_thread = (int)$_GET['id'];

	// query to obtain the owner of the thread
	$thread_owner = mysqli_query($conn, "
	SELECT 
	thread_id,
	thread_creator
	FROM threads
	WHERE thread_id = $current_thread
	");
	
	// get the username for the thread owner from the query 
	while($row = mysqli_fetch_array($thread_owner, MYSQLI_ASSOC)){
		$the_thread_owner = $row['thread_creator'];
	}

	// check if logged in user is the owner of the post, an admin or a forum moderator
	// redirect to error page if they are not
	if($_SESSION['username'] == $the_thread_owner){
	}
	elseif($_SESSION['user_privilege'] == "admin"){
	}
	elseif($_SESSION['user_privilege'] == "mod"){
	}
	else{
	 	header("Location: ../error.php");
	}

?>

<?php
// include universal header file
include_once "../header.php";
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>EDIT THREAD</h2>
<?php
// url to return to the thread
echo'
<a href="./thread.php?id=';
if (isset($current_thread))
{
$id = $current_thread;
echo $id; // return to the thread 
} 
echo'">Return</a><br/><br/>';

// redirect user away from editing a thread that doesn't exist
if (isset($_GET['id'])){
	// typecast data obtained from url for inject protection
	$id_thread = (int)$_GET['id'];

	// check if the thread ID exists in the database or not
	// boolean flag indicates if the post has already been marked as deleted or not
	$select_current_thread = "SELECT thread_id FROM threads WHERE thread_id = '$id_thread' AND deleted = 0";
	// execute query
	$result = mysqli_query($conn, $select_current_thread);
	// count how many rows the query returned
	$resultCheck = mysqli_num_rows($result);

	// if no results are returned, then redirect the user
	if ($resultCheck < 1){
		$path = $_SERVER['DOCUMENT_ROOT'];
		$path .= "../error.php";
		header("Location: $path");
	}
}

// get thread information for echo in form
if(isset($_GET['id'])) {
	// typecast data obtained from url for inject protection
	$current_thread = (int)$_GET['id'];

	// query to obtain all thread content - some of which is only used by admins and moderators
	$query_content = "
	SELECT 
	thread_id,
	thread_title,
	thread_desc,
	deleted,
	category_id,
	sticky
	FROM threads
	WHERE thread_id = '$current_thread'
	";
		
	// run query
	$result_thread = mysqli_query($conn, $query_content);
		 
	// assign data in to a variable for display in the form     
	while($row = mysqli_fetch_array($result_thread, MYSQLI_ASSOC)){
		$thread_id = $row['thread_id'];
		$thread_title = $row['thread_title'];
		$thread_desc = $row['thread_desc'];
		$deleted = $row['deleted'];
		$category_id = $row['category_id'];
		$sticky = $row['sticky'];
	}
}

if(!empty($_POST['thread_edit'])) {
	if(isset($_GET['id'])){
		// typecast data obtained from url for inject protection
		$thread_id = (int)$_GET['id'];
		// sql injection / xss attack prevention by escaping input sent by form submit
		$thread_title = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['thread_title']));
		$thread_desc = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['thread_desc']));
		$deleted = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['deleted']));
		$category_id = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['category_id']));
		$sticky = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['sticky']));

		// begin prepare statement insert to protect against sql injection
		$sql = "UPDATE threads SET thread_title = ?, thread_desc = ?, deleted = ?, category_id = ?, sticky = ? WHERE thread_id = ?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql)){
			echo $message = "Failed to edi thread.<br /><br />";
		} else {
			// bind placeholders to data obtained from user submitted info from POST
			// i = integer / d = double / s = string
			mysqli_stmt_bind_param($stmt, "ssiiii", $thread_title, $thread_desc, $deleted, $category_id, $sticky, $thread_id);
			mysqli_stmt_execute($stmt);

			// update thread data variables so it will display correctly in the  form after submitting the form
			$thread_title = $_POST['thread_title'];
			$thread_title = nl2br(stripcslashes($thread_title));
			$thread_desc = $_POST['thread_desc'];
			$thread_desc = nl2br(stripcslashes($thread_desc));
			$deleted= $_POST['deleted'];
			$category_id = $_POST['category_id'];
			$sticky = $_POST['sticky'];
			echo $message = "Update successful.<br /><br />";
		}
	}
// This message displays when the user has not hit submit on the form yet.
} else echo 'Enter edit data in form and hit submit. <br/><br/>';
?>

<form method="post" action="<?php $_PHP_SELF ?>">
        Message [20,000 characters max]:<br />
        <textarea name="thread_title" rows = "1" maxlength="60" /><?php echo strip_tags(nl2br(stripcslashes($thread_title))) ?></textarea><br /><br />
        <textarea name="thread_desc" rows = "10" maxlength="20000" /><?php echo strip_tags(nl2br(stripcslashes($thread_desc))) ?></textarea><br /><br />
        
        <?php
        if(isset($_SESSION['user_privilege'])){
        if($_SESSION['user_privilege'] == "admin" || "mod"){
        ?>
        Temporarily deleted?<br/>
        <select name='deleted' maxlength='75'>
			<option value='<?php if($deleted==1){echo '1';}else{echo '0';} ?>'><?php if($deleted==1){echo 'YES';}else{echo 'NO';} ?></option>
			<option value='1'>Yes</option>
			<option value='NULL'>No</option>
		</select><br/><br/>
		<?php
		}
		}
		?>

		<?php
        if(isset($_SESSION['user_privilege'])){
        if($_SESSION['user_privilege'] != "admin" || $_SESSION['user_privilege'] != "mod"){
        ?>
        Category:<br/>
        <select name='category_id' maxlength='75'>
			<option value='<?php echo $category_id; ?>'><?php echo $category_id; ?></option>
			<?php include_once "category_options.php"; ?>
		</select><br/><br/>

		Sticky:
		<select name='sticky' maxlength='75'>
			<option value='<?php if($sticky==1){echo '1';}else{echo '0';} ?>'><?php if($sticky==1){echo 'YES';}else{echo 'NO';} ?></option>
			<option value='1'>Yes</option>
			<option value='0'>No</option>
		</select><br/><br/>
		<?php
		}
		}
		?>

        <input type="submit" name="thread_edit" style = "float:left;" value="Submit Edit" />
</form>

</div>
</section>


<?php
	include_once '../footer.php';
?>
