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
	$current_post = (int)$_GET['id'];

	// query to obtain the owner of the post
	$post_owner = mysqli_query($conn, "
	SELECT 
	post_id,
	post_creator
	FROM posts
	WHERE post_id = $current_post
	");
	 
	// get the username for the post owner from the query     
	while($row = mysqli_fetch_array($post_owner, MYSQLI_ASSOC)){
		$the_post_owner = $row['post_creator'];
	}

	// check if logged in user is the owner of the post, an admin or a forum moderator
	// redirect to error page if they are not
	if($_SESSION['username'] == $the_post_owner){
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
		<h2>EDIT POST</h2>

<?php
// url to return to the parent thread that the post belongs to
echo'
<a href="./thread.php?id=';
if (isset($_GET['t']))
{
$tid = $_GET['t'];
echo $tid; 
} 
echo'">Return</a><br/><br/>';

// redirect user away from editing a post that doesn't exist
if (isset($_GET['id'])){
	// typecast data obtained from url for inject protection
	$id_post = (int)$_GET['id'];

	// check if the post ID exists in the database or not
	// boolean flag indicates if the post has already been marked as deleted or not
	$select_current_post = "SELECT post_id FROM posts WHERE post_id = '$id_post' AND deleted = 0";
	// execute query
	$result = mysqli_query($conn, $select_current_post);
	// count how many rows the query returned
	$resultCheck = mysqli_num_rows($result);

	// if no results are returned, then redirect the user
	if ($resultCheck < 1){
		$path = "../error.php";
		header("Location: $path");
	}
}


// get post content for display in form
if(isset($_GET['id'])) {
	// typecast data obtained from url for inject protection
	$current_post = (int)$_GET['id'];

	// query to get the post content itself
	$query_posts = "
	SELECT 
	post_content
	FROM posts 
	WHERE post_id = '$current_post'
	AND deleted = 0
	";
		
	// run query
	$result_posts = mysqli_query($conn, $query_posts);
		 
	// assign data in to a variable for display in the form          
	while($row = mysqli_fetch_array($result_posts, MYSQLI_ASSOC)){
		$post_content = $row['post_content'];
	}
}

if(!empty($_POST['post_edit'])) {
	if(isset($_GET['id'])){
		// typecast data obtained from url for inject protection
		$post_id = (int)$_GET['id'];
		// sql injection / xss attack prevention by escaping input sent by form submit
		$post_content = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['post_content']));
		
		// begin prepare statement insert to protect against sql injection
		$sql = "UPDATE posts SET post_content = ? WHERE post_id = ?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql)){
			echo $message = "Failed to edit post.<br />";
		} else {
			// bind placeholders to data obtained from user submitted info from POST
			// i = integer / d = double / s = string
			mysqli_stmt_bind_param($stmt, "si", $post_content, $post_id);
			mysqli_stmt_execute($stmt);

			// update post content variable so it will display correctly in the form after submitting the form
			$post_content = $_POST['post_content'];
			$post_content = nl2br(stripcslashes($post_content));
			echo $message = "Update successful.<br /><br />";
		}
	}
// This message displays when the user has not hit submit on the form yet.
} else echo 'Enter edit data in form and hit submit. <br/><br/>';
?>

<form method="post" action="<?php $_PHP_SELF ?>">
        Message [20,000 characters max]:<br />
        <textarea name="post_content" id="postContent" maxlength="20000" style="width:100%; height: 100px;"/><?php echo strip_tags(nl2br(stripcslashes($post_content))); ?></textarea><br /><br />
        <input type="submit" name="post_edit" style = "float:left;" value="Submit Edit" />
</form>

</div>
</section>


<?php
   include_once '../footer.php';
?>
