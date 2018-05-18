<?php
// This file is for logging in on mobile devices, seeing how I didn't want to include the login form in the dropdown menu at the top while  the browser is at mobile scale. You are also redirected to this file if your login attempt fails while using the form found in the universal header displayed at the top of each page.

include_once ('header.php');
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		<h2>LOGIN</h2>
		<hr/>
		<center>
		<?php
		if(!isset($_GET['login'])){
			$_GET['login'] = "";
			$warning_message = "Please enter login username and password.";
			echo $warning_message;
		}

		$warning = $_GET['login'];
		if ($warning == 'empty'){
			$warning_message = "Please enter login username and password.";
			echo $warning_message;
		} elseif ($warning == 'error'){
			$warning_message = "Invalid username.";
			echo $warning_message;
		} elseif ($warning == 'ipass'){
			$warning_message = "Invalid password. Try again.";
			echo $warning_message;
		} else $warning_message = "Please enter login username and password.";
			
		?>

		<form class="signup-form" action="includes/login.inc.php" method="POST"><br/>
			<input type="text" name="username" maxlength="35" placeholder="username"><br/>
			<input type="password" name="password" maxlength="70" placeholder="password"><br/>
			<button type="submit" name="submit">Login</button>
		</form>
		</center>

	</div>
</section>


<?php
include_once 'footer.php';
?>
