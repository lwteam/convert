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
2438=>'10',
2441=>'11',
2435=>'12',
2433=>'13',
2427=>'14',
2428=>'15',
2431=>'16',
2439=>'17',
2434=>'18',
2437=>'19',
2432=>'110',
2451=>'111',
2440=>'112',
2464=>'20',
2494=>'21',
2420=>'22',
2473=>'23',
2429=>'24',
2442=>'25',
2443=>'26',
2430=>'27',
2491=>'28',
2493=>'29',
2492=>'210',
2419=>'211',
2423=>'212',
2422=>'213',
2425=>'214',
2426=>'215',
2455=>'216',
);

$handlinglist= array(
	13=>4,
	14=>1,
	16=>3,
	18=>1,
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
		if ($thread['stamp'] == 18) {
			$insert['supply'] = 1;
		}
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