<?php
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:../error.php");
}
?>

<?php
	include_once "../includes/dbh.inc.php";

	$current_post = $_GET['id'];
	// redirect user away if they attempt to edit a post that is not there's 
	$post_owner = mysqli_query($conn,"
	SELECT 
	post_id,
	post_creator
	FROM posts
	WHERE post_id = $current_post");
	 
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
	include_once ('../header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>DELETE POST</h2>
		<hr/>


		<?php
		// BEGIN create link to navigate back to the post's parent thread
		// we want to keep this echo here so that the return link stays above the error message display
		echo'
		<a href="./thread.php?id=';
		if (isset($_GET['t']))
		{
		$id = $_GET['t'];
		echo $id; // return to the thread that this post belongs to
		} 
		echo'">Return</a><br/><br/>';
		
		// END create link to navigate back to the post's parent thread
		?>

		<!-- display deletion verification message to use under return link; pass 'yes' or 'no' response back to this page -->
		<?php 
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
		// BEGIN block for delete of post

		if(isset($_GET['d'])){ // $_GET['d'] is set when the user selects "yes" or "no" to whether they wish to delete the post or not
			$delete = $_GET['d']; // variable for whether they wish to delete the post or not, given a value or "yes" or "no"
			// BEGIN "if" for user clicking "yes" on the verification of whether they want to delete the post or not
			if($delete == 'yes'){ 
				echo 'Commence delete. '; // meant to  display message to user, but they currently don't get to see this before the page redirects
				$current_post = $_GET['id'];
				
				// BEGIN GRAB THREAD ID TO RETURN USER TO THREAD AFTER DELETE
				$current_thread = mysqli_query($conn,"SELECT post_thread_id FROM posts WHERE post_id = ".$current_post);

				while($row = mysqli_fetch_array($current_thread, MYSQLI_ASSOC)){
					$newURL = $row['post_thread_id'];
				}
				// END GRAB THREAD ID TO RETURN USER TO THRAD AFTER DELETE

				// query to delete post with placeholder variable used for current post id
				$sql = "DELETE FROM posts WHERE post_id = ?";
				
				// error check for query
				if (!$result = $conn->prepare($sql))
				{
				    die('Query failed: (' . $con->errno . ') ' . $con->error);
				}

				// bind placeholder value and the post id that the user is currently viewing
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
					echo $message = "Update successful.<br /><br />";
					$result->close();
					$conn->close();
					echo '
						</div>
						</section>
					';
					include_once '../footer.php';
					exit();
				}
				else
				{
				    echo "Couldn't delete the post ID."; // for when the query fails for any reason
				}

			} // END "if" for user clicking "yes" on the verification of whether they want to delete the post or not

			// if user selects no to the question if whether they really wanted to delete the post or not, then display this:
			elseif($delete == 'no'){
				echo $message = 'Stop delete. Click return if you wish to navigate away from post deletion.';
			}
		}
		// END block for delete of post
		?>

		<?php
		// BEGIN display the post that the user is looking to delete
		if(isset($_GET['id'])){
			// get post id from the url
			$post_id = $_GET['id'];

			// query to display the post the user wishes to delete
			$query_posts = mysqli_query($conn,"
			SELECT 
			a.post_id,
			a.post_content,
			a.post_date,
			a.post_creator,
			b.user_id,
			b.username,
			b.date_joined, /* date joined not being used yet */
			b.user_privilege,
			b.post_count
			FROM posts a, users b
			WHERE a.post_creator = b.username
			AND post_id = $post_id
			");

			// i need to place the data in an array so i can check if there's no post results returned before echo of post info in the  foreach loop
				while($row = mysqli_fetch_array($query_posts, MYSQLI_ASSOC)){
				$post_id = $row['post_id'];
				$post_creator = $row['username']; 
				$post_content = $row['post_content'];
				$post_count = $row['post_count']; 
				$post_creator_id = $row['user_id'];
				$user_privilege = $row['user_privilege'];
				}

				// delete margin-top when im done coding delete function
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
		// END display the post that the user is looking to delete
		?>

	</div>
</section>


<?php
	include_once '../footer.php';
?>
