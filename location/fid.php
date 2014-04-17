<?php


	$id = $_REQUEST['fid']?$_REQUEST['fid']:0; 
	$page = $_REQUEST['page']?$_REQUEST['page']:0; 


	$id = DB::result_first("SELECT fid FROM ".DB::table('forum_forum_lephonefid')." WHERE lephonefid='$id'");


	$location = "http://127.0.0.1/discuzx31/forum.php?mod=forumdisplay&fid=$id&page=$page";
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$location);
	die();

?>