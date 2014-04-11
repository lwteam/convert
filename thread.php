<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();



class threadconvert 
{

	function lenovothread($tid){
		global $tableSameArray,$threadposttables_lephone;
		$insert = $insertlen = array();
					
		foreach ($threadposttables_lephone as  $table) {
			DB::query("insert into ".DB::table($table)." (".join(',',$tableSameArray[$table]).") select ".join(',',$tableSameArray[$table])." from convert_lefen.".DB::table($table)." where tid ='$tid'");
		}

	}
	function lephonethread($tid){
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

echo'<pre>';
var_dump( threadconvert::lenovothread(163114) );
echo'</pre>';exit;
	

?>