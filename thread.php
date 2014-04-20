<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');
require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

require 'function.php';

$posttables = array('forum_thread','forum_attachment','forum_attachment_0','forum_attachment_1','forum_attachment_2','forum_attachment_3','forum_attachment_4','forum_attachment_5','forum_attachment_6','forum_attachment_7','forum_attachment_8','forum_attachment_9','forum_poll','forum_polloption','forum_polloption_image','forum_pollvoter','forum_post','forum_post_location','forum_postcomment','forum_postlog','forum_poststick');


$postcleartables = array('forum_thread_lephonetid','forum_post_tableid','forum_thread','forum_post','forum_attachment','forum_attachment_0','forum_attachment_1','forum_attachment_2','forum_attachment_3','forum_attachment_4','forum_attachment_5','forum_attachment_6','forum_attachment_7','forum_attachment_8','forum_attachment_9','forum_poll','forum_polloption','forum_polloption_image','forum_pollvoter','forum_post','forum_post_location','forum_postcomment','forum_postlog','forum_poststick');


$convertfups = array(255,665);
$convertlefids = array(85,87,67,71,4,13);

$fupfids = join(',',$convertfups);


$query = DB::query("SELECT f.* FROM convert_lefen.".DB::table('forum_forum')." f 
	WHERE f.status='1' AND (f.fup IN ($fupfids) || f.fid IN ($fupfids) ) AND f.type IN ('forum','group') ");

while($forum = DB::fetch($query)) {
	$opfids[] = $forum['fid'];
	if ($forum['type'] == 'forum') {
		$suball =  DB::fetch_all("SELECT f.* FROM convert_lefen.".DB::table('forum_forum')." f 
				WHERE f.status='1' AND f.fup='$forum[fid]'  AND f.type ='sub';");
		if ($suball) {
			foreach ($suball as $value) {
				$opfids[] = $value['fid'];
			}
		}
	}
}




$bugfid = 730;



$tableSameArray = array();

//比较两个表获得两2个表中相同的部分
foreach ($posttables as $table) {
	$TempAry1 = $TempAry2 = array();
	$query = DB::query("desc ".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry1[] = $value['Field'];
	}
	$query = DB::query("desc convert_lefen.".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry2[] = $value['Field'];
	}
	$tableSameArray[$table] = array_intersect($TempAry1,$TempAry2);
}


class threadconvert 
{
	function lenovothread($tid){
		global $tableSameArray,$posttables;
		$insert = $insertlen = array();
					
		foreach ($posttables as  $table) {
			DB::query("insert into ".DB::table($table)." (".join(',',$tableSameArray[$table]).") select ".join(',',$tableSameArray[$table])." from convert_lefen.".DB::table($table)." where tid ='$tid'");
		}

	}
}


ini_set('memory_limit','12800M');



$ProcessNum  = 5000;
$page = (int)$_REQUEST['page'];
$totalnum = (int)$_REQUEST['totalnum'];
$starttime = (int)$_REQUEST['starttime'];
if (!$starttime) {
	$starttime = $_G['timestamp'];
}


if ($page<2) {
	foreach ($postcleartables  as  $value) {
		DB::query("TRUNCATE TABLE ".DB::table($value));
	}
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lefen.".DB::table('forum_thread')." WHERE fid IN (".join(',',$opfids).") ORDER BY tid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}


$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lefen.".DB::table('forum_thread')." WHERE fid IN (".join(',',$opfids).") ORDER BY tid ASC LIMIT $offset,$ProcessNum");
while($thread = DB::fetch($query)) {
	if (!DB::fetch_first("SELECT *  FROM ".DB::table('forum_thread')." WHERE tid='$thread[tid]'")) {
		threadconvert::lenovothread($thread['tid']);
	}
	
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐粉主题帖子数据已经转换完毕! 将进行乐Phone.CC主题数据转换','cc_thread.php');
}

showmnextpage("乐粉主题帖子数据正在转换中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,0);


?>