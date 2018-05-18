<?php
// verify that GET is numeric
if(is_numeric($_GET['id']) == FALSE){
    header("Location: ../error.php");
    exit();
}
?>

<?php
    // file includes list of countries in an array
    include_once "../country.php";
    // file includes list of conditions in an array
    include_once "../condition.php";
    // include universal header file
    include_once "../header.php";
?>

<section class="main-container">
	<div class="main-wrapper">
        <div class="inner_padding">
		<h2>USER PROFILE</h2>
        <hr>

        <?php
        // Attempt MySQL server connection
        include_once "../includes/dbh.inc.php";

        // get category id from the url for later use
        // typecast data obtained from url for inject protection
        $current_user = (int)$_GET['id']; 

        // display message if user navigates to a profile that doesnt exist, include footer, end page
        $select_current_thread = "SELECT user_id FROM users WHERE user_id = '$current_user'";
        // run query
        $result = mysqli_query($conn, $select_current_thread);
        // count number of rows returned by query
        $resultCheck = mysqli_num_rows($result);
        // display error and end page is no query results were returned
        if ($resultCheck < 1){
            echo 'The user does not exist. Return to forum <a href="index.php" />index</a>.<br/>';
            include_once "../footer.php";
            exit();

            // i might  eventually make this redirect to the error page instead
            // header("Location: ../error.php");
        }

        ?>

		<a action="action" onclick="window.history.go(-1); return false;">Return</a><br><br>

		<?php
        // Attempt MySQL server connection
		include_once "../includes/dbh.inc.php";
		
        // get the user id from the url
        // typecast data obtained from url for inject protection
		$user_id = (int)$_GET['id'];

        // datbase query for user information using user id from url
		$query = "SELECT * FROM users WHERE user_id=".$user_id;

		// run query
		$result= mysqli_query($conn, $query);

		// assign profile information in to variables
		while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
		$user_id = $row['user_id'];
		$username = $row['username'];
		$user_email = $row['user_email'];
		$user_firstName = $row['user_first'];
		$user_lastName = $row['user_last'];
		$user_privilege = $row['user_privilege'];
		$user_post_count = $row['post_count'];
		$date_joined = $row['date_joined'];
        $penpal = $row['penpal'];
        $birthday = $row['birthday'];
        $country = $row['country'];
        $condition1 = $row['condition1'];
        $condition2 = $row['condition2'];
        $condition3 = $row['condition3'];
        $last_viewed = $row['last_viewed'];
        $introduction = $row['introduction'];
        $ip = $row['ip'];
		}
		?>

		<?php echo '<h1>' . $username . '</h1><br>'?>

        <?php
        // typecast data obtained from url for inject protection
        $user_id = (int)$_GET['id'];
        // query to obtain profile pic file name and whether it's approved for display on the forum or not
        $profile_pic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $user_id");
        
        while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
            $profilepic = $row['filename'];
            $approval = $row['approved'];
        }
        ?>
        <div class = "profile_pic">

        <?php
        // if the profile pic approved, then go ahead and display it to the user
        // if not, then display the default profile pic file
        if(isset($approval)){
            if($approval == true){
            echo '<img src="../img/user_pics/'.$profilepic.'" >';
            } else echo '<img src="../img/user_pics/default.jpg" >';
        } else echo '<img src="../img/user_pics/default.jpg" >';
        ?>

        <?php 
        // Attempt MySQL server connection
        include_once "../includes/dbh.inc.php";
        
        // get the user id from the url
        // typecast data obtained from url for inject protection
        $user_id = (int)$_GET['id'];

        // query to obtain users ip address in order to display if they are banned or not
        $query = "SELECT ip FROM users WHERE user_id=".$user_id;
        // run query
        $result= mysqli_query($conn, $query);
        // place ip in to a variable 
        while($row = mysqli_fetch_array($result, MYSQLI_ASSOC)){
        $ip = $row['ip'];
        }

        // check and see if the users ip is in the banned user table or not 
        $sql = "SELECT * FROM banned WHERE ip = '$ip'";
        $result = $conn->query($sql);

        if (mysqli_num_rows($result)!=0){
        // display banned message on profile if they're banned
            echo '<div class = "pm_link">';
            echo 'Banned.<br/>';
            echo '</div>';
        }
        ?>

        <div class = "pm_link">
            <a href="../new_conversation.php?uname=<?php echo $username; ?>" />Send PM</a>
        </div>

        <?php
        // dont allow admins to be banned by mods
        if($user_privilege != "admin"){
        if(isset($_SESSION['user_privilege'])){
        if($_SESSION['user_privilege'] != "admin" || $_SESSION['user_privilege'] != "mod"){
        ?>
        <div class = "pm_link">
            <a href="../ban_user.php?id=<?php echo $user_id; ?>" />Ban User</a>
        </div>

        <div class = "pm_link">
            <a href="../unban_user.php?id=<?php echo $user_id; ?>" />unBan User</a>
        </div>
        <?php
        }
        }
        }
        ?>

        </div>
		<!-- display user information in verticle table -->
        <table id="horizontal">
            <thead>
                <tr>
                    <th colspan="3">First Name</th>
                </tr>
                <tr>
                    <th colspan="3">Last Name</th>
                </tr>
                <tr>
                    <th colspan="3">User Rank</th>
                </tr>
                <tr>
                    <th colspan="3">Email</th>
                </tr>
                <tr>
                    <th colspan="3">Post Count</th>
                </tr>
                <tr>
                    <th colspan="3">Date Joined</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo $user_firstName; ?></td>
                </tr>
                <tr>
                    <td><?php echo $user_lastName; ?></td>
                </tr>
                <tr>
                    <td><?php echo $user_privilege; ?></td>
                </tr>
                <tr>
                    <td>
                        <input type="hidden" value="<?php echo $user_email; ?>" id="myInput">
                        <a href="#" onclick="copyToClipboard('#userEmail')">Click to copy</a>
                        <p id="userEmail" style="display:none; "><?php echo $user_email; ?></p>
                    </td>
                </tr>
                 <tr>
                    <td><?php echo $user_post_count; ?></td>
                </tr>
                <tr>
                    <td><?php echo $date_joined; ?></td>
                </tr>
            </tbody>

            <thead>
                <tr>
                    <th colspan="3">Main Condition</th>
                </tr>
                <tr>
                    <th colspan="3">Country</th>
                </tr>
                <tr>
                    <th colspan="3">Birthday</th>
                </tr>
                <tr>
                    <th colspan="3">Age</th>
                </tr>
                <tr>
                    <th colspan="3">Penpal Status</th>
                </tr>
                <tr>
                    <th colspan="3">Last Visitor</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo $condition1; ?> 
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $country; ?> 
                    </td>
                </tr>
                <tr>
                    <td><?php if($birthday!=NULL && $birthday !="0000-00-00"){echo $birthday;} else {echo 'Not provided.';} ?></td>
                </tr>
                <tr>
                    <td>
                    <?php 
                    if($birthday!=NULL && $birthday!="0000-00-00"){
                        echo date_diff(date_create($birthday), date_create('today'))->y; 
                    } else {echo 'N/A.';}
                    ?></td>
                </tr>
                <tr>
                    <td><?php if($penpal==1 ){echo 'Yes';}else{echo 'No';} ?></td>
                </tr>
                 <tr>
                    <td><?php echo $last_viewed; ?></td>
                </tr>
            </tbody>

            <thead>
                <tr>
                    <th colspan="3">Second Condition</th>
                </tr>
                <tr>
                    <th colspan="3">Third Condition</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <?php echo $condition2; ?> 
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php echo $condition3; ?> 
                    </td>
                </tr>
            </tbody>

        </table>

        <div id = "profile_intro">
            <b>Introduction:</b>
            <hr/>
            <?php echo nl2br(strip_tags(stripcslashes($introduction))); ?> 
         </div>

	</div>
</section>

<?php
// update last logged in user who viewed this profile in the users table
if(isset($_SESSION['username'])){
    $viewer = $_SESSION['username'];
    $sql = "UPDATE users SET last_viewed = ? WHERE user_id = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)){
        // failed to update recently viewed
    } else {
        // bind placeholders to data obtained from user submitted info from POST
        // i = integer / d = double / s = string
        mysqli_stmt_bind_param($stmt, "si", $viewer, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

?>

<?php
    // include universal footer file
   include_once '../footer.php';
?>
