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
// redirect user away from this page if they attempt to edit a thread that was not created by them
	// Attempt MySQL server connection
	include_once "../includes/dbh.inc.php";
	// typecast data obtained from url for inject protection
	$thread_id = (int)$_GET['id'];

	// query to obtain the creator of the thread
	$thread_owner = mysqli_query($conn,"
	SELECT 
	thread_id,
	thread_creator,
	category_id
	FROM threads
	WHERE thread_id = $thread_id
	");
	 
	// get the username for the thread owner        
	while($row = mysqli_fetch_array($thread_owner, MYSQLI_ASSOC)){
		$the_thread_owner = $row['thread_creator'];
		$category_id = $row['category_id'];
	}

	// check if logged in user is the owner of the thread, an admin or a forum moderator
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
	include_once ('../header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>DELETE THREAD</h2>
		<hr/>

		<?php
		// Create link to navigate back to the threads's parent category
		// we want to keep this echo here so that the return link stays above the error message display.
		// This needs to be a constant so that it will still be available once we have deleted the thread.
		define("CATEGORY_ID", $category_id);

		echo'
		<a href="./category.php?id='.CATEGORY_ID.
		'">Return</a><br/><br/>';
		
		?>

		<?php 
		// display deletion verification message to use under return link; pass 'yes' or 'no' response back to this page 
		if (isset($_GET['d'])){
		} else { 
		?>
		Are you sure you want to delete your thread?<br>
		You will be redirected back to the category page after you delete a thread.<br/><br/>
		<a href = "deletet.php?id=<?php echo $thread_id .'&d=yes'; ?> ">Yes</a>&nbsp;	&nbsp;	&nbsp;
		<a href = "deletet.php?id=<?php echo $thread_id .'&d=no'; ?> ">No</a>
		<br/><br/>
		<?php

		}?>

		<?php
		// $_GET['d'] is set when the user selects "yes" or "no" to whether they wish to delete the post or not. This value is either a "yes" or "no".
		if(isset($_GET['d'])){ 
			$delete = $_GET['d'];
			// If the user clicking "yes" on the verification of whether they want to delete the post or not, then execute the following code to mark the post as deleted with a boolean flag of 1.
			if($delete == 'yes'){ 
				echo 'Commence delete.'; 
				// Typecast for extra security
				$current_thread = (int)$_GET['id'];

				// query to soft-delete the post with boolean flag
				// a placeholder variable used for current post id
				$sql = "UPDATE threads SET deleted = 1 WHERE thread_id = ?";
				
				// error check for query
				if (!$result = $conn->prepare($sql))
				{
				    die('Query failed: (' . $con->errno . ') ' . $con->error);
				}

				// bind placeholders to data obtained from user submitted info from POST
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

				// check if the delete query execution was a success ...
				if ($result->affected_rows > 0)
				{
					//return user to category after a successful delete
					echo $message = "Thread deletion successful.<br /><br /> <a href='./category.php?id=".CATEGORY_ID."'>Return to the category by clicking here.</a><br/><br/>";

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
				    echo "Couldn't delete the thread ID."; 
				}

			} // END if statement for selection "yes" to the deletion verification question.

			// if user selects no to the question if whether they really wanted to delete the post or not, then display this:
			elseif($delete == 'no'){
				echo 'Stop delete. Click return if you wish to navigate away from thread deletion.';
			}
		}
		?>

		<?php
		// prepare to display the thread that the user is looking to delete
		if(isset($_GET['id'])){
			// get post id typecast data obtained from url for inject protection
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
			
			// run query and place returned data in to variables
			$result_thread = mysqli_query($conn, $query_thread);
			 
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

				// dsplay the thread information to the user
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
	// include universal footer file
	include_once '../footer.php';
?>