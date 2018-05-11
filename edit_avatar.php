<?php
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:../error.php");
}
?>

<?php
   include_once 'country.php';
?>

<?php
	// Attempt MySQL server connection
	include_once "includes/dbh.inc.php";

	$current_user = $_GET['id'];
	// redirect user away if they attempt to edit an account that is not theirs
	$update_user = mysqli_query($conn, "
	SELECT 
	user_id
	FROM users
	WHERE user_id = $current_user
	");
	// run query
	//$the_post_owner = mysqli_query($conn, $post_owner);
	 
	// get the username for the thread owner        
	while($row = mysqli_fetch_array($update_user, MYSQLI_ASSOC)){
		$the_user = $row['user_id'];
	}

	if($_SESSION['user_id'] != $the_user){
		$path = "index.php";
	 	header("Location: $path");
	}

?>

<?php
   $path = "header.php";
   include_once $path;
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>EDIT ACCOUNT</h2>
		<hr/>
		<a href="edit_account.php?update=<?php echo $current_user ?>">Return to profile</a><br/><br/>

<?php
if(isset($_GET['msg'])){
	if($_GET['msg']=="success"){
		echo "Image upload successful. Awaiting approval.<br/><br/>";
	}
	else if($_GET['msg']=="failed"){
		echo "Upload failed.<br/><br/>";
	}
	else if($_GET['msg']=="wrong_size"){
		echo "Image must be 150px X 150px.<br/><br/>";
	}
	else if($_GET['msg']=="name_exists"){
		echo "Temporary file name already exists ... oddly enough.<br/><br/>";
	}
	else if($_GET['msg']=="file_size"){
		echo "The physical file size is too large.<br/><br/>";
	}
	else if($_GET['msg']=="error"){
		echo "Extension not okay.<br/><br/>";
	}
	else if($_GET['msg']=="type"){
		echo "Error determining file type.<br/><br/>";
	}
	else if($_GET['msg']=="nofile"){
		echo "Enter a file before you hit submit.<br/><br/>";
	}
	else if($_GET['msg']=="already_has"){
		echo "You already have a profile image.<br/><br/>";
	}
}
?>

<?php
// block for profile pic
$profile_pic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $current_user");
        
while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
	$profilepic = $row['filename'];
	$approval = $row['approved'];
}
?>
<div class = "profile_pic">

<?php
if(isset($approval)){
	if($approval == true){
		echo '<img src="./img/user_pics/'.$profilepic.'" >';
	} else echo '<img src="./img/user_pics/default.jpg" >';
} else echo '<img src="./img/user_pics/default.jpg" >';
?>

<form action="upload_img.php?id=<?php echo $_SESSION['user_id'] ?>" method="POST" enctype="multipart/form-data">
	<input type="file" name="file"><br/>
	<button type="submit" name="submit">UPLOAD</button>
</form>

</div> <!-- end profile pic div-->






<?php
   $path = 'footer.php';
   include_once $path;
?>