<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');
require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

require 'function.php';

ini_set('memory_limit','12800M');


define('AVATARPATH_OLD', DISCUZ_ROOT.'file/data/avatar/');
define('AVATARPATH', DISCUZ_ROOT.'uc_server/data/avatar/');


$ProcessNum  = 500;
$page = (int)$_REQUEST['page'];
$totalnum = (int)$_REQUEST['totalnum'];
$starttime = (int)$_REQUEST['starttime'];
if (!$starttime) {
	$starttime = $_G['timestamp'];
}



if ($page<2) {
	$totalnum = DB::result_first("SELECT count(*)  FROM ".DB::table('common_member_lephoneuid'));
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}



$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM ".DB::table('common_member_lephoneuid')."  ORDER BY uid ASC LIMIT $offset,$ProcessNum");
while($user = DB::fetch($query)) {
	mv_avatar($user['uid'],$user['lephoneuid']);
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐Phone.CC用户数据搬迁完毕!');
}
showmnextpage("乐Phone.CC用户数据搬迁中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,0);





?>