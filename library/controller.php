<?php
namespace Codup;
class controller extends main {

    function sendJSON($arr)
    {
        if(!is_array($arr)){
	    return false;
	}
	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Mon, 16 Jul 1997 02:00:00 GMT');
	header('Content-type: application/json; charset=utf-8');
	echo json_encode($arr);
	exit;
    }
    function disableLayout(){
	$this->register('layoutEnabled', false);
    }

}
