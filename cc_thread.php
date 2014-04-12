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

		$pidtopids = $uidtouids = array();
		

		$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_post')." WHERE `tid`='$tid' ORDER BY pid");
		while($post = DB::fetch($query)) {
			$ccpost = array();
			$pid = C::t('forum_post_tableid')->insert(array('pid' => null), true);
			$postuser = DB::fetch_first("SELECT * FROM ".DB::table('common_member_lephoneuid')." WHERE `lephoneuid`='$post[authorid]'" );
			$uidtouids[$postuser['lephoneuid']] = $postuser['uid'];

			foreach ($tableSameArray['forum_post'] as $value) {
				if ($value == 'tid') {
					$ccpost[$value] = $newtid;
				}elseif ($value == 'pid') {
					$ccpost[$value] = $pid;
				}elseif ($value == 'fid') {
					$ccpost[$value] = $fid;	
				}elseif ($value == 'authorid') {
					$ccpost[$value] = $postuser['uid'];
				}elseif ($value == 'author') {
					$ccpost[$value] = $postuser['lephoneusername'].'@lephone';
				}else{
					$ccpost[$value] = $post[$value];
				}
			}
			$pidtopids[$post['pid']] = $pid;
			DB::insert('forum_post', $ccpost);
		}
		$tabstatus =  DB::fetch_first("SHOW TABLE STATUS where name='pre_forum_attachment';");
		$newaid= $tabstatus['Auto_increment'];
		$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_attachment')." WHERE `tid`='$tid' ORDER BY aid");
		while($attach = DB::fetch($query)) {
			$ccattach = array();
			foreach ($tableSameArray['forum_attachment'] as $value) {
				if ($value == 'aid') {
					$ccattach[$value] = $newaid;
				}elseif ($value == 'tid') {
					$ccattach[$value] = $newtid;
				}elseif ($value == 'pid') {
					$ccattach[$value] = $pidtopids[$attach['pid']];
				}elseif ($value == 'fid') {
					$ccattach[$value] = $fid;	
				}elseif ($value == 'uid') {
					$ccattach[$value] = $uidtouids[$attach['uid']];
				}else{
					$ccattach[$value] = $attach[$value];
				}
			}
			DB::insert('forum_attachment', $ccattach);
			$attach_num = DB::fetch_first("SELECT * FROM convert_lephone.".DB::table('forum_attachment_'.$attach['tableid'])." WHERE `aid`='$attach[aid]'" );
			$ccattach = array();
			foreach ($tableSameArray['forum_attachment_'.$attach['tableid']] as $value) {
				if ($value == 'aid') {
					$ccattach[$value] = $newaid;
				}elseif ($value == 'tid') {
					$ccattach[$value] = $newtid;
				}elseif ($value == 'attachment') {
					$ccattach[$value] = 'lephonecc/'.$attach_num[$value];
				}elseif ($value == 'pid') {
					$ccattach[$value] = $pidtopids[$attach['pid']];
				}elseif ($value == 'fid') {
					$ccattach[$value] = $fid;	
				}elseif ($value == 'uid') {
					$ccattach[$value] = $uidtouids[$attach['uid']];
				}else{
					$ccattach[$value] = $attach_num[$value];
				}
			}

			DB::insert('forum_attachment_'.$attach['tableid'], $ccattach);
			DB::query("UPDATE ".DB::table('forum_post')." SET `message` = replace(message, '[attach]{$attach['aid']}[/attach]', '[attach]{$newaid}[/attach]') WHERE `tid` ='$newtid' AND `pid` ='{$ccattach['pid']}';");
			$newaid++;		
		}


		

	}

}


ini_set('memory_limit','12800M');



$ProcessNum  = 100;
$page = (int)$_REQUEST['page'];
$totalnum = (int)$_REQUEST['totalnum'];



if ($page<2) {
	foreach ($postcleartables  as  $value) {
		DB::query("TRUNCATE TABLE ".DB::table($value));
	}
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lephone.".DB::table('forum_thread')." ORDER BY tid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}

if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐Phone.CC主题数据已经转换完毕!');
}

$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_thread')."  ORDER BY tid ASC LIMIT $offset,$ProcessNum");
while($thread = DB::fetch($query)) {
	threadconvert::lephonethread($thread['tid']);
}
showmnextpage("乐Phone.CC主题数组正在转换中...".$ProcessNum*$page." / $totalnum",'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum);




?>