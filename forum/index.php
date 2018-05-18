
<?php include_once ('../header.php'); ?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2 style="clear:both;">FORUM</h2>
		<hr>

		<?php
		// Attempt MySQL server connection
		include_once "../includes/dbh.inc.php";

		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		} 

		// query to get forum category information
		$sql = "SELECT cat_id, cat_title, cat_desc, locked FROM category"; 
		$result = $conn->query($sql);

		// check if category list was returned, display error if it wasn't
		if ($result->num_rows > 0) {
		    // output data for each category row
		    while($row = $result->fetch_assoc()) {
			// assign category information for each row to variables
			$cat_id = $row['cat_id'];
			$cat_title = $row['cat_title']; 
			$cat_desc = $row['cat_desc']; 
			$locked = $row['locked'];

			// Count the numbr of existing threads that pertain to each category;
			// each thread is asssigned the id of the category to which it belongs
			// when the thread is submitted in to the database.
			// Threads can be marked as deleted, so don't count any deleted threads in the total.
			$query = "SELECT thread_id FROM threads WHERE category_id = $cat_id AND deleted != 1";
			$result_threads = mysqli_query($conn, $query);

			if($result_threads === 0){
				$total_threads = 0;
			} else {
				$total_threads = mysqli_num_rows($result_threads); 
			}

			// each category info for each cycle of the foreach loop
			echo '
			<div class = "forum_cat_wrap">
			<div class = "category_header"><a href="category.php?id=' . $row["cat_id"] . '">'.$row["cat_title"].'</a>
			<div id="category_count" >Total Threads: ' . $total_threads . '</div></div>' . 
			'<div class = "category_disc">'.$cat_desc.'</div>
			</div>
			<br>';
			}
		} else { echo 'The forum is currently unavailable for viewing.'; }
		
		?>
	</div>
</section>


<?php
	include_once '../footer.php';
?>
