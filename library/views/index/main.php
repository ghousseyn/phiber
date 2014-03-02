Template file:  
<?php

echo __file__;

?>
<br/>
Examples: <a href="/dev" title="Defaults to controler index and action index">Dev</a> | <a href="/dev/index/action" title="controller index action action">Dev:action</a> | <a href="/dev/index!" title="See errors bellow">invalid</a><br/>
<?php
echo $this->text;
?>
