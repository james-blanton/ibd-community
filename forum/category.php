
<?php
include_once "../header.php";
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>FORUM</h2>
		<hr>

<?php
		// database connection file
		include_once "../includes/dbh.inc.php";

		// get category id from the url for later use
		// mysql_real_escape causing an error ... 
		$current_category = $_GET['id']; 

		// display message if user navigates to category that doesnt exist, include footer, end page
		$select_current_category = "SELECT cat_id FROM category WHERE cat_id = '$current_category'";
		$result = mysqli_query($conn, $select_current_category);
		$resultCheck = mysqli_num_rows($result);
		if ($resultCheck < 1){
			echo 'The category does not exist. Return to forum <a href="index.php" />index</a>.<br/>';
			include_once "../footer.php";
			exit();
		}
		// end redirect user away from category that does not exist
		?>

		<!-- return to forum index -->
		<a href="./index.php">Index</a><br><br>

		<!-- create thread for category user is currently viewing w/ category id obtained from the url -->
		<a href="create_thread.php?category_id=<?php echo $current_category; ?>">Create Thread</a><br><br>

		<?php

		// database database for thread information using current category id from url
		// old query before i wrote the join:
		// $query = "SELECT * FROM threads WHERE category_id =".$current_category; 
		// using a join to connect the thread and the user who created the thread
		// this join makes it so i don't have to track the thread posters information in the threads tables
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
		b.user_id,
		b.username
		FROM threads a, users b
		WHERE a.thread_creator = b.username
		AND category_id = '$current_category'
		ORDER BY a.thread_reply_date DESC
		"; 
		
		// performs a query on the database
		$result = mysqli_query($conn, $query_thread);
		
		// assign data for category from the query results in to an array
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
			$resultset[] = $row;
		}

		// database query for category information using current category id from url
		$query = "SELECT * FROM category WHERE cat_id =".$current_category; 

		// performs a query on the database
		$results = mysqli_query($conn, $query);

		// echo category title at the top of the page
		// &#9608 is for a "block" icon next to the category name
		while($rows = mysqli_fetch_array($results, MYSQLI_ASSOC)){
			echo '<font class = "caps" color="#01a3e0"><b>'.$rows['cat_title'].'</b></font><br><br>'; 
		}
?>
		<!-- BEGIN HTML headers for the thread information  -->

		<div class = "thread_description_header">
			Thread Description
		</div>

		<div class ="thread_posts_header">
			Post Count
		</div>

		<div class ="thread_views_header">
			Views
		</div>

		<!-- END HTML headers for the thread information  -->

<?php
		// ** BEGIN PAGINATION AND DISPLAY OF THREADS ** //
		$results_per_page = 10;
		$sql = "SELECT * FROM threads WHERE category_id = '$current_category'";
		// run query
		$result = mysqli_query($conn, $sql);
		// determine total number of thread that belong to this category
		$num_of_result = mysqli_num_rows($result);
		// get the total number of pages for this specific category
		$num_of_pages = ceil($num_of_result/$results_per_page);
		// get current category id
		$category_id = $_GET['id'];

		// determine page the user is currently on
		if(!isset($_GET['p'])){
			$page = 1;
		} else {
			$page = $_GET['p'];
		}

		// determine the sql limit starting number for the results on the displaying page
		$this_page_first_result = ($page-1)*$results_per_page;

		// query for all thread information with a join to get username and id
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
				b.user_id,
				b.username
				FROM threads a, users b
				WHERE a.thread_creator = b.username
				AND category_id = '$current_category'
				ORDER BY a.thread_reply_date DESC
				LIMIT " . $this_page_first_result . ',' . $results_per_page
				; 

		// display 5 results of threads per page for each category
		$result = mysqli_query($conn, $sql);
		while($row = mysqli_fetch_array($result)){

		// begin code for thread post count
		$query_posts = "
		SELECT 
		a.post_id,
		a.post_content,
		a.post_date,
		a.post_thread_id,
		a.post_creator,
		b.user_id,
		b.username,
		b.date_joined,
		b.user_privilege,
		b.post_count
		FROM posts a, users b
		WHERE a.post_creator = b.username
		AND post_thread_id = ". $row['thread_id']
		;
		// run query to count number of posts belonging to each thread
		$result_posts = mysqli_query($conn, $query_posts);
		$post_count = mysqli_num_rows($result_posts);
		$post_count = $post_count + 1; // need to count the original thread posters message, so we add 1

		// end code for thread post count

		?>
					<div id = "thread_wrapper">
						<div id = "thread_container">

							<div class = "thread_header">
								<div class ="thread_title">

								<a href="thread.php?id=<?php echo $row['thread_id']; ?>"><?php echo $row['thread_title']; ?></a> 
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
