<?php
// Turn on all error reporting
// ini_set('error_reporting', E_ALL);

//set timezone for all forum dates / times
date_default_timezone_set('US/Eastern');

// GET THE NAME OF CURRENT DIRECTORY
// This is used to create absolute urls that will work no matter where I upload this project.
function getDirectory(){
	$path = DIRNAME($_SERVER['PHP_SELF']);
	$position = STRRPOS($path,'/') + 1;
	$current = SUBSTR($path,$position);
	return $current;
}

// grab the url fpr the page the user is currently on by calling function
$current_dir = getDirectory();

// php function to generate some of the head navigation urls
// im doing this to make the code a little cleaner and easier to understand
function generateURL($url, $name){
	// scope makes this neccessary 
	$current_dir = getDirectory();

	// actually generate the url
	echo "
	<a href='";
		
	if($current_dir == 'forum'){
		echo '../'.$url;
	} else {echo $url;}
		
	echo "'>".$name."</a>
	";	
}

?>

<?php 
// Start session for all pages if one isn't set already
// Attempt MySQL server connection
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
// regenerate session id for security
session_regenerate_id();

// database connection file
include_once "includes/dbh.inc.php";
?>

<!DOCTYPE html>

<html>
<header>


	<!-- my css ... tabs_style.css is for the information boxes that overlay the header image on the index page-->
	<?php
	// ensure that stylesheet is included on all pages, even if you're in a subdirectory, such as /forum/
	if ($current_dir == "forum"){
   	$path = "../main_style.css";
   
	echo '<link rel="stylesheet" type="text/css" href="'.$path.'">';
	} else {
	$path = "main_style.css";
	echo '<link rel="stylesheet" type="text/css" href="'.$path.'">';
	}
	?>
	<link rel="stylesheet" href="tabs_style.css"> 

	<!-- other css -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<!-- scripts -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
	<script src="includes/jquery-3.3.1.min.js"></script>

	<script type="text/javascript">
	// used to copy email of use on profile page
	function copyToClipboard(element) {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($(element).text()).select();
		document.execCommand("copy");
		$temp.remove();
	}

	//used for menu dropdown on mobile scale 
	function mobileDropdown() {
	    var x = document.getElementById("mobile_nav_bars");
	    if (x.style.display === "none") {
	        x.style.display = "block";
	    } else {
	        x.style.display = "none";
	    }
	}
	</script>
	
	<title></title>
</header>


<body>
	<!-- BEGIN header TITLE banner -->
	<nav class ="banner-top">
		<!-- bann-top is the small portion at the top for phone number on desktop version ... this is hidden on mobile version-->
	</nav>

	<nav class ="banner-main">

	<div class="pTitle"><?php generateURL('index.php','IB Support Center') ?></div>
	<!-- END header TITLE banner -->
	
	<!-- MOBILE VERSION begin navigation -->
	<nav id="myHeader_mobile">
		<div id = "mobile_icon"><a href="#" onclick="mobileDropdown()">&#9776;</a></div>
		<div id ="mobile_nav_bars" style="display:none;">
			<!-- display username & edit account link if user is logged in php block below-->
			<?php if(isset($_SESSION['username'])){ ?>
			<div id="mobile_nav_bar">
				<a href="

				<?php 
				$id = $_SESSION['user_id'];
				if($current_dir == 'forum'){
					echo '../edit_account.php?update='.$id;
				} else {echo 'edit_account.php?update='.$id;}
				?>

				">
				<?php echo $_SESSION['username']; ?></a>
			</div>
			<?php } ?>

			<div id="mobile_nav_bar"><?php generateURL('index.php','HOME') ?></div>

			<div id="mobile_nav_bar"><?php generateURL('conditions.php','CONDITIONS') ?></div>

			<div id="mobile_nav_bar"><?php generateURL('help.php','HELP') ?></div>

			<div id="mobile_nav_bar">
				<a href="
				<?php 
				if($current_dir == 'forum'){
					echo 'index.php';
				} else {echo 'forum/index.php';}
				?>
				">FORUM</a>
			</div>

			<div id="mobile_nav_bar">

			<?php
					// echo logout link if the user is logged in
					// echo login link / register link if they're not logged in
					if(isset($_SESSION['username'])){
						generateURL('includes/logout.inc.php','Logout');
					} else {
						generateURL('login.php','LOGIN');

						echo'
						<div id="mobile_nav_bar"  class="answer_list" >';
						generateURL('signup.php','SIGNUP');
						echo '
						</div>
						';
					}

				?>

			</div>
		</div>
	</nav>
	<!-- MOBILE VERSION end navigation -->


	</nav> <!-- end banner-main -->

	<nav class="myHeader">
		<div class="main-wrapper">

			<div id="navbar">
				<!-- DESKTOP NAVIGATION begin main left navigation (excluding conditions dropdown) -->
				
				<?php generateURL('index.php','INDEX') ?> 

				<?php generateURL('help.php','HELP') ?>

				<?php
				echo '
				<a href="
				';
				if($current_dir == 'forum'){
					echo 'index.php';
				} else {echo 'forum/index.php';}
				echo'
				">FORUM</a>
				';
				?>
				<!-- DESKTOP NAVIGATION end main navigation (excluding conditions dropdown) -->

				<!-- DESKTOP NAVIGATION begin login / signup forms  ... nav-login div -->
				<div class="nav-login">
					<?php
						// echo logout link if the user is logged in
						// echo login form / register link if they're not logged in
						if(isset($_SESSION['username'])){
							generateURL('includes/logout.inc.php','Logout');
						} else {
							// full login form on header bar for desktop version of navigation
							if($current_dir == 'forum'){
								$url= '../includes/login.inc.php';
							} else {$url= 'includes/login.inc.php';}
							

							echo '
							<form action="'.$url.'" method="POST">
								<input type="text" name="username" placeholder="username">
								<input type="password" name="password" placeholder="password">
								<button type="submit" name="submit">Login</button>
							</form>';

							generateURL('signup.php','SIGN UP');
						}
					?>
				</div>
				<!-- DESKTOP NAVIGATION end login / signup forms ... nav-login div -->

				<!-- DESKTOP NAVIGATION begin 'edit account' navigation ... nav-edit_account div -->
				<div class="nav-edit_account">
					<?php if(isset($_SESSION['username'])){  ?>
					<a href = "

						<?php
						$id = $_SESSION['user_id'];
						if($current_dir == 'forum'){
							$url= '../edit_account.php?update='.$id;
						} else {$url= 'edit_account.php?update='.$id;}
						echo $url
						?>

					 " >Edit Account</a>
					 <?php } ?>
				</div> 
				<!-- DESKTOP NAVIGATION end 'edit account' navigation ... nav-edit_account div -->

				<div class="nav-username">
					<?php 
					// echo the logged in user's username if they are in fact logged in
					if(isset($_SESSION['username'])){ 
						echo $_SESSION['username'];
					} 
					?>
				</div>

				<!--DESKTOP NAVIGATION NEGIN medical conditions dropdown -->
				<div class="dropdown">

					<button class="dropbtn"><a href="<?php 
						if($current_dir == 'forum'){
							$url= '../conditions.php';
						} else {$url= 'conditions.php';}
						echo $url
					 ?>">CONDITIONS <i class="fa fa-caret-down"></i></a>
					</button>
					<div class="dropdown-content">
						<?php 
						echo '
						<a href="'.$url.'?c=croh">CHROHNS DISEASE</a>
						<a href="'.$url.'?c=ulc">ULCERATIVE COLITIS</a>
						<a href="'.$url.'?c=ibs">IRRITABLE BOWEL</a>
						<a href="'.$url.'?c=more">MORE</a>
						';
						?>
					</div>
				</div> <!-- DESKTOP NAVIGATION end medical conditions dropdown --> 

			</div> <!-- DESKTOP NAVIGATION end nav bar -->
		</div> <!-- DESKTOP NAVIGATION end main-wrapper  -->
	</nav> <!-- DESKTOP NAVIGATION end myHeader -->

<!-- end FULL navigation navigation -->

<!--login / logout success message display under navigation bar on desktop -->
<?php if (isset($_GET['login'])){ ?>
<div id = "login_message">
	<?php 
	if($_GET['login'] == "success"){
		echo 'Successfully logged in.';
	} else if ($_GET['login'] == "out") {echo 'Logout successful.';}

	?>
</div>
<?php } ?>

<script>
// for sticky navigation on page scroll
// needs to appear in code below header
window.onscroll = function() {StickyNav()};

var navbar = document.getElementById("navbar");
var sticky = navbar.offsetTop;

function StickyNav() {

  if (window.pageYOffset >= sticky) {
    navbar.classList.add("sticky")
  } else {
    navbar.classList.remove("sticky")
  }
  if (window.pageYOffset <= sticky) {
  	navbar.classList.remove("sticky")
  }
}
</script>
