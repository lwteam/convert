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
$membercleartables = array('ucenter_members', 'ucenter_memberfields','common_member','common_member_lenovoid','common_member_lephoneid');
/**
* 
*/


class memberconvert 
{

	function lephonemember($uid){
		global $membeructables,$memberfields;

		//SHOW TABLE STATUS from convert_lephone where name='pre_ucenter_members';
		$tabstatus =  DB::fetch_first("SHOW TABLE STATUS where name='pre_ucenter_members';");
		$newuid= $tabstatus['Auto_increment'];

		$omember = DB::fetch_first("SELECT * FROM convert_lephone.pre_common_member WHERE `uid`='$uid'" );
		$username = $omember['username'].'@lephone';
		$email =  $omember['uid'].'@lephone.cc';
	

		foreach ($membeructables as  $value) {
			$levalue = str_replace('ucenter_', 'uc_', $value);
			$member = DB::fetch_first("SELECT * FROM convert_lephone.$levalue WHERE `uid`='$uid'" );
			$member['uid'] = $newuid;
			if ($member['username']) {
				$member['username'] = $username;
			}
			if ($member['email']) {
				$member['email'] = $email;
			}
			DB::insert($value, $member);
		}

		$insert = $insertlen = array();
		$member = DB::fetch_first("SELECT m.* FROM convert_lephone.`pre_common_member` m WHERE m.`uid`='$uid'" );
		foreach ($memberfields as  $value) {
			if ($value == 'uid') {
				$insert[$value] = $newuid;
			}elseif ($value == 'username') {
				$insert[$value] = $username;
			}elseif ($value == 'email') {
				$insert[$value] = $email;
			}else{
				$insert[$value] = $member[$value];
			}
		}

		$insertlen['uid'] = $newuid;
		$insertlen['lephoneuid'] = $omember['uid'];
		$insertlen['lephoneusername'] = $omember['username'];
		$insertlen['lephoneemail'] = $omember['email'];
		DB::insert('common_member', $insert);
		DB::insert('common_member_lephoneuid', $insertlen);
	}
}



ini_set('memory_limit','12800M');

$ProcessNum  = 1000;
$page = (int)$_REQUEST['page'];
$totalnum = (int)$_REQUEST['totalnum'];



if ($page<2) {
	$totalnum = DB::result_first("SELECT count(*)  FROM convert_lephone.".DB::table('common_member')." ORDER BY uid asc");
	$page = 1;
}

if(@ceil($totalnum/$ProcessNum) < $page){
	$page = 1;
}

if($totalnum <= $ProcessNum*$page){
	showmnextpage('乐Phone.CC会员数据已经转换完毕!');
}

$offset = ($page - 1) * $ProcessNum;

$query = DB::query("SELECT * FROM convert_lephone.".DB::table('common_member')." ORDER BY uid asc LIMIT $offset,$ProcessNum");
while($user = DB::fetch($query)) {
	memberconvert::lephonemember($user['uid']);
}
showmnextpage("乐Phone.CC会员数据正在转换中...".$ProcessNum*$page." / $totalnum",'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.'page='.($page+1).'&totalnum='.$totalnum);



?>