<?php
// include universal header file
include_once "../header.php";
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>FORUM</h2>
		<hr>

		<?php
		// Attempt MySQL server connection
		include_once "../includes/dbh.inc.php";

		// typecast data obtained from url for inject protection 
		$current_thread = (int)$_GET['id']; 

		// display message if user navigates to category that doesnt exist, include footer, end page
		$select_current_thread = "SELECT thread_id FROM threads WHERE thread_id = '$current_thread' AND deleted = 0";

		// run query
		$result = mysqli_query($conn, $select_current_thread);

		// check number of results returned by query
		$resultCheck = mysqli_num_rows($result);

		// display error message if no results are returned
		if ($resultCheck < 1){
			echo 'The thread does not exist. Return to forum <a href="index.php" />index</a>.<br/>';
			include_once "../footer.php";
			exit();
		}
		?>

		<?php
		// Attempt MySQL server connection
		include_once "../includes/dbh.inc.php";
		// typecast data obtained from url for inject protection
		$current_thread = (int)$_GET['id'];

		// Begin pagination and display of posts
		$results_per_page = 10;
		$sql = "SELECT * FROM posts WHERE post_thread_id = '$current_thread' AND deleted = 0";
		// run query
		$result = mysqli_query($conn, $sql);
		// determine total number of thread that belong to this category
		$num_of_result = mysqli_num_rows($result);
		// get the total number of pages for this specific category
		$num_of_pages = ceil($num_of_result/$results_per_page);
		// get current category id typecast data obtained from url for inject protection
		$category_id = (int)$_GET['id'];

		// determine page the user is currently on
		if(!isset($_GET['p'])){
			$page = 1;
			(int)$_GET['p'] = 1;
		} else {
			$page = (int)$_GET['p'];
		}

		// determine the sql limit starting number for the results on the displaying page
		$this_page_first_result = ($page-1)*$results_per_page;

		// query for required post information
		// using the thread ID obtained from the URL and GET method
		// boolean flag of 'deleted' indicates if the post has already been marked as deleted or not
		$query_posts = "
		SELECT 
		a.post_id,
		a.post_content,
		a.post_date,
		a.post_thread_id,
		a.post_creator,
		a.post_date,
		a.deleted,
		b.user_id,
		b.username,
		b.date_joined,
		b.user_privilege,
		b.post_count
		FROM posts a, users b
		WHERE a.post_creator = b.username
		AND a.post_thread_id = '$current_thread'
		AND a.deleted = 0
		LIMIT " . $this_page_first_result . ',' . $results_per_page
		;
		
		// run query
		$result_posts = mysqli_query($conn, $query_posts);
		 
		// assign data for posts to an array        
		while($row = mysqli_fetch_array($result_posts, MYSQLI_ASSOC)){
			$post_rank = $row['user_privilege'];
			$resultset[] = $row;
		}

		// query for  required thread info ...
		// using the thread ID obtained from the URL using GET method
		// boolean flag of 'deleted' indicates if the post has already been marked as deleted or not
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
		a.deleted,
		a.sticky,
		b.user_id,
		b.username,
		b.date_joined,
		b.user_privilege,
		b.post_count
		FROM threads a, users b
		WHERE a.thread_creator = b.username
		AND thread_id = '$current_thread'
		AND deleted = 0
		"; 

		// run query for thread info on the thread the viewer is currently viewing
		$result_threads = mysqli_query($conn, $query_thread);

		// assign thread information to variables
		while($row = mysqli_fetch_array($result_threads, MYSQLI_ASSOC)){
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
		}

		// get thread creator profile picture
		// if the profile pic approved, then go ahead and display it to the user
			// if not, then display the default profile pic file
		$thread_profilePic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $thread_creator_id");
		while($row = mysqli_fetch_array($thread_profilePic, MYSQLI_ASSOC)){
            $t_profilepic = $row['filename'];
            $t_profilepic_approval = $row['approved'];
        }

		// add one view to the thread when page refreshes
		if(isset($view_counter)){	
		$new_view_count = $view_counter + 1;
		$sql_views = mysqli_query($conn, "UPDATE threads SET view_counter=$new_view_count WHERE thread_id=$current_thread");
		} else {
		    echo "Error updating record: " . $conn->error;
		}

		// back link to return to the category page
		echo '<a href = "./category.php?id=' . $category_id . '" >Return</a><br><br>';

		// create post pass thread id
		echo '<a href = "./create_post.php?id=' . $current_thread . '" >Create Post</a><br><br>';

		// echo first post belonging to the thread only if the user is viewing the first page
		// of the thread, which is determined by the pagination function. $_GET['p'] indicates what page is being viewed
		if($_GET['p'] == 1){

			// echo original thread post and author's user information
			echo 
			'<div class = "thread_info"><div class="padding">';

			// only display edit / delete link if the active session is that of the user who created the thread, an admin, or a forum moderator
			if(isset($_SESSION['username'])){
			if($_SESSION['username'] == $thread_creator || $_SESSION['user_privilege'] == "admin"){
				echo '
				<div class = "edit_link">
				<a href = "edit_thread.php?id=' .$thread_id.'">Edit</a>
				</div>';

				echo '
				<div class = "edit_link">
				<a href = "deletet.php?id=' .$thread_id.'&c='.$category_id.'">Delete</a>
				</div>';
			}
			}

			if(isset($_SESSION['username'])){
			if($_SESSION['user_privilege'] == "admin" || $_SESSION['user_privilege'] == "mod"){
				echo '
				<div class = "edit_link">
				<a href = "p_deletet.php?id=' .$thread_id.'&c='.$category_id.'">PermDelete</a>
				</div>';
			}
			}

			echo
			$thread_date .'
			<b><h1>' . $thread_title . '</h1></b>' .

			// if the profile pic approved, then go ahead and display it to the user
			// if not, then display the default profile pic file
			'<br><div class = "user_avatar">';
			if(isset($t_profilepic_approval)){
	            if($t_profilepic_approval == true){
	            echo '<img src="../img/user_pics/'.$t_profilepic.'" >';
	            } else echo '<img src="../img/user_pics/default.jpg" >';
	        } else echo '<img src="../img/user_pics/default.jpg" >';
			echo '
			</div>' .

			// display thread creator's user information above the post content
			'<br><a href = "profile.php?id=' .$thread_creator_id.'">'. $thread_creator .'</a>
			<br>Rank: '. $user_privilege .
			'<br>Posts: ' . $poster_postcount .
			'<br><br>' . nl2br(stripcslashes($thread_desc)) .
			'</div></div>';

		}

		// thread reply header text
		echo 'THREAD REPLIES:<br><br>';

		// asssign post info to variables and echo it out
		// only attempt to echo post if query returned posts belonging to this thread

		if(!empty($resultset)){
		foreach($resultset as $row): 
			$post_id = $row['post_id'];
			$post_creator = $row['username']; 
			$post_content = $row['post_content'];
			$post_count = $row['post_count']; 
			$post_creator_id = $row['user_id'];
			$user_privilege = $row['user_privilege'];
			$post_date = $row['post_date'];

			// get post creator profile picture
			$post_profilePic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $post_creator_id");
			while($row = mysqli_fetch_array($post_profilePic, MYSQLI_ASSOC)){
	            $p_profilepic = $row['filename'];
	            $p_profilepic_approval = $row['approved'];
	        }

			echo '
			<div class = "response_post_header"><div class="padding">';

			// only display edit / delete link if the active session is that of the user who created the post, an admin, or a forum moderator
			if(isset($_SESSION['username'])){
				if($_SESSION['username'] == $post_creator || $_SESSION['user_privilege'] == "admin"){
				echo '
				<div class = "edit_link">
				<a href = "edit_post.php?id=' .$post_id.'&t='.$thread_id.'">Edit</a>
				</div>';

				echo '
				<div class = "edit_link">
				<a href = "deletep.php?id=' .$post_id.'&t='.$thread_id.'">Delete</a>
				</div>';
				}
			}
			
			// if the profile pic approved, then go ahead and display it to the user
			// if not, then display the default profile pic file
			echo 
			$post_date.'
			<br><br><div class = "user_avatar">';
			if(isset($p_profilepic_approval)){
	            if($p_profilepic_approval== true){
	            echo '<img src="../img/user_pics/'.$p_profilepic.'" >';
	            } else echo '<img src="../img/user_pics/default.jpg" >';
        	} else echo '<img src="../img/user_pics/default.jpg" >';

        	// display post creator's user information above the post content
			echo '
			</div>
			<br><a href = "profile.php?id=' .$post_creator_id.'">'. $post_creator .'</a>
			<br>Rank: '. $user_privilege .
			'<br>Posts: '.$post_count.'
			</div>
			';

			echo '</div><div class = "response_post_content"><div class="padding">' . nl2br(stripcslashes($post_content)) . '</div></div>';
		endforeach;
		} else {
			echo 'This thread has no posts yet.';
		}

		// used to pagination list at the bottom of the page
		if($num_of_pages > 1){
			echo '<br/><br/><div id="pagination_links"> PAGE: &nbsp;';
			// displays links for pagination of threads
			for($page=1;$page<=$num_of_pages;$page++){
				echo '<a href="thread.php?id=' .$thread_id.'&p='. $page . '">'. $page . '</a> &nbsp; &nbsp;';
			}
			echo '</div>';
		}	        
		?>
	</div>
</section>


<?php
   include_once '../footer.php';
?>
