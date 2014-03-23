<!DOCTYPE HTML>
<html>
<head>
	<title>Phiber Framework</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<link rel="stylesheet" type="text/css" href="/css/ivory.css" media="all">

	<!-- For Date picker only -->
	<link href="/css/jquery-ui.css" rel="stylesheet" type="text/css"/>
	<link href="/css/hint.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="/js/jquery.min.js"></script>
	<script src="/js/jquery-ui.js"></script>
	<script src="/js/js.js"></script>
	<script>
	  $(document).ready(function() {
	    $("#datepicker1").datepicker();
		$("#datepicker2").datepicker();

	  });
	</script>
	<!-- For Date picker only -->

	<style>
		.content{width: 100%; height: auto; background-color: #EBEAE8; padding: 30px 12px;}
	.note {
		background-color: #ffffff;
		padding: 10px 0;
		color: #333333;
				border-radius:5px;
		   -moz-border-radius:5px;
		-webkit-border-radius:5px;
				box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.2);
	       -moz-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.2);
		-webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, 0.2);
	}
	</style>

</head>
<body>

	<div class="row text-center">
		<img src="/logo.png" alt="Phiber FRAMEWORK" />
	</div>
	<div class="content">
	<div class="grid">
		<div class="row space-top space-bot">
			<h3 class="text-center"><?php echo $this->header ?></h3>
		</div>
		<div class="row space-bot">
			<div class="c12">
				<div class="note">
				   <p class="note-content">
					<?php
						include $this->content;



					?>
				   </p>

				</div>
			</div>
		</div>

	</div><!-- grid -->

		<?php
			echo $this->debuginfo;
		?>

	</div>
</body>
</html>
