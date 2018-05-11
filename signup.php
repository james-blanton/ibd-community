<?php
include_once 'header.php';
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
		
		<h2>SIGNUP</h2><hr/>
		<center>
		<?php
		if(!isset($_GET['sign'])){
			$_GET['sign'] = "";
			$warning_message = "Please enter signup information.<br/><br/>";
			echo $warning_message;
		}

		$warning = $_GET['sign'];
		if ($warning == 'empty'){
			$warning_message = "Please enter signup information.<br/><br/>";
			echo $warning_message;
		} elseif ($warning == 'success'){
			$warning_message = "Successful signup you may now log in.<br/><br/>";
			echo $warning_message;
		} elseif ($warning == 'eexist'){
			$warning_message = "Email already in use.<br/><br/>";
			echo $warning_message;
		} elseif ($warning == 'uexist'){
			$warning_message = "Username already in use.<br/><br/>";
			echo $warning_message;
		} elseif ($warning == 'iname'){
			$warning_message = "Invalid characters in username.<br/><br/>";
			echo $warning_message;
		} elseif ($warning == 'iemail'){
			$warning_message = "Invalid email.<br/><br/>";
			echo $warning_message;
		} else $warning_message = "Please enter signup information.<br/><br/><br/><br/>";
		?>

		<form class="signup-form" action="includes/signup.inc.php" method="POST">
			<input type="text" name="first" placeholder="Firstname" maxlength="35"></br>
			<input type="text" name="last" placeholder="Lastname" maxlength="35"></br>
			<input type="text" name="email" placeholder="Email" maxlength="50"></br>
			<input type="text" name="username" placeholder="Username" maxlength="35"></br>
			<input type="password" name="pass" placeholder="Password" maxlength="70"></br>
			<button type="submit" name="submit">Sign Up</button>
		</form>
		<center>
	</div>
</section>


<?php
include_once 'footer.php';
?>
