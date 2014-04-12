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
$fids = array(649,668,329,269,335,648,383,669,715,264,400,677,678,728,729,714,690,734,706,705,686,683,699,691,724,713,711,733,737,637,660,674,362,293,639,386,385,662,670,675,687);

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



$ProcessNum  = 1000;
$page = (int)$_REQUEST['page'];
$totalnum = (int)$_REQUEST['totalnum'];



if ($page<2) {
	foreach ($posttables  as  $value) {
		DB::query("TRUNCATE TABLE ".DB::table($value));
	}
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lefen.".DB::table('forum_thread')." WHERE fid IN (".join(',',$fids).") ORDER BY tid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}


$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lefen.".DB::table('forum_thread')." WHERE fid IN (".join(',',$fids).") ORDER BY tid ASC LIMIT $offset,$ProcessNum");
while($thread = DB::fetch($query)) {
	threadconvert::lenovothread($thread['tid']);
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐粉主题帖子数据已经转换完毕! 将进行乐Phone.CC主题数据转换','cc_thread.php');
}

showmnextpage("乐粉主题帖子数据正在转换中...".$ProcessNum*$page." / $totalnum",'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum);


?>