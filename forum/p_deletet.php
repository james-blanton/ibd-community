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
	$thread_id = $_GET['id'];
	// redirect user away if they attempt to edit a thread that is not there's in this php block
	$thread_owner = mysqli_query($conn,"
	SELECT 
	thread_id,
	thread_creator,
	category_id
	FROM threads
	WHERE thread_id = $thread_id
	");

	while($row = mysqli_fetch_array($thread_owner, MYSQLI_ASSOC)){
		$category_id = $row['category_id'];
	}
	 
	// only allow admin and mod access to permanently deleting a thread   
	if($_SESSION['user_privilege'] == "admin"){

	}
	else if ($_SESSION['user_privilege'] == "mod"){

	} else {
	 	header("Location: ../error.php");
	}
?>

<?php
	include_once ('../header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>DELETE THREAD</h2>
		<hr/>

		<?php
		define("CATEGORY_ID", $category_id);

		// link to return to the live thread currently being edited
		echo'
		<a href="./category.php?id='.CATEGORY_ID.
		'">Return</a><br/><br/>';
		
		?>

		<?php 
		// display deletion verification message to use under return link; pass 'yes' or 'no' response back to this page 
		if (isset($_GET['d'])){
		} else { 
		?>
		Are you sure you want to permanently delete this thread and all posts associated with it?<br>
		You will be redirected back to the category page after you delete a thread.<br/><br/>
		<a href = "p_deletet.php?id=<?php echo $thread_id .'&d=yes'; ?> ">Yes</a>&nbsp;	&nbsp;	&nbsp;
		<a href = "p_deletet.php?id=<?php echo $thread_id .'&d=no'; ?> ">No</a>
		<br/><br/>
		<?php

		}?>

		<?php
		// begin php block for deletetion of provided thread

		// $_GET['d'] is set when the user selects "yes" or "no" to whether they wish to delete the thread or not
		if(isset($_GET['d'])){ 
			// variable for whether they wish to delete the thread or not, given a value of "yes" or "no"
			// typecast data passed through url to a string for further security
			$delete = (string)$_GET['d'];

			// BEGIN "if" for user clicking "yes" on the verification of whether they want to delete the thread or not
			if($delete == 'yes'){ 
				echo 'Commence delete. Click return to navigate back to the category.'; 

				// typecast current thread ID obtained from the url for further security
				$current_thread = (int)$_GET['id'];
				
				// Determine the id of the category that this thread belongs to so that we can redirect the user to the category page once the thread deletion has been completed.
				$current_category = "SELECT category_id FROM threads WHERE thread_id = ".$current_thread;
				$result_category = mysqli_query($conn, $current_category);

				while($row = mysqli_fetch_array($result_category, MYSQLI_ASSOC)){
					$newURL = $row['category_id'];
				}

				// query to delete thread with placeholder variable used for current thread id
				$sql = "DELETE FROM threads WHERE thread_id = ?";
				
				// error check for query
				if (!$result = $conn->prepare($sql))
				{
				    die('Query failed: (' . $con->errno . ') ' . $con->error);
				}

				// bind placeholder value and the thread id that the user is currently viewing
				// the 'i' indicates that the value is an integer
				// i = integer / d = double / s = string
				if (!$result->bind_param('i', $current_thread))
				{
				    die('Binding parameters failed: (' . $result->errno . ') ' . $result->error);
				}

				// execute deletion query
				if (!$result->execute())
				{
				    die('Execute failed: (' . $result->errno . ') ' . $result->error);
				}

				// check if the delete query execution was a success ...
				if ($result->affected_rows > 0)
				{

				} else {echo 'An error occured while deleting the thread.'; exit ();}

				// prepare to execute all posts associated with this thread  
				// query to delete thread with placeholder variable used for current thread id
				$sql = "DELETE FROM posts WHERE post_thread_id = ?";
				
				// error check for query
				if (!$result = $conn->prepare($sql))
				{
				    die('Query failed: (' . $con->errno . ') ' . $con->error);
				}

				// bind placeholder value and the thread id that the user is currently viewing
				// the 'i' indicates that the value is an integer
				// i = integer / d = double / s = string
				if (!$result->bind_param('i', $current_thread))
				{
				    die('Binding parameters failed: (' . $result->errno . ') ' . $result->error);
				}

				// execute query
				if (!$result->execute())
				{
				    die('Execute failed: (' . $result->errno . ') ' . $result->error);
				}

				// check if the delete query execution was a success 
				// if the deletion was a success, then end the page here so that the page does not
				// attempt to display the thread information again.
				if ($result->affected_rows > 0)
				{
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
				    echo "<br/><br/>Couldn't delete the thread ID or associated posts (you will get this message if you perma-delete a thread that has no posts associated with it.)"; // for when the query fails for any reason
				}

			} // END "if" for user clicking "yes" on the verification of whether they want to delete the thread or not

			// if user selects no to the question if whether they really wanted to delete the post or not, then display this:
			elseif($delete == 'no'){
				echo 'Stop delete. Click return if you wish to navigate away from thread deletion.';
			}
		}
		
		?>

		<?php
		// Begin display the thread that the user is looking to delete
		if(isset($_GET['id'])){
			// get thread id from the url, typecast it to an int for security
			$current_thread = (int)$_GET['id'];

			// query to display the thread the user wishes to delete
			$query_thread = " 
			SELECT 
			a.thread_id,
			a.thread_title,
			a.thread_desc,
			a.thread_creator,
			a.thread_date,
			a.category_id,
			a.view_counter,
			a.locked,
			b.user_id,
			b.username,
			b.date_joined,
			b.user_privilege,
			b.post_count
			FROM threads a, users b
			WHERE a.thread_creator = b.username
			AND thread_id = $current_thread
			"; 
			
			// execute the query
			$result_thread = mysqli_query($conn, $query_thread);
			 
			// assign data obtained from query to variables and display the thread post
			// that the user is looking to delete.
			while($row = mysqli_fetch_array($result_thread, MYSQLI_ASSOC)){
				$thread_id = $row['thread_id'];
				$thread_title = $row['thread_title'];
				$thread_desc = $row['thread_desc'];
				$thread_creator_id = $row['user_id'];
				$thread_creator = $row['username'];
				$thread_date = $row['thread_date'];
				$user_privilege = $row['user_privilege'];
				$poster_postcount = $row['post_count'];
				$category_id = $row['category_id'];
				$locked = $row['locked'];
				$view_counter = $row['view_counter'];

				// echo thread information
				echo 
				'<div class = "thread_info"><div class="padding">';

				echo'
				<b><h1>' . $thread_title . '</h1></b>' .
				'<br><div class = "user_avatar"></div>' .
				'<br><a href = "profile.php?id=' .$thread_creator_id.'">'. $thread_creator .'</a>
				<br>Rank: '. $user_privilege .
				'<br>Posts: ' . $poster_postcount .
				'<br><br>' . nl2br(stripcslashes($thread_desc)) .
				'</div></div>';
			}
		}
		
		?>

	</div>
</section>


<?php
	include_once '../footer.php';
?>