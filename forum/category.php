<?php
// verify that GET is numeric
if(is_numeric($_GET['id']) == FALSE){
	header("Location: ../error.php");
	exit();
}
?>

<?php
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

		// get category id from the url for later use
		// typecast data obtained from url for inject protection
		$current_category = (int)$_GET['id']; 

		// display message if user navigates to category that doesnt exist, include footer, end page
		$select_current_category = "SELECT cat_id FROM category WHERE cat_id = '$current_category'";
		$result = mysqli_query($conn, $select_current_category);
		$resultCheck = mysqli_num_rows($result);
		if ($resultCheck < 1){
			echo 'The category does not exist. Return to forum <a href="index.php" />index</a>.<br/>';
			include_once "../footer.php";
			exit();
		}
		?>

		<?php //return to forum index  ?>
		<a href="./index.php">Index</a><br><br>

		<?php //create thread for category user is currently viewing w/ category id obtained from the url ?>
		<a href="create_thread.php?category_id=<?php echo $current_category; ?>">Create Thread</a><br><br>

		<?php

		// database query for thread information using current category id obtained from the url
		$query_thread = " 
		SELECT 
		a.thread_id,
		a.thread_title,
		a.thread_desc,
		a.thread_creator,
		a.thread_date,
		a.total_posts,
		a.category_id,
		a.view_counter,
		a.thread_reply_date,
		a.locked,
		a.sticky,
		b.user_id,
		b.username
		FROM threads a, users b
		WHERE a.thread_creator = b.username
		AND category_id = '$current_category'
		AND deleted = 0
		ORDER BY a.thread_reply_date DESC
		"; 
		
		// performs a query on the database
		$result = mysqli_query($conn, $query_thread);
		
		// assign data for category from the query results in to an array
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
			$resultset[] = $row;
		}

		// database query for category information using current category id from url
		$query = "SELECT * FROM category WHERE cat_id =".$current_category ; 

		// performs a query on the database
		$results = mysqli_query($conn, $query);

		// echo category title at the top of the page
		while($rows = mysqli_fetch_array($results, MYSQLI_ASSOC)){
			echo '<font class = "caps" color="#01a3e0"><b>'.$rows['cat_title'].'</b></font><br><br>'; 
		}
?>
		<?php // begin display html for thread information to user  ?>

		<div class = "thread_description_header">
			Thread Description
		</div>

		<div class ="thread_posts_header">
			Post Count
		</div>

		<div class ="thread_views_header">
			Views
		</div>

		<?php // end display html for thread information to user  ?>

<?php
		// pagination: set the total number of threads to display per page
		$results_per_page = 10;
		$sql = "SELECT * FROM threads WHERE category_id = '$current_category' AND deleted = 0 ";
		// pagination: run query
		$result = mysqli_query($conn, $sql);
		// pagination: determine total number of thread that belong to this category
		$num_of_result = mysqli_num_rows($result);
		// pagination: get the total number of pages for this specific category
		$num_of_pages = ceil($num_of_result/$results_per_page);

		// determine page the user is currently on
		if(!isset($_GET['p'])){
			$page = 1;
		} else {
			$page = $_GET['p'];
		}

		// determine the sql limit starting number for the results on the displaying page
		$this_page_first_result = ($page-1)*$results_per_page;

		// Query for all thread information with a join to get username and id for display in the thread listing. The 'a.deleted' checks if the thread is marked as "temporarily" deleted or not with a 1 or 0 boolean flag in the 'threads' table. The LIMIT specification in this query is for the range of records to show according to our pagination code found above.
		$sql = "SELECT 
				a.thread_id,
				a.thread_title,
				a.thread_desc,
				a.thread_creator,
				a.thread_date,
				a.total_posts,
				a.category_id,
				a.view_counter,
				a.thread_reply_date,
				a.locked,
				a.deleted,
				a.sticky,
				b.user_id,
				b.username
				FROM threads a, users b
				WHERE a.thread_creator = b.username
				AND category_id = '$current_category'
				AND a.deleted = 0
				ORDER BY (a.sticky = 1) DESC, a.thread_reply_date DESC
				LIMIT " . $this_page_first_result . ',' . $results_per_page
				; 

		// execute query to get threads pertaining to the category currently being viewed
		$result = mysqli_query($conn, $sql);
		while($row = mysqli_fetch_array($result)){

		// A query to calculate out the number of posts in the database pertaining to each particular thread. When posts are "deleted" by a user they are also marked in the database as "temporarily" deleted with a 1 or 0 boolean flag.
		$query_posts = "
		SELECT 
		a.post_id,
		a.post_content,
		a.post_date,
		a.post_thread_id,
		a.post_creator,
		a.deleted,
		b.user_id,
		b.username,
		b.date_joined,
		b.user_privilege,
		b.post_count
		FROM posts a, users b
		WHERE a.post_creator = b.username
		AND a.deleted = 0
		AND post_thread_id = ". $row['thread_id']
		;

		// Execute query to count number of posts belonging to each thread
		$result_posts = mysqli_query($conn, $query_posts);
		$post_count = mysqli_num_rows($result_posts);
		// We need to count the original thread posters message, so we add 1 because the original post is currently in the 'threads' table and not the 'posts' table.
		$post_count = $post_count + 1;

		?>
					<div id = "thread_wrapper">
						<div id = "thread_container">

							<div class = "thread_header">

								<div class ="thread_title">
								<?php
									if($row['sticky'] == 1){
										echo '<font color="#01a3e0" style="float:left;">•&nbsp;</font> ';
									}
								?>
								<font style="text-transform: uppercase; font-weight: bold">
									<a href="thread.php?id=<?php echo $row['thread_id']; ?>"><?php echo $row['thread_title']; ?></a>
								</font>
								by <a href = "profile.php?id=<?php echo $row['user_id']; ?>"><?php echo $row['thread_creator']; ?></a>
								</div>
							</div>

							<div class = "thread_description"><?php echo stripcslashes(mb_strimwidth($row['thread_desc'], 0, 75, "...")); ?>
							<div id="category_reply_date">-- Date: <?php echo $row['thread_reply_date'] ?></div></div><br>
						</div>

						<div class ="thread_posts">
							<?php echo $post_count; ?>
						</div>

						<div class ="thread_views">
							<?php echo $row['view_counter']; ?>
						</div>
					</div>
					
		<?php
		}

		if($num_of_pages > 1){
			echo '<div id="pagination_links"> PAGE: &nbsp;';
			// displays links for pagination of threads
			for($page=1;$page<=$num_of_pages;$page++){
				echo '<a href="category.php?id=' .$category_id.'&p='. $page . '">'. $page . '</a> &nbsp; &nbsp;';
			}
			echo '</div>';
		}
		        
		?>
	</div>
</section>

<?php
	include_once '../footer.php';
?>
