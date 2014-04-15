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

$bugfid = 730;

//分类对应关系 
$typeidclasslist = array(
'2463'=>'20',
'2466'=>'21',
'2376'=>'22',
'2472'=>'23',
'2392'=>'24',
'2405'=>'25',
'2406'=>'26',
'2393'=>'27',
'2490'=>'28',
'2488'=>'29',
'2487'=>'210',
'2375'=>'211',
'2379'=>'212',
'2378'=>'213',
'2381'=>'214',
'2389'=>'215',
'2418'=>'216',
'2401'=>'10',
'2404'=>'11',
'2398'=>'12',
'2396'=>'13',
'2390'=>'14',
'2391'=>'15',
'2394'=>'16',
'2402'=>'17',
'2397'=>'18',
'2400'=>'19',
'2395'=>'110',
'2414'=>'111',
'2403'=>'112'
);

$handlinglist= array(
	13=>4,
	14=>1,
	16=>3,
	18=>14,
	34=>4,
);



class threadconvert 
{
	public function buglist($tid,$thread){
		global $typeidclasslist,$handlinglist;
		$classid = $typeidclasslist[$thread['typeid']]?$typeidclasslist[$thread['typeid']]:2;
		$handling = $handlinglist[$thread['stamp']]?$handlinglist[$thread['stamp']]:0;
		$insert = array();
		$insert['tid']		= $thread['tid'];
		$insert['uid']		= $thread['authorid'];
		$insert['username']	= $thread['author'];
		$insert['classid'] 	= $classid;
		$insert['dateline'] = $insert['handtime'] = $insert['lasttime']  = $thread['dateline'];
		$insert['samenum']	= $thread['recommend_add'];
		$insert['handling']	= $handling;
		DB::insert('buglist', $insert);
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
	DB::query("TRUNCATE TABLE ".DB::table('buglist'));
	$totalnum = DB::result_first("SELECT count(*)  FROM ".DB::table('forum_thread')." WHERE fid = '$bugfid'  ORDER BY tid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}


$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM ".DB::table('forum_thread')." WHERE fid = '$bugfid' ORDER BY tid ASC LIMIT $offset,$ProcessNum");
while($thread = DB::fetch($query)) {
	if (!DB::fetch_first("SELECT *  FROM ".DB::table('buglist')." WHERE tid='$thread[tid]'")) {
		threadconvert::buglist($thread['tid'],$thread);
	}
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('BUGLIST反馈系统数据已经转换完毕!');
}

showmnextpage("BUGLIST反馈系统数据正在转换中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,0);




?>