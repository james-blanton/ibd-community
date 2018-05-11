<?php
    include_once "../country.php";
    include_once "../condition.php";
    include_once "../header.php";
?>

<section class="main-container">
	<div class="main-wrapper">
        <div class="inner_padding">
		<h2>USER PROFILE</h2>
        <hr>

        <?php
        // database connection file
        include_once "../includes/dbh.inc.php";

        // get category id from the url for later use
        // mysql_real_escape causing an error ... 
        $current_user = $_GET['id']; 

        // display message if user navigates to a profile that doesnt exist, include footer, end page
        $select_current_thread = "SELECT user_id FROM users WHERE user_id = '$current_user'";
        $result = mysqli_query($conn, $select_current_thread);
        $resultCheck = mysqli_num_rows($result);
        if ($resultCheck < 1){
            echo 'The user does not exist. Return to forum <a href="index.php" />index</a>.<br/>';
            include_once "../footer.php";
            exit();
        }
        // end redirect user away from category that does not exist
        ?>

		<a action="action" onclick="window.history.go(-1); return false;">Return</a><br><br>

		<?php
        // Attempt MySQL server connection
		include_once "../includes/dbh.inc.php";
		
        // get the user id from the url
		$user_id = $_GET['id'];

        // datbase query for user information using id from url
		$query = "SELECT * FROM users WHERE user_id=".$user_id;

		// run query
		$result= mysqli_query($conn, $query);

        // redirect user if they try to view a user profile that does not exist 
        $count = mysqli_num_rows($result);
        if ($count < 1){
            $path = $_SERVER['DOCUMENT_ROOT'];
            $path .= "/error.php";
            header("Location: $path");
        }

		// assign profile information to variables
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
		}
		?>

		<?php echo '<h1>' . $username . '</h1><br>'?>

        <?php
        $user_id = $_GET['id'];
        $profile_pic = mysqli_query($conn, "SELECT * FROM profile_pics WHERE user_id = $user_id");
        
        while($row = mysqli_fetch_array($profile_pic, MYSQLI_ASSOC)){
            $profilepic = $row['filename'];
            $approval = $row['approved'];
        }
        ?>
        <div class = "profile_pic">

        <?php
        if(isset($approval)){
            if($approval == true){
            echo '<img src="../img/user_pics/'.$profilepic.'" >';
            } else echo '<img src="../img/user_pics/default.jpg" >';
        } else echo '<img src="../img/user_pics/default.jpg" >';
        ?>

        </div>
		<!-- display user information in verticle table -->
        <table id="horizontal" >
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
                    <th colspan="3">Primary Condition</th>
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
// update last user viewed

if(isset($_SESSION['username'])){
    $viewer = $_SESSION['username'];
    $sql = "UPDATE users SET last_viewed = ? WHERE user_id = ?";
    $stmt = mysqli_stmt_init($conn);
    if (!mysqli_stmt_prepare($stmt, $sql)){
        // failed to update recently viewed
    } else {
        mysqli_stmt_bind_param($stmt, "sd", $viewer, $user_id);
        mysqli_stmt_execute($stmt);
    }
}

?>

<?php
   include_once '../footer.php';
?>
