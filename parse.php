<?php
require_once 'common.php';

function getDataBetweenTokens($data,$open,$close){
	$arr = explode($open," ".$data);
	unset($arr[0]);
	$res = array();
	foreach($arr as $part){
		$aux = explode($close,$part,2);
		if(count($aux)==1) continue;
		if(substr_count($aux[0],"\n")>0) continue;
		if(isBlacklisted($aux[0])) continue;
		$res[]=$aux[0];
	}
	return $res;
}

function parsePage($data){
	$data=str_replace("#[|","[",$data);
	$data=str_replace("~[","",$data);
	$data=str_replace("~]","",$data);
	$links1 = getDataBetweenTokens($data,"[[","]]");
	$links2 = getDataBetweenTokens($data,"](",")");
	$links3 = getDataBetweenTokens($data,"|","]");
	$res = array_merge($links1,$links2,$links3);
	foreach ($res as $name) addPage($name);
}

function getPage($page){
	global $base;
	$data = file_get_contents($base.urlencode(utf8_decode(($page['name']))));
	savePage($page,$data);
	return $data;
}

function addPage($page){
	global $list;
	$normal = normalizeName($page);
	if(!isset($list[$normal])) $list[$normal]=array("name"=>utf8_encode($page),"normal"=>$normal);
}

addPage("Denko");

foreach($list as $page){
	if(!exists($page)) {
		$data = getPage($page);
	}else{
		$data = file_get_contents(getFileName($page));
	}
	parsePage($data);
}

echo "listo!!!\n";
if(count($newBlacklist)>0){
	echo "\nHay nuevas blacklist:\n";
	print_r($newBlacklist);
	$nb = "";
	foreach($newBlacklist as $new){
		$nb.="$new\n";
	}
	file_put_contents("newBlacklist.txt", $nb);
}

file_put_contents("database.txt",json_encode($list,true));