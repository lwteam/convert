<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();

require 'function.php';


$memberfields = array('uid', 'email', 'username', 'password', 'status', 'emailstatus', 'avatarstatus', 'videophotostatus', 'groupid',  'regdate',   'timeoffset',);
$membeructables = array('ucenter_members', 'ucenter_memberfields');
$membercleartables = array('ucenter_members', 'ucenter_memberfields','common_member','common_member_lenovoid','common_member_accountchange','common_member_lephoneuid');
/**
* 
*/


class memberconvert 
{

	function lenovomember($uid){
		global $membeructables,$memberfields;
		$insert = $insertlen = array();
		$member = DB::fetch_first("SELECT m.*,mp.field1,mp.field2  FROM convert_lefen.`pre_common_member` m 
			LEFT JOIN convert_lefen.`pre_common_member_profile` mp USING(uid) WHERE m.`uid`='$uid'" );

			
		if ($member) {
			foreach ($memberfields as  $value) {

				$insert[$value] = $member[$value];
			}
			$insert['adminid'] = '0';
			$insert['groupid'] = '10';
			$insert['allowadmincp'] = '0';
			$insert['groupid'] = '10';
			

			foreach ($membeructables as  $value) {
				DB::query("insert into ".DB::table($value)." select * from convert_lefen.".DB::table($value)." where uid ='$uid'");
			}
		}

		$insertlen['uid'] = $member['uid'];
		$insertlen['lenovoid'] = $member['field1'];
		DB::insert('common_member', $insert);
		DB::insert('common_member_accountchange', array('uid'=>$member['uid'],'username'=>$member['username'],'email'=>$member['email']));
		if ($member['field1']) {
			if (!DB::result_first("SELECT *  FROM ".DB::table('common_member_lenovoid')." WHERE lenovoid='$insertlen[lenovoid]'")) {
				DB::insert('common_member_lenovoid', $insertlen);
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
	foreach ($membercleartables  as  $value) {
		DB::query("TRUNCATE TABLE ".DB::table($value));
	}
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lefen.".DB::table('common_member')." ORDER BY uid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}



$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lefen.".DB::table('common_member')." ORDER BY uid asc LIMIT $offset,$ProcessNum");
while($user = DB::fetch($query)) {
	memberconvert::lenovomember($user['uid']);
}
if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐粉会员数据已经转换完毕! 将进行乐Phone.CC会员数据转换!','cc_member.php');
}
showmnextpage("乐粉会员数据正在转换中...".loadingdata(),'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum.'&starttime='.$starttime,0);


?>