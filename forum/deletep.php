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
// redirect user away from this page if they attempt to edit a post that was not created by them
	// Attempt MySQL server connection
	include_once "../includes/dbh.inc.php";
	// typecast data obtained from url for inject protection
	$current_post = (int)$_GET['id'];

	// query to obtain the creator of the post
	$post_owner = mysqli_query($conn,"
	SELECT 
	post_id,
	post_creator
	FROM posts
	WHERE post_id = $current_post");
	 
	// get the username for the post owner        
	while($row = mysqli_fetch_array($post_owner, MYSQLI_ASSOC)){
		$the_post_owner = $row['post_creator'];
	}

	// if the user attempting to access the page is not the creator of the post,
	// an admin or a forum moderator, then direct them away from this page.
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
	include_once ('../header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>DELETE POST</h2>
		<hr/>

		<?php
		// create link to navigate back to the post's parent thread
		// we want to keep this echo here so that the return link stays above the error message display
		echo'
		<a href="./thread.php?id=';
		if (isset($_GET['t']))
		{
		$id = (int)$_GET['t'];
		echo $id; 
		} 
		echo'">Return</a><br/><br/>';
		
		?>

		<?php 
		// display deletion verification message to use under return link; pass 'yes' or 'no' response back to this page 
		if (isset($_GET['d'])){
		} else { 
		$thread_id=$_GET['t'];
		?>
		Are you sure you want to delete your post?<br>
		You will be redirected back to the thread after you delete a post.<br/><br/>
		<a href = "deletep.php?id=<?php echo $current_post .'&d=yes&t='.$thread_id; ?> ">Yes</a>&nbsp;	&nbsp;	&nbsp;
		<a href = "deletep.php?id=<?php echo $current_post  .'&d=no&t='.$thread_id; ?> ">No</a>
		<br/><br/>
		<?php } ?>

		<?php
		// $_GET['d'] is set when the user selects "yes" or "no" to whether they wish to delete the post or not. This value is either a "yes" or "no".
		if(isset($_GET['d'])){ 
			$delete = $_GET['d'];
			// If the user clicking "yes" on the verification of whether they want to delete the post or not, then execute the following code to mark the post as deleted with a boolean flag of 1.
			if($delete == 'yes'){ 
				echo 'Commence delete. '; 
				// Typecast for extra security
				$current_post = (int)$_GET['id'];
				
				// Grab the thread id for the thread that this post belongs to so that we can redirect the user once the deletion of the post is completed.
				$current_thread = mysqli_query($conn,"SELECT post_thread_id FROM posts WHERE post_id = ".$current_post);

				while($row = mysqli_fetch_array($current_thread, MYSQLI_ASSOC)){
					$newURL = $row['post_thread_id'];
				}

				// query to soft-delete the post with boolean flag
				// a placeholder variable used for current post id
				$sql = "UPDATE posts SET deleted = 1 WHERE post_id = ?";
				
				// error check the query
				if (!$result = $conn->prepare($sql))
				{
				    die('Query failed: (' . $con->errno . ') ' . $con->error);
				}

				// bind placeholders to data obtained from user submitted info from POST
				// i = integer / d = double / s = string
				if (!$result->bind_param('i', $current_post))
				{
				    die('Binding parameters failed: (' . $result->errno . ') ' . $result->error);
				}

				// execute query
				if (!$result->execute())
				{
				    die('Execute failed: (' . $result->errno . ') ' . $result->error);
				}

				// check if the delete query execution was a success ...
				if ($result->affected_rows > 0)
				{
					//return user to thread after a successful delete
					echo $message = "Post deletion successful.<br /><br />";
					$result->close();
					$conn->close();
					echo '
						</div>
						</section>
					';
					// include universal footer file and end the page
					include_once '../footer.php';
					exit();
				}
				else
				{
					// for when the query fails for any reason
				    echo $message = "Could not delete the post."; 
				}

			} // END if statement for selection "yes" to the deletion verification question.

			// if user selects no to the question if whether they really wanted to delete the post or not, then display this:
			elseif($delete == 'no'){
				echo $message = 'Stop delete. Click return if you wish to navigate away from post deletion.';
			}
		}
		?>

		<?php
		// prepare to display the post that the user is looking to delete
		if(isset($_GET['id'])){
			// get post id typecast data obtained from url for inject protection
			$post_id = (int)$_GET['id'];

			// query to display the post the user wishes to delete
			$query_posts = mysqli_query($conn,"
			SELECT 
			a.post_id,
			a.post_content,
			a.post_date,
			a.post_creator,
			b.user_id,
			b.username,
			b.date_joined,
			b.user_privilege,
			b.post_count
			FROM posts a, users b
			WHERE a.post_creator = b.username
			AND post_id = $post_id
			");

			// run query and place returned data in to variables
			while($row = mysqli_fetch_array($query_posts, MYSQLI_ASSOC)){
			$post_id = $row['post_id'];
			$post_creator = $row['username']; 
			$post_content = $row['post_content'];
			$post_count = $row['post_count']; 
			$post_creator_id = $row['user_id'];
			$user_privilege = $row['user_privilege'];
			}

			// dsplay the post information to the user
			echo '
			<div class = "response_post_header"><div class="padding">';
				
			echo '
			<br><div class = "user_avatar"></div>
			<br><a href = "profile.php?id=' .$post_creator_id.'">'. $post_creator .'</a>
			<br>Rank: '. $user_privilege .
			'<br>Posts: '.$post_count.'
			</div>
			';

			echo '</div><div class = "response_post_content"><div class="padding">' . nl2br(stripcslashes($post_content)) . '</div></div></div>';
				
		}
		
		?>

	</div>
</section>


<?php
	// include universal footer file
	include_once '../footer.php';
?>
