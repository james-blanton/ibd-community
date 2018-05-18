<?php
// set session if one isn't already set
if (session_status() == PHP_SESSION_NONE) {
		session_start();
}

// check if user is logged in
// redirect to error page if they are not
if(!isset($_SESSION['username'])){
 	header("Location:error.php");
}
?>

<?php
	// Attempt MySQL server connection
	include_once "includes/dbh.inc.php";

	// get user id passed in url 
	// ensure  that user id passed in url matches the user id of the active session
	$current_user = $_GET['id'];

	// verify that GET is numeric
	if(is_numeric($_GET['id']) == FALSE){
		header("Location: error.php");
		exit();
	}

	$update_user = mysqli_query($conn, "
	SELECT 
	user_id
	FROM users
	WHERE user_id = $current_user
	");
	 
	// fetch results or query and place user id in to a variable      
	while($row = mysqli_fetch_array($update_user, MYSQLI_ASSOC)){
		$the_user = $row['user_id'];
	}

	// redirect user away if they're attempting to edit
	// an avatar for an account that is not theirs
	if($_SESSION['user_id'] != $the_user){
	 	header("Location: error.php");
	}

?>

<?php
	// include universal header file
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
// upload_img.php (the file that processes the form for avatar / profile image submission)
// gives feeback to this file alerting the user if the avatar / profile image upload failed
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
// this small php query checks to see if the user currently has a profile image entry in the datbase table
// this database contains the following rows: id (primary key), filename ...
// (what the file name became after upload_img.php renamed the file to a unique name),
// the unique user_id  for the user who submitted the image, an approved / unapproved boolean and 
// and upload date
$profile_pic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $current_user");
        
while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
	$profilepic = $row['filename'];
	$approval = $row['approved'];
}
?>

<div class = "profile_pic">
	<?php
	// if the current profile picture / avatar image has been approved by admins / mods,
	// then go ahead and display it above the form for submitting a new image
	// else display the default profile picture file
	if(isset($approval)){
		if($approval == true){
			echo '<img src="./img/user_pics/'.$profilepic.'" >';
		} else echo '<img src="./img/user_pics/default.jpg" >';
	} else echo '<img src="./img/user_pics/default.jpg" >';
	?>
</div>

<form action="upload_img.php?id=<?php echo $_SESSION['user_id'] ?>" method="POST" enctype="multipart/form-data">
	<input type="file" name="file" style="padding:0px;"><br/><br/>
	<button type="submit" name="submit">UPLOAD</button>
</form>

<?php
   $path = 'footer.php';
   include_once $path;
?>