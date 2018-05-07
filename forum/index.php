
<?php include_once ('../header.php'); ?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2 style="clear:both;">FORUM</h2>
		<hr>

		<?php
		// database connection file
		include_once "../includes/dbh.inc.php";

		// Check connection
		if ($conn->connect_error) {
		    die("Connection failed: " . $conn->connect_error);
		} 

		$sql = "SELECT cat_id, cat_title, cat_desc, locked FROM category"; 
		$result = $conn->query($sql);

		if ($result->num_rows > 0) {
		    // output data of each row
		    while($row = $result->fetch_assoc()) {
			// assign category information for each row to variables
			$cat_id = $row['cat_id'];
			$cat_title = $row['cat_title']; 
			$cat_desc = $row['cat_desc']; 
			$locked = $row['locked'];

			// count the numbr of existing threads that pertain to each category
			// each thread is asssigned the id of the category to which it belongs
			// when the thread is submitted in to the database
			$query = "SELECT thread_id FROM threads WHERE category_id = $cat_id";
			$result_threads = mysqli_query($conn, $query);
			$total_threads = mysqli_num_rows($result_threads); 

			// each category info for each cycle of the foreach loop
			echo '
			<div class = "forum_cat_wrap">
			<div class = "category_header"><a href="category.php?id=' . $row["cat_id"] . '">'.$row["cat_title"].'</a>
			<div id="category_count" >Total Threads: ' . $total_threads . '</div></div>' . 
			'<div class = "category_disc">'.$cat_desc.'</div>
			</div>
			<br>';
			}
		}
		
		?>
	</div>
</section>


<?php
	include_once '../footer.php';
?>
