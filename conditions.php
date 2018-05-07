
<?php
include_once 'header.php';
?>

<section class="main-container">
	<div class="main-wrapper">
		<div class="inner_padding">
			<h2>CONDITIONS</h2>
			<hr/>

			<div class="conditions_inner">
				<a href="?c=croh">CHROHN'S DISEASE</a>
				<a href="?c=ulc">ULCERATIVE COLITIS</a>
				<a href="?c=ibs">IRRITABLE BOWEL</a>
				<a href="?c=more">MORE</a>
			</div>

			<?php
			if(isset($_GET['c'])){
				if($_GET['c'] == 'croh'){
					echo 'chrons info';
				}
				elseif($_GET['c'] == 'ulc'){
					echo 'ulc info';
				}
				elseif($_GET['c'] == 'ibs'){
					echo 'ibs info';
				}
				elseif($_GET['c'] == 'more'){
					echo 'more info';
				} else echo 'You are on the conditions page.';
			}
			?>
				
	</div>
</section>


<?php
include_once 'footer.php';
?>
