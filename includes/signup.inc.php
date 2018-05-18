<?php
// check if the email that the user is attempting to register with already exists in the database
// reload the signup page and display an error message if it does already exist
if (isset($_POST['submit']))
{
	include_once 'dbh.inc.php';

	$theemail = mysqli_real_escape_string($conn, $_POST['email']);

	$query = "SELECT user_email FROM users WHERE user_email = '$theemail' LIMIT 1";

	$result = $conn->query($query);

	if(mysqli_num_rows($result) > 0) {
		// redirect is email entered exists already
		header('Location:../signup.php?sign=eexist');
		exit();
	}

} 

// check if the username that the user is attempting to register with already exists in the database
// reload the signup page and display an error message if it does already exist
if (isset($_POST['submit']))
{
	include_once 'dbh.inc.php';

	$theusername = mysqli_real_escape_string($conn, $_POST['username']);

	$query = "SELECT username FROM users WHERE username = '$theusername' LIMIT 1";

	$result = $conn->query($query);

	if(mysqli_num_rows($result) > 0) {
		// redirect is username entered exists already
		header('Location:../signup.php?sign=uexist');
		exit();
	}
} 

// if both the username and the email are valid for registration, then begin the registration process
if (isset($_POST['submit']))
{
	// Attempt MySQL server connection
	include_once 'dbh.inc.php';

	// sql injection / xss attack prevention by escaping input sent by form submit
	$first = mysqli_real_escape_string($conn, $_POST['first']);
	$last = mysqli_real_escape_string($conn, $_POST['last']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = mysqli_real_escape_string($conn, $_POST['pass']);
	$username = mysqli_real_escape_string($conn, $_POST['username']);

	// verify that form inputs are not empty
	// if the fields are empty, then reload the signup page and display an error
	if(empty($first) || empty($last) || empty($email) || empty($password) || empty($username))
	{
		header('Location:../signup.php?sign=empty');
		exit();
	} else 

	{		
			// check if the characters entered for the username field (first & last) are valid
			// reload the signup page with an error if the characters are invalid
			if(!preg_match("/^[a-zA-Z]*$/", $first) || !preg_match("/^[a-zA-Z]*$/", $last) )
			{
				header("Location:../signup.php?sign=iname");
				exit();
			} else 

			{		
					// validate the email entered by the user
					// reload the signup page with an error if the email is invalid
					if(!filter_var($email, FILTER_VALIDATE_EMAIL))
					{
						header("Location:../signup.php?sign=iemail");
						exit();
					} else

					{		
		      			// if  the email is valid, then go ahead and continue on to inserting the new
		      			// user in to the database

						// hasing password for safe storage in the database
						$hashed_pass = password_hash($password,PASSWORD_BCRYPT);
						// insert user into database PREPARE STATEMENT
						$sql = "INSERT INTO users (user_first, user_last, user_email, user_pass, username, date_joined) VALUES (?, ?, ?, ?, ?, ?);";
						$stmt  = mysqli_stmt_init($conn);
						if(!mysqli_stmt_prepare($stmt, $sql)){
							$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
						} else {
							// set time zone before determining thse current EST (eastern time zone)
							date_default_timezone_set("America/New_York");
							$date = date('m/d/Y h:i:s a', time());
							// bind placeholders to data obtained from user submitted info from POST
							// i = integer / d = double / s = string
							mysqli_stmt_bind_param($stmt, "ssssss", $first, $last, $email, $hashed_pass, $username, $date);
							// run prepared database insertion
							mysqli_stmt_execute($stmt);
							$message = "Register successful.";
							// pass sign=success for display of successful registration back to the registration page
							header("Location: ../signup.php?sign=success");
						}

						exit();
					}
			}
	}
}


?>