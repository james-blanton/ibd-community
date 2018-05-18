<?php
// set session if one isn't already set
if (session_status() == PHP_SESSION_NONE) {
	   session_start();
}

// Attempt MySQL server connection
include 'dbh.inc.php';

// check if banned member ... start by obtaining the user's public ip address
$ip = $_SERVER['REMOTE_ADDR'];

// query banned users table to see if the users public ip address is in the table
$sql = "SELECT * FROM banned WHERE ip = '$ip'";
// run query
$result = $conn->query($sql);

// redirect the user if they are banned and stop code execution
// AKA exit if the query returns results
if (mysqli_num_rows($result)!=0){
	header('Location:../index.php?login=banned');
	exit();
}

// if user has submitted the form, then go ahead and process the form information
if(isset($_POST['submit'])){
	// Attempt MySQL server connection
	include 'dbh.inc.php';

	// sql injection / xss attack prevention by escaping input sent by form submit
	$username = mysqli_real_escape_string($conn, $_POST['username']);
	$pass = mysqli_real_escape_string($conn, $_POST['password']);

	// reload the page and display an error if the user doesn't fill in the forms
	if(empty($username) || empty($pass)){
		header('Location:../login.php?login=empty');
		exit();
	} else {
		// query the user table to see if the username exists in the database
		$sql = "SELECT * FROM users WHERE username = '$username'";
		// execute query
		$result = $conn->query($sql);

		// check if the query returned any results
		// redirect the user if the username they filled in is not in the database
		// if results are returned, then begin checking if the entered password matches the password for the provided username in the users table row
		if (mysqli_num_rows($result)!=0){ 
			while ($row = $result->fetch_assoc()) {
				// the password_verify() function verifies that a password matches a hash
				// so, here are are checking if the password submitted in the form ($pass)
				// matches up with the hashed password that was stored in the database during registration
				$hashedPwdCheck = password_verify($pass, $row['user_pass']);
				if($hashedPwdCheck == false){
					// redirect if password is wrong
					header('Location:../login.php?login=ipass');
					exit();
				} elseif ($hashedPwdCheck == true) {
				// If we reach this point, then the password  filled in to the form was correct.
				// Proceed log in the user in here by  setting session variables and
				// reload the login page to display a successful login message to the users
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['username'] = $row['username'];;
				$_SESSION['first_name'] = $row['user_first'];
				$_SESSION['last_name'] = $row['user_last'];
				$_SESSION['user_email'] = $row['user_email'];
				$_SESSION['user_privilege'] = $row['user_privilege'];

				// Update the user's IP address ine the users table every time they log in.
				// We can use this if we ever need to ban a user from accessing the website.
				$ip = $_SERVER['REMOTE_ADDR'];
				$username = $row['username'];
				$stmt = $conn->prepare("UPDATE users SET `ip`=? WHERE username=?");
				$stmt->bind_param("ss", $ip, $username);
				$stmt->execute();

				header('Location:../index.php?login=success');

				} // end hashed password check successful
			}
		} else {header('Location:../index.php?login=failed');}
	}
}

 else {
	// redirect the user away from this file if it's accessed in any way
	// without having a form submit data to this file
	header('Location:../index.php?login=error');
	exit();
}