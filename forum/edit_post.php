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

	$current_post = $_GET['id'];
	// redirect user away if they attempt to edit a thread that is not there's in this php block
	$post_owner = mysqli_query($conn, "
	SELECT 
	post_id,
	post_creator
	FROM posts
	WHERE post_id = $current_post
	");
	// run query
	//$the_post_owner = mysqli_query($conn, $post_owner);
	 
	// get the username for the thread owner        
	while($row = mysqli_fetch_array($post_owner, MYSQLI_ASSOC)){
		$the_post_owner = $row['post_creator'];
	}

	if($_SESSION['username'] != $the_post_owner){
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
		<h2>EDIT POST</h2>

<?php
echo'
<a href="./thread.php?id=';
if (isset($_GET['t']))
{
$tid = $_GET['t'];
echo $tid; // return to the thread that this post belongs to
} 
echo'">Return</a><br/><br/>';

// redirect user away from editing a thread that doesn't exist
if (isset($_GET['id'])){
	$id_post = $_GET['id'];

	$select_current_post = "SELECT post_id FROM posts WHERE post_id = '$id_post'";
	$result = mysqli_query($conn, $select_current_post);
	$resultCheck = mysqli_num_rows($result);

	if ($resultCheck < 1){
		$path = $_SERVER['DOCUMENT_ROOT'];
		$path .= "/error.php";
		header("Location: $path");
	}
}


// get post content for echo in form
if(isset($_GET['id'])) {
	$current_post = $_GET['id'];

	$query_posts = "
	SELECT 
	post_content
	FROM posts 
	WHERE post_id = '$current_post'
	";
		
	// run query
	$result_posts = mysqli_query($conn, $query_posts);
		 
	// assign data for echo in to form          
	while($row = mysqli_fetch_array($result_posts, MYSQLI_ASSOC)){
		$post_content = $row['post_content'];
	}
}

if(!empty($_POST['post_edit'])) {
	if(isset($_GET['id'])){
		
		$post_id = $_GET['id'];
		// injection protection escape input
		$post_content = htmlspecialchars (mysqli_real_escape_string($conn, $_POST['post_content']));
		
		// begin prepare statement insert to protect against sql injection
		$sql = "UPDATE posts SET post_content = ? WHERE post_id = ?";
		$stmt = mysqli_stmt_init($conn);
		if (!mysqli_stmt_prepare($stmt, $sql)){
			echo $message = "Failed to edit post.<br />";
		} else {
			mysqli_stmt_bind_param($stmt, "sd", $post_content, $post_id);
			mysqli_stmt_execute($stmt);

			$post_content = $_POST['post_content'];
			$post_content = nl2br(stripcslashes($post_content));
			echo $message = "Update successful.<br /><br />";
		}
	}
} else echo 'Enter edit data in form and hit submit. <br/><br/>';// message displayed when user has not hit submit on the form yet.
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
