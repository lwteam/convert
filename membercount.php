<?php



// 0 新提交 1 待解决 2 正在解决 3 已经解决 4 完结

if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');
require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

require 'function.php';




class memberconvert 
{
	public function count($uid){

		$insert = array();
		$insert['uid']		= $uid;
		
		$insert['posts']	= DB::result_first("SELECT count(*)  FROM ".DB::table('forum_thread')." WHERE authorid='$uid'");
		$insert['threads']	= DB::result_first("SELECT count(*)  FROM ".DB::table('forum_post')." WHERE authorid='$uid'");
		$insert['extcredits1']	= '0';
		$insert['extcredits2']	= $insert['posts']+$insert['threads'];
		$insert['extcredits8']	= '1';


		DB::insert('common_member_count', $insert);
	}

}


ini_set('memory_limit','12800M');



$ProcessNum  = 1000;
$page = (int)$_REQUEST['page'];
$totalnum = (int)$_REQUEST['totalnum'];
$starttime = (int)$_REQUEST['starttime'];
if (!$starttime) {
	$starttime = $_G['timestamp'];
}


if ($page<2) {
	DB::query("TRUNCATE TABLE ".DB::table('common_member_count'));
	$totalnum = DB::result_first("SELECT count(*)  FROM ".DB::table('common_member'));
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}


$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM ".DB::table('common_member')." ORDER BY uid ASC LIMIT $offset,$ProcessNum");
while($member = DB::fetch($query)) {
	if (!DB::fetch_first("SELECT *  FROM ".DB::table('common_member_count')." WHERE uid='$member[uid]'")) {
		memberconvert::count($member['uid']);
	}
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('会员统计数据已经转换完毕!');
}

showmnextpage("会员统计数据正在转换中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,0);




?>