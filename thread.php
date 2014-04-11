<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();


$posttables = array('forum_thread','forum_attachment','forum_attachment_0','forum_attachment_1','forum_attachment_2','forum_attachment_3','forum_attachment_4','forum_attachment_5','forum_attachment_6','forum_attachment_7','forum_attachment_8','forum_attachment_9','forum_poll','forum_polloption','forum_polloption_image','forum_pollvoter','forum_post','forum_post_location','forum_postcomment','forum_postlog','forum_poststick');


foreach ($posttables  as  $value) {
	DB::query("DELETE FROM ".DB::table($value));
}


$tableSameArray = array();

//比较两个表获得两2个表中相同的部分
foreach ($posttables as $table) {
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


class threadconvert 
{

	function lenovothread($tid){
		global $tableSameArray,$posttables;
		$insert = $insertlen = array();
					
		foreach ($posttables as  $table) {
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



$stdout = fopen('php://stdout', 'w');

ini_set('memory_limit','12800M');

$query = DB::query("SELECT * FROM convert_lefen.".DB::table('common_member')." ORDER BY uid asc");
while($user = DB::fetch($query)) {
	fwrite(STDOUT,"lefen -> $user[uid]\r\n"); 
	$k = memberconvert::lenovomember($user['uid']);
	
}

echo'<pre>';
var_dump( threadconvert::lenovothread(163114) );
echo'</pre>';exit;
	

?>