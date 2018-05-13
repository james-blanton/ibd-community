<?php
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

if(!isset($_SESSION['username'])){
 	header("Location:../error.php");
}
?>

<?php

if (isset($_POST['submit'])){
$user_id = $_GET['id'];
	include_once 'includes/dbh.inc.php';
	$user_id = $_SESSION['user_id'];
	// check if user has already uploaded a profile image
	$query = "SELECT user_id FROM profile_pics WHERE user_id = '$user_id' LIMIT 1";
	$result = $conn->query($query);

	if( (mysqli_num_rows($result)) == 0) {

		if(isset($_FILES['file']['name'])){
			$fileName = $_FILES['file']['name'];
			$fileTmp_name = $_FILES['file']['tmp_name'];
			$fileSize = $_FILES['file']['size'];
			$fileError = $_FILES['file']['error'];
			$fileType = $_FILES['file']['type'];

			$fileExt = explode('.', $fileName); // get the file extension
			$fileActualExt = strtolower(end($fileExt)); // change file extention name to all lower case
			$allow = array('jpg','jpeg','png'); // tell what type of files we'll allow


				// if the file of the correct extentension, then allow upload
				if(in_array($fileActualExt, $allow)){
					if($fileError === 0){
						// if the file is less than 1000 KB
						if($fileSize < 1000000){

							$current_user = $_GET['id'];

							// creates new name for file to give it a unique name before upload
							// i need to put an sql query in place here that ENSURES the file name is unique
							$fileNameNew = uniqid('', true).".".$fileActualExt;

							// this row and the following two rows check to make sure the user doesn't already have a profile image uploaded:
							// $result_name_check = $conn->query($file_name_check);
							// mysqli_num_rows($result_name_chec)) == 0
							$file_name_check= "SELECT filename FROM profile_pics WHERE filename = '$fileNameNew' LIMIT 1";
							$result_name_check = $conn->query($file_name_check);

							if( (mysqli_num_rows($result_name_check)) == 0) { 
							
								$fileDestination = 'img/user_pics/'.$fileNameNew;
								$fileTmp_name = $_FILES['file']['tmp_name'];
								date_default_timezone_set("America/New_York");
								$today = date("Y-m-d");

								$image_info = getimagesize($fileTmp_name);
								$image_width = $image_info[0];
								$image_height = $image_info[1];

								if($image_width == "150" || $image_width == "150"){
									// file gets uploaded with new file name
									move_uploaded_file($fileTmp_name, $fileDestination);

									// add file to profile pictures table with new temporary name
									include 'includes/dbh.inc.php';
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