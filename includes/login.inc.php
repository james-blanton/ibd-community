<?php
// helps prevent session hijacking
// this may cause issues later
// session_set_cookie_params(time()+600,'/','localhost',false,true);
ini_set('display_errors', '1');error_reporting(E_ALL);
// neccessary session start
session_start();

// if user has submit  form
if(isset($_POST['submit'])){
	include 'dbh.inc.php';

	// injection protection escape input
	$username = mysqli_real_escape_string($conn, $_POST['username']);
	$pass = mysqli_real_escape_string($conn, $_POST['password']);

	if(empty($username) || empty($pass)){
		// redirect if the user doesn't fill in forms
		header('Location:../login.php?login=empty');
		exit();
	} else {
		$sql = "SELECT * FROM users WHERE username = '$username'";
		$result = $conn->query($sql);

		if (mysqli_num_rows($result)!=0){ // causes the redirect else statement if  the username doesnt exist
			while ($row = $result->fetch_assoc()) {
				// check here if the passwords match, else, redirect
				$hashedPwdCheck = password_verify($pass, $row['user_pass']);
				if($hashedPwdCheck == false){
					// redirect if password is wrong
					header('Location:../login.php?login=ipass');
					exit();
				} elseif ($hashedPwdCheck == true) {

				// if we're in this block, then the password was correct
				// log in the user here by  setting session variables
				// redirect to index page and pass login=success to display successful login message to user
				$_SESSION['user_id'] = $row['user_id'];
				$_SESSION['username'] = $row['username'];
				$_SESSION['first_name'] = $row['user_first'];
				$_SESSION['last_name'] = $row['user_last'];
				$_SESSION['user_email'] = $row['user_email'];
				$_SESSION['user_privilege'] = $row['user_privilege'];
				header('Location:../index.php?login=success');

				} // end hashed password check successful
			}
		} else {header('Location:../index.php?login=failed');}
	}
}

 else {
	// REDIRECT IF USER TRIES TO ACCESS page w/o clicking submit
	header('Location:../index.php?login=error');
	exit();
}