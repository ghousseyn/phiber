<html>
<head>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script>
$(document).ready(function(){
	$("#click").click(function(){
		$.get('index/index',function(resp){
			alert(resp);
		});
	});
});
</script>
</head>
<body>
<?php
include $this->content;

echo $this->debug;

?>
</body>
</html>
