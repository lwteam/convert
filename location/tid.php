<?php


	$id = $_REQUEST['tid']?$_REQUEST['tid']:0; 
	$page = $_REQUEST['page']?$_REQUEST['page']:0;


	$thread =DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE tid='$id'");

	$id = DB::fetch_first("SELECT tid FROM ".DB::table('forum_thread_lephonetid')." WHERE lephonetid='$id'");


	$fid = DB::result_first("SELECT  tid FROM ".DB::table('forum_thread_lephonetid')." WHERE lephonetid='{$thread['fid']}'");


	$location = "http://127.0.0.1/discuzx31/forum.php?mod=viewthread&fid=$fid&tid=$id&page=$page";
	header('HTTP/1.1 301 Moved Permanently');
	header('Location: '.$location);
	die();

?>