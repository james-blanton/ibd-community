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
        $condition = $row['conditions'];
		}
		?>

		<?php echo '<h1>' . $username . '</h1><br>'?>

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
                        <?php 
                        // using the condition abbreviation from the database
                        // as the key in the array found in condition.php file
                        // to find the full name of the condition
                        if ($conditions_array[$condition] == "\nSELECT OPTION"){
                            echo "Not provided.";
                        } else {
                        echo $conditions_array[$condition];
                        }
                        ?> 
                    </td>
                </tr>
                <tr>
                    <td>
                        <?php 
                        // using the country abbreviation from the database
                        // as the key in the array found in country.php file
                        // to find the full name of the country
                        if ($countries[$country] == "\nSELECT OPTION"){
                            echo "Not provided.";
                        } else {
                        echo $countries[$country];
                        }
                        ?> 
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
                    <td><?php echo 'Coming soon.'; ?></td>
                </tr>
            </tbody>
        </table>

	</div>
</section>


<?php
   include_once '../footer.php';
?>
