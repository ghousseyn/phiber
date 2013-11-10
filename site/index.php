<?php

include "../library/main.class.php";
$main = main::getInstance();


/*
$conf = $main->load('config');


try{
	$db = $main->load('db',$conf);
	
}catch(Exception $e){
	echo $debug;
	print $e->getMessage();
}
*/
//$db->reset();

//$db->insert(array("entries",array("name" => "guettaf","fname" => "hussein","street" => "my street","zip" => "32000","city" => 2)));
//$db->update(array("entries",array("name" => "guettaf", "fname" => "nour", "city" => 5), "id=3"));
//$db->delete(array("entries",array("id=2")));
//$db->select(array("entries as en", array("en.id", "en.name","en.fname","en.street","en.zip", "city.name as city"), " LEFT JOIN city ON en.city = city.id"));
//print_r($db);
//echo $conf->_dbuser;
?>
<br />
<pre>
<?php 
$main->run();

//$route = $main->getRoute();
//echo $main->dispatch();
//echo "Module: ".$route['module']."<br />";
//echo "Controller: ".$route['controller']."<br />";
//echo "Action: ".$route['action']."<br />";
//$debug->stackPush(__file__);
//var_dump($main->get("config")->_dbhost);
//echo $debug;






?>
</pre>
