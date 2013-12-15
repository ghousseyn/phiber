
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8" />
	<title>WebLab - Free CSS template by ChocoTemplates.com</title>
	<link rel="shortcut icon" type="image/x-icon" href="css/images/favicon.ico" />
	<link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
	<link rel="stylesheet" href="css/flexslider.css" type="text/css" media="all" />
	
	<script src="js/jquery-1.7.2.min.js" type="text/javascript"></script>
	<!--[if lt IE 9]>
		<script src="js/modernizr.custom.js"></script>
	<![endif]-->
	<script src="js/jquery.flexslider-min.js" type="text/javascript"></script>
	<script src="js/functions.js" type="text/javascript"></script>
</head>
<body>
	<div id="wrapper">
		<!-- header -->
		<?php include 'header.php'?>
		<!-- end of header -->
		<!-- shell -->
		<div class="shell">
			<!-- main -->
			<div class="main">
				<!-- slider-holder -->
				<?php include 'slider.php'?>
				<!-- slider-holder -->
				<!-- cols -->
				<section class="cols">
					<div class="col">
						<h3 class="starter-ico"><a href="#">Starter Themes</a></h3>
						<p>Integer aliquam, quam vel tempor porta, dolor tortor cursus elit, sit amet ultrices ipsum metus quis aliquam, quam vel tempor porta</p>
						<a href="#" class="more">Read More</a>
					</div>
					<div class="col">
						<h3 class="awesome-ico"><a href="#">Awesome Colours</a></h3>
						<p>Integer aliquam, quam vel tempor porta, dolor tortor cursus elit, sit amet ultrices ipsum metus quis \aliquam, quam vel tempor porta</p>
						<a href="#" class="more">Read More</a>
					</div>
					<div class="col">
						<h3 class="save-ico"><a href="#">Save Time</a></h3>
						<p>Integer aliquam, quam vel tempor porta, dolor tortor cursus elit, sit amet ultrices ipsum metus quis aliquam, quam vel tempor porta</p>
						<a href="#" class="more">Read More</a>
					</div>

					<div class="cl">&nbsp;</div>
				</section>
				<!-- end of cols -->

				<!-- featured -->
				<?php include 'featured.php'?>
				<!-- end of featured -->
			</div>
			<!-- end of main -->
		</div>
		<!-- end of shell -->
		<div id="footer-push"></div>
	</div>	
	<!-- footer -->
	<div id="footer">
		<!-- shell -->
		<div class="shell">
			<div class="widgets">
				
				<div class="widget">
					<h4>CATEGORIES</h4>
					<ul>
						<li><a href="#">Art of Photography</a></li>
						<li><a href="#">Design Template</a></li>
						<li><a href="#">Website &amp; Development</a></li>
						<li><a href="#">How to Create a Great Layout</a></li>
						<li><a href="#">Beautiful Backgrounds</a></li>
						<li><a href="#">Customisation</a></li>
					</ul>
				</div>

				<div class="widget gallery-widget">
					<h4>GALLERY</h4>
					<ul>
						<li><a href="#"><img src="css/images/gallery-img.png" alt="" /></a></li>
						<li><a href="#"><img src="css/images/gallery-img2.png" alt="" /></a></li>
						<li><a href="#"><img src="css/images/gallery-img3.png" alt="" /></a></li>
						<li><a href="#"><img src="css/images/gallery-img4.png" alt="" /></a></li>
						<li><a href="#"><img src="css/images/gallery-img5.png" alt="" /></a></li>
						<li><a href="#"><img src="css/images/gallery-img6.png" alt="" /></a></li>
					</ul>
				</div>

				<div class="widget">
					<h4>Web Lab</h4>
					<ul>
						<li><a href="#">More About Us</a></li>
						<li><a href="#">Our Portfolio Company</a></li>
						<li><a href="#">Company Blog</a></li>
						<li><a href="#">Our Mission</a></li>
						<li><a href="#">Get in Touch with UsMore</a></li>
					</ul>
				</div>

				<div class="widget contact-widget">
					<h4>Contacts</h4>
					<p class="address-ico">
						Company Name Head Office<br />
						1234 City Name, <br />
						Country 7451
					</p>

					<p class="phone-ico">
						Phone: +11 2345 6778
						Fax: +11 2345 6789 
					</p>
					<a href="#" class="chat-btn"><span class="chat-ico"></span>Client Sheet</a>
				</div>
				<div class="cl">&nbsp;</div>
			</div>
			<!-- end of widgets -->

			<!-- footer-bottom -->
			<div class="footer-bottom">
				<!-- footer-nav -->
				<div class="footer-nav">
					<ul>
						<li class="active"><a href="#">HOME</a></li>
						<li><a href="#">ABOUT</a></li>
						<li><a href="#">SERVICES</a></li>
						<li><a href="#">PORTFOLIO</a></li>
						<li><a href="#">CONTACTS</a> </li>
					</ul>
				</div>
				<!-- end of footer-nav -->
				<p class="copy">&copy; company name. Design by <a href="http://chocotemplates.com" target="_blank">Chocotemplates.com</a></p>
			</div>
			<!-- end of footer-bottom -->
		</div>
		<!-- end of shell -->
	</div>
	<!-- end of footer -->
</body>
</html>


<?php
include $this->content;

echo $this->debuginfo;

?>