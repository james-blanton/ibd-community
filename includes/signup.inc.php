<?php
/////////// ERROR CHECKS ////////////////

// CHECK IF EMAIL ALREADY EXISTS
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
// END CHECK IF EMAIL ALREADY EXISTS

// CHECK IF USERNAME ALREADY EXISTS
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
// END CHECK IF USERNAME ALREADY EXISTS

/////////// REGISTER USER ///////////////

if (isset($_POST['submit']))
{
	// database connection file
	include_once 'dbh.inc.php';

	// injection protection escape input from form
	$first = mysqli_real_escape_string($conn, $_POST['first']);
	$last = mysqli_real_escape_string($conn, $_POST['last']);
	$email = mysqli_real_escape_string($conn, $_POST['email']);
	$password = mysqli_real_escape_string($conn, $_POST['pass']);
	$username = mysqli_real_escape_string($conn, $_POST['username']);

	if(empty($first) || empty($last) || empty($email) || empty($password) || empty($username))
	{
		// REDIRECT IF USER DIDNT ENTER ONE OF THE VALUES
		header('Location:../signup.php?sign=empty');
		exit();

	} else 

	{		
			// CHECK IF CHARACTERS ARE VALID FOR NAME
			if(!preg_match("/^[a-zA-Z]*$/", $first) || !preg_match("/^[a-zA-Z]*$/", $last) )
			{
				header("Location:../signup.php?sign=iname");
				exit();

			} else 

			{		

					// CHECK IF EMAIL IS VALID
					if(!filter_var($email, FILTER_VALIDATE_EMAIL))
					{
						header("Location:../signup.php?sign=iemail");
						exit();
					} else

					{		
		      			// IF THEY DONT EXIST, INSERT NEW USER IN TO DATABASE

						// hasing password
						$hashed_pass = password_hash($password,PASSWORD_BCRYPT);
						// insert user into database PREPARE STATEMENT
						$sql = "INSERT INTO users (user_first, user_last, user_email, user_pass, username, date_joined) VALUES (?, ?, ?, ?, ?, ?);";
						$stmt  = mysqli_stmt_init($conn);
						if(!mysqli_stmt_prepare($stmt, $sql)){
							$message = "ERROR: Could not able to execute sql. " . mysqli_error($conn);
						} else {
							date_default_timezone_set("America/New_York");
							$date = date("Y-m-d");
							// bind the data to the placeholders in order to prepare for insert in to database
							mysqli_stmt_bind_param($stmt, "ssssss", $first, $last, $email, $hashed_pass, $username, $date);
							// run insert 
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