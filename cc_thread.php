<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');
require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

require 'function.php';

$posttables = array('forum_thread','forum_post','forum_attachment','forum_attachment_0','forum_attachment_1','forum_attachment_2','forum_attachment_3','forum_attachment_4','forum_attachment_5','forum_attachment_6','forum_attachment_7','forum_attachment_8','forum_attachment_9');

$postcleartables = array('forum_thread_lephonetid');

//$postcleartables = array('forum_thread_lephonetid','forum_post_tableid','forum_thread','forum_post','forum_attachment','forum_attachment_0','forum_attachment_1','forum_attachment_2','forum_attachment_3','forum_attachment_4','forum_attachment_5','forum_attachment_6','forum_attachment_7','forum_attachment_8','forum_attachment_9','forum_poll','forum_polloption','forum_polloption_image','forum_pollvoter','forum_post','forum_post_location','forum_postcomment','forum_postlog','forum_poststick');

$convertlefids = array(85,87,67,71,4,13);

$convertlefidsjoin = join(',',$convertlefids);



$tableSameArray = array();

//比较两个表获得两2个表中相同的部分
foreach ($posttables as $table) {
	$TempAry1 = $TempAry2 = array();
	$query = DB::query("desc ".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry1[$value['Field']] = $value['Field'];
	}
	$query = DB::query("desc convert_lephone.".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry2[$value['Field']] = $value['Field'];
	}
	$tableSameArray[$table] = array_intersect($TempAry1,$TempAry2);
}


class threadconvert 
{
	function lephonethread($tid){
		global $tableSameArray;

		//SHOW TABLE STATUS from convert_lephone where name='pre_ucenter_members';
		$tabstatus =  DB::fetch_first("SHOW TABLE STATUS where name='pre_forum_thread';");
		$newtid= $tabstatus['Auto_increment'];


		$thread = DB::fetch_first("SELECT * FROM convert_lephone.".DB::table('forum_thread')." WHERE `tid`='$tid'" );
		$omember = DB::fetch_first("SELECT * FROM ".DB::table('common_member_lephoneuid')." WHERE `lephoneuid`='$thread[authorid]'" );
		$forum = DB::fetch_first("SELECT * FROM ".DB::table('forum_forum_lephonefid')." WHERE `lephonefid`='$thread[fid]'" );

		if(!$thread||!$forum||!$omember){
			return false;
		}
	
		$uid = $omember['uid'];
		$username = $omember['lephoneusername'].'@lephone';
		$fid = $forum['fid'];


		$ccthread  = array();

		foreach ($tableSameArray['forum_thread'] as $key => $value) {
			if ($value == 'tid') {
				$ccthread[$value] = $newtid;
			}elseif ($value == 'fid') {
				$ccthread[$value] = $fid;	
			}elseif ($value == 'authorid') {
				$ccthread[$value] = $uid;
			}elseif ($value == 'author') {
				$ccthread[$value] = $username;
			}elseif ($value == 'relatebytag') {
				$ccthread[$value] = '';
			}else{
				$ccthread[$value] = $thread[$value];
			}
		}

			
		
		DB::insert('forum_thread', $ccthread);
		DB::insert('forum_thread_lephonetid', array('tid'=>$newtid,'lephonetid'=>$thread['tid']));

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
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lephone.".DB::table('forum_thread')."  WHERE fid IN ($convertlefidsjoin) ORDER BY tid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}


$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_thread')." WHERE fid IN ($convertlefidsjoin) ORDER BY tid ASC LIMIT $offset,$ProcessNum");
while($thread = DB::fetch($query)) {
	if (!DB::fetch_first("SELECT *  FROM ".DB::table('forum_thread_lephonetid')." WHERE lephonetid='$thread[tid]'")) {
		threadconvert::lephonethread($thread['tid']);
	}
}
if($totalnum <= $ProcessNum*$page){
	DB::query("UPDATE ".DB::table('forum_thread')." SET `displayorder`='1' WHERE (`displayorder`>'1')");
	showmnextpage('乐Phone.CC主题数据已经转换完毕! 将进行POST数据转换!','cc_post.php');
}

showmnextpage("乐Phone.CC主题数据正在转换中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,0);




?>