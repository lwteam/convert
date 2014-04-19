<?php


if (php_sapi_name() == 'cli') {
	chdir(dirname(__FILE__));
}

chdir('../');

require './source/class/class_core.php';
$discuz = & discuz_core::instance();
$discuz->init();


$forumtables = array('forum_forum','forum_forumfield','forum_threadclass');

$clearforumtables = array('forum_forum_lephonefid','forum_forum','forum_forumfield','forum_threadclass');


$convertfups = array(255,665);
$convertlefids = array(85,87,67,71,4,13);

$fupfids = join(',',$convertfups);


$query = DB::query("SELECT f.* FROM convert_lefen.".DB::table('forum_forum')." f 
	WHERE f.status='1' AND (f.fup IN ($fupfids) || f.fid IN ($fupfids) ) AND f.type IN ('forum','group') ");

while($forum = DB::fetch($query)) {
	$opfids[] = $forum['fid'];
	if ($forum['type'] == 'forum') {
		$suball =  DB::fetch_all("SELECT f.* FROM convert_lefen.".DB::table('forum_forum')." f 
				WHERE f.status='1' AND f.fup='$forum[fid]'  AND f.type ='sub';");
		if ($suball) {
			foreach ($suball as $value) {
				$opfids[] = $value['fid'];
			}
		}
	}
}




foreach ($clearforumtables   as  $value) {
	DB::query("TRUNCATE TABLE  ".DB::table($value));
}
DB::query("truncate table  ".DB::table('forum_forum_lephonefid'));


$tableSameArray = $CcTableSameArray = array();

//比较两个表获得两2个表中相同的部分
foreach ($forumtables as $table) {
	$TempAry1 = $TempAry2 = array();
	$query = DB::query("desc ".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry1[$value['Field']] = $value['Field'];
	}
	$query = DB::query("desc convert_lefen.".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry2[$value['Field']] = $value['Field'];
	}
	$query = DB::query("desc convert_lephone.".DB::table($table));
	while($value = DB::fetch($query)) {
		$TempAry3[$value['Field']] = $value['Field'];
	}
	$tableSameArray[$table] = array_intersect($TempAry1,$TempAry2);
	$CcTableSameArray[$table] = array_intersect($TempAry1,$TempAry3);
}



class forumconvert 
{

	function lenovoforum($fid){
		global $tableSameArray,$forumtables;
		$insert = $insertlen = array();
		foreach ($forumtables as  $table) {
			if ($table == 'forum_forum') {
				foreach (array('displayorder','lastpost') as $key=>$value) {
					unset($tableSameArray[$table][$value]);
				}
			}

			if ($table == 'forum_forumfield') {
				foreach (array('icon','moderators','viewperm','postperm','replyperm','getattachperm','postattachperm','postimageperm','spviewperm') as $key=>$value) {
					unset($tableSameArray[$table][$value]);
				}
			}
			DB::query("insert into ".DB::table($table)." (".join(',',$tableSameArray[$table]).") select ".join(',',$tableSameArray[$table])." from convert_lefen.".DB::table($table)." where fid ='$fid'");
		}

	}
	function lephoneforum($fid,$forum){
		global $CcTableSameArray,$forumtables;
		static $groupids = array();

		//SHOW TABLE STATUS from convert_lefen where name='pre_ucenter_members';
		$tabstatus =  DB::fetch_first("SHOW TABLE STATUS where name='pre_forum_forum';");
		$newfid= $tabstatus['Auto_increment'];
		if ($forum['type'] == 'group') {
			$groupids[$fid] = $newfid;
		}

		DB::insert('forum_forum_lephonefid', array('fid'=>$newfid,'lephonefid'=>$fid));

		$returnarray = array('fid'=>$newfid,'lephonefid'=>$fid);

		foreach ($forumtables as  $table) {

			if ($table == 'forum_forum') {
				foreach (array('displayorder','lastpost') as $key=>$value) {
					unset($tableSameArray[$table][$value]);
				}
			}

			if ($table == 'forum_forumfield') {
				foreach (array('icon','threadtypes','moderators','viewperm','postperm','replyperm','getattachperm','postattachperm','postimageperm','spviewperm') as $key=>$value) {
					unset($CcTableSameArray[$table][$value]);
				}
			}
			if ($table == 'forum_threadclass') {
				continue;
			}

			$NewCcTableSameArray = $CcTableSameArray;

			$NewCcTableSameArray[$table]['fid'] = "'$newfid'";
			if ($forum['type'] != 'group' && $CcTableSameArray[$table]['fup']) {
				$NewCcTableSameArray[$table]['fup'] = "'{$groupids[$forum['fup']]}'";
			}
			DB::query("insert into ".DB::table($table)." (".join(',',$CcTableSameArray[$table]).") select ".join(',',$NewCcTableSameArray[$table])." from convert_lephone.".DB::table($table)." where fid ='$fid'");
		}

		return $returnarray;
	}
}


$allfids = join(',',$opfids);
$alllefids = join(',',$convertlefids);

	
$query = DB::query("SELECT * FROM convert_lefen.".DB::table('forum_forum')." WHERE fid IN ($allfids)  ORDER BY fid asc");
while($forum = DB::fetch($query)) {
	forumconvert::lenovoforum($forum['fid']);
}

$query = DB::query("SELECT * FROM convert_lephone.".DB::table('forum_forum')."  WHERE fid IN ($alllefids)  ORDER BY type");
while($forum = DB::fetch($query)) { 
	$lepfids[$forum['fid'].$forum['name']] = forumconvert::lephoneforum($forum['fid'],$forum);

}
echo'<pre>';
var_dump( $allfids,$lepfids );
echo'</pre>';exit;
	
?>