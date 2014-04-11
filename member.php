<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();




$memberfields = array('uid', 'email', 'username', 'password', 'status', 'emailstatus', 'avatarstatus', 'videophotostatus', 'groupid',  'regdate',   'timeoffset',);
$membeructables = array('ucenter_members', 'ucenter_memberfields');
$membercleartables = array('ucenter_members', 'ucenter_memberfields','common_member','common_member_lenovoid','common_member_lephoneid');
/**
* 
*/
foreach ($membercleartables as  $value) {
	DB::query("DELETE FROM ".DB::table($value));
}


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
		if ($member['field1']) {
			echo "lenovoid : $insertlen[uid]:$insertlen[lenovoid]\n";
			DB::insert('common_member_lenovoid', $insertlen);
		}
		
	}
	function lephonemember($uid){
		global $membeructables,$memberfields;

		//SHOW TABLE STATUS from convert_lefen where name='pre_ucenter_members';
		$tabstatus =  DB::fetch_first("SHOW TABLE STATUS where name='pre_ucenter_members';");
		$newuid= $tabstatus['Auto_increment'];

		foreach ($membeructables as  $value) {
			$levalue = str_replace('ucenter_', 'uc_', $value);
			$member = DB::fetch_first("SELECT * FROM convert_lephone.$levalue WHERE `uid`='$uid'" );
			$member['uid'] = $newuid;
			if ($member['username']) {
				$member['username'] = $member['username'].'@lephone';
			}
			DB::insert($value, $member);
		}

		$insert = $insertlen = array();
		$member = DB::fetch_first("SELECT m.* FROM convert_lephone.`pre_common_member` m WHERE m.`uid`='$uid'" );
		foreach ($memberfields as  $value) {
			if ($value == 'uid') {
				$insert[$value] = $newuid;
			}elseif ($value == 'username') {
				$insert['username'] = $member['username'].'@lephone';
			}else{
				$insert[$value] = $member[$value];
			}
		}
		$insertlen['uid'] = $newuid;
		$insertlen['lephoneid'] = $uid;
		DB::insert('common_member', $insert);
		DB::insert('common_member_lephoneid', $insertlen);
	}

	function get_avatar($uid, $size = 'big', $type = '') {
		$size = in_array($size, array('big', 'middle', 'small')) ? $size : 'big';
		$uid = abs(intval($uid));
		$uid = sprintf("%09d", $uid);
		$dir1 = substr($uid, 0, 3);
		$dir2 = substr($uid, 3, 2);
		$dir3 = substr($uid, 5, 2);
		$typeadd = $type == 'real' ? '_real' : '';
		return  $dir1.'/'.$dir2.'/'.$dir3.'/'.substr($uid, -2).$typeadd."_avatar_$size.jpg";
	}
}

$stdout = fopen('php://stdout', 'w');

ini_set('memory_limit','12800M');

$query = DB::query("SELECT * FROM convert_lefen.".DB::table('common_member')." ORDER BY uid asc");
while($user = DB::fetch($query)) {
	fwrite(STDOUT,"lefen -> $user[uid]\r\n"); 
	$k = memberconvert::lenovomember($user['uid']);
	
}

$query = DB::query("SELECT * FROM convert_lephone.".DB::table('common_member')." ORDER BY uid asc");
while($user = DB::fetch($query)) {
	print "lephone -> $user[uid]\r\n";
	flush();
	$k = memberconvert::lephonemember($user['uid']);
}

?>