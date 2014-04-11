<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();



$forumtables = array('forum_forum','forum_forumfield','forum_threadclass');


foreach ($forumtables  as  $value) {
	DB::query("DELETE FROM ".DB::table($value));
}


$tableSameArray = array();

//比较两个表获得两2个表中相同的部分
foreach ($forumtables as $table) {
	$TempAry1 = $TempAry2 = array();
	$query = DB::query("desc ".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry1[] = $value['Field'];
	}
	$query = DB::query("desc convert_lefen.".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry2[] = $value['Field'];
	}
	$tableSameArray[$table] = array_intersect($TempAry1,$TempAry2);
}

class forumconvert 
{

	function lenovoforum($fid){
		global $tableSameArray,$forumtables;
		$insert = $insertlen = array();
					
		foreach ($forumtables as  $table) {
			DB::query("insert into ".DB::table($table)." (".join(',',$tableSameArray[$table]).") select ".join(',',$tableSameArray[$table])." from convert_lefen.".DB::table($table)." where fid ='$fid'");
		}

	}
	function lephoneforum($tid){
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
}

$query = DB::query("SELECT * FROM convert_lefen.".DB::table('forum_forum')." ORDER BY fid asc");
while($forum = DB::fetch($query)) {
	fwrite(STDOUT,"lefen fid -> $forum[fid]\r\n"); 
	$k = forumconvert::lenovoforum($forum['fid']);
	
}


?>