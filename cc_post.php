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
	function lephonepost($pid){
		global $tableSameArray;

		$ccpost = $uidtouids = array();
		//获取老帖子
		$post = DB::fetch_first("SELECT * FROM convert_lephone.".DB::table('forum_post')." WHERE `pid`='$pid'" );
		//获取新主题的id
		$tid =  DB::result_first("SELECT `tid` FROM ".DB::table('forum_thread_lephonetid')." WHERE `lephonetid`='$post[tid]'" );
		//获取主题内容
		$thread = DB::fetch_first("SELECT * FROM ".DB::table('forum_thread')." WHERE `tid`='$tid'" );
		if(!$thread||!$tid||!$thread['fid']){
			return false;
		}
		$tableid = dintval($thread['tid']{strlen($thread['tid'])-1});
		
		$newpid = C::t('forum_post_tableid')->insert(array('pid' => null), true);

		$postuser = DB::fetch_first("SELECT * FROM ".DB::table('common_member_lephoneuid')." WHERE `lephoneuid`='$post[authorid]'" );
		$uidtouids[$postuser['lephoneuid']] = $postuser['uid'];
		foreach ($tableSameArray['forum_post'] as $value) {
			if ($value == 'tid') {
				$ccpost[$value] = $thread['tid'];
			}elseif ($value == 'pid') {
				$ccpost[$value] = $newpid;
			}elseif ($value == 'fid') {
				$ccpost[$value] = $thread['fid'];
			}elseif ($value == 'authorid') {
				$ccpost[$value] = $postuser['uid'];
			}elseif ($value == 'author') {
				$ccpost[$value] = $postuser['lephoneusername'].'@lephone';
			}else{
				$ccpost[$value] = $post[$value];
			}
		}
		$pidtopids[$post['pid']] = $newpid;
		DB::insert('forum_post', $ccpost);
		DB::insert('forum_thread_lephonepid', array('pid'=>$newpid,'lephonepid'=>$post['pid']));

		if ($post['attachment']) {
			$tabstatus =  DB::fetch_first("SHOW TABLE STATUS where name='pre_forum_attachment';");
			$newaid= $tabstatus['Auto_increment'];
			$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_attachment')." WHERE `tid`='$post[tid]' AND `pid`='$post[pid]'  ORDER BY aid");
			while($attach = DB::fetch($query)) {
				$ccattach = array();
				foreach ($tableSameArray['forum_attachment'] as $value) {
					if ($value == 'aid') {
						$ccattach[$value] = $newaid;
					}elseif ($value == 'tid') {
						$ccattach[$value] = $thread['tid'];
					}elseif ($value == 'pid') {
						$ccattach[$value] = $newpid;
					}elseif ($value == 'fid') {
						$ccattach[$value] = $thread['fid'];;	
					}elseif ($value == 'uid') {
						$ccattach[$value] = $uidtouids[$attach['uid']];
					}elseif ($value == 'tableid') {
						$ccattach['tableid'] = $tableid;
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
						$ccattach[$value] = $thread['tid'];
					}elseif ($value == 'pid') {
						$ccattach[$value] = $newpid;
					}elseif ($value == 'fid') {
						$ccattach[$value] = $thread['fid'];;	
					}elseif ($value == 'uid') {
						$ccattach[$value] = $uidtouids[$attach['uid']];
					}elseif ($value == 'tableid') {
						$ccattach['tableid'] = $tableid;
					}elseif ($value == 'attachment') {
						$ccattach[$value] = 'lephonecc/'.$attach_num[$value];
					}else{
						$ccattach[$value] = $attach_num[$value];
					}
				}

				DB::insert('forum_attachment_'.$tableid, $ccattach);
				DB::query("UPDATE ".DB::table('forum_post')." SET `message` = replace(message, '[attach]{$attach['aid']}[/attach]', '[attach]{$newaid}[/attach]') WHERE `tid`='$thread[tid]' AND `pid`='$newpid' ;");
				

				$newaid++;		
			}
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
	if ($postcleartables) {
		foreach ($postcleartables  as  $value) {
			DB::query("TRUNCATE TABLE ".DB::table($value));
		}
	}

	$maxpid=  DB::result_first("SELECT pid  FROM ".DB::table('forum_post')." ORDER by pid desc LIMIT 1");

	DB::query("REPLACE INTO ".DB::table('forum_post_tableid')." SET `pid`='$maxpid'");
	
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lephone.".DB::table('forum_post')."  WHERE fid IN ($convertlefidsjoin) ");
	$page = 1;

}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}



$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_post')." WHERE fid IN ($convertlefidsjoin)   ORDER BY tid ASC LIMIT $offset,$ProcessNum");
while($post = DB::fetch($query)) {
	if (!DB::fetch_first("SELECT *  FROM ".DB::table('forum_thread_lephonepid')." WHERE lephonepid='$post[pid]'")) {
		threadconvert::lephonepost($post['pid']);
	}
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐Phone.CC主题POST数据已经转换完毕!');
}
showmnextpage("乐Phone.CC主题POST数据正在转换中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,1000);

/*


	DELETE FROM `pre_forum_thread_lephonepid`;
 DELETE FROM `pre_forum_post` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_0` WHERE `tid`>=166495;

 DELETE FROM `pre_forum_attachment_1` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_2` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_3` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_4` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_5` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_6` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_7` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_8` WHERE `tid`>=166495;
 DELETE FROM `pre_forum_attachment_9` WHERE `tid`>=166495;

*/
?>