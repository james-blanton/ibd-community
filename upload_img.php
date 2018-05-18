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
// If form is submitted from edit_avatar.php, then process the image submittion.
if (isset($_POST['submit'])){
	// Typecast user's username for security purposes
	$user_id = (int)$_GET['id'];

	// verify that GET is numeric
	if(is_numeric($_GET['id']) == FALSE){
		header("Location: error.php");
		exit();
	}

	// Not sure if it's better to get the user id from the url or from a session variable check
	// $user_id = $_SESSION['user_id'];

	// Attempt MySQL server connection
	include_once 'includes/dbh.inc.php';

	// Query to check if user has already uploaded a profile image
	$query = "SELECT user_id FROM profile_pics WHERE user_id = '$user_id' LIMIT 1";
	$result = $conn->query($query);

	// If query doesnt return any results from the profile_pics table, then continue on to processing the image
	if( (mysqli_num_rows($result)) == 0) {

		if(isset($_FILES['file']['name'])){
			$fileName = $_FILES['file']['name'];
			$fileTmp_name = $_FILES['file']['tmp_name'];
			$fileSize = $_FILES['file']['size'];
			$fileError = $_FILES['file']['error'];
			$fileType = $_FILES['file']['type'];

			// get the file extension
			$fileExt = explode('.', $fileName);
			// change file extention name to all lower case lettters
			$fileActualExt = strtolower(end($fileExt));
			// array for what type of files extensions are permitted for profile images
			$allow = array('jpg','jpeg','png'); 

				// if the file of the correct extentension, then allow upload
				if(in_array($fileActualExt, $allow)){
					if($fileError === 0){
						// if the file is less than 1000 KB
						if($fileSize < 1000000){

							$current_user = $_GET['id'];

							// creates new name for file to give it a unique name before upload
							// i need to put an sql query in place here that ENSURES the file name is unique
							$fileNameNew = uniqid('', true).".".$fileActualExt;

							// this query and the following two rows check to make sure the user doesn't already have a profile image uploaded:
							$file_name_check= "SELECT filename FROM profile_pics WHERE filename = '$fileNameNew' LIMIT 1";
							$result_name_check = $conn->query($file_name_check);

							// if the query returned no results (the user has no image uploaded yet)
							if( (mysqli_num_rows($result_name_check)) == 0) { 
								
								$fileDestination = 'img/user_pics/'.$fileNameNew;
								$fileTmp_name = $_FILES['file']['tmp_name'];

								// set the time zone to eastern time before generating the current time
								date_default_timezone_set("America/New_York");
								$today = date("Y-m-d");

								// check image mime type
								$mimetype = mime_content_type($_FILES['file']['tmp_name']);
								if(in_array($mimetype, array('image/jpeg', 'image/gif', 'image/png'))) {
									// do nothing if mime type is correct
								} else {
								    header("Location:edit_avatar.php?id=".$user_id."&msg=mime");
								}

								// calculate the files width and height in pixels 
								$image_info = getimagesize($fileTmp_name);
								$image_width = $image_info[0];
								$image_height = $image_info[1];

								// if the image is exactly 150 x 150 pixels, then proceed with the upload
								if($image_width == "150" || $image_width == "150"){
									// file gets uploaded with new file name
									move_uploaded_file($fileTmp_name, $fileDestination);

									// Attempt MySQL server connection
									include 'includes/dbh.inc.php';

									// add file to profile pictures table with new temporary name
									$sql = "INSERT INTO profile_pics (filename, user_id, upload_date) VALUES ('$fileNameNew', $current_user, '$today')";

									if (mysqli_query($conn, $sql)) {
									    echo "New record created successfully";
									    header("Location:edit_avatar.php?id=".$user_id."&msg=success");
									} else {
									    header("Location:edit_avatar.php?id=".$user_id."&msg=failed");
									}
								} else { header("Location:edit_avatar.php?id=".$user_id."&msg=wrong_size");}
							} else { header("Location:edit_avatar.php?id=".$user_id."&msg=name_exists");}
						} else { header("Location:edit_avatar.php?id=".$user_id."&msg=file_size");}
					} else { header("Location:edit_avatar.php?id=".$user_id."&msg=error"); }
				} else {  header("Location:edit_avatar.php?id=".$user_id."&msg=type"); }
		} else { header("Location:edit_avatar.php?id=".$user_id."&msg=nofile"); }
	} else { header("Location:edit_avatar.php?id=".$user_id."&msg=already_has"); }
}
?>