<?php
global $base,$list,$blacklist,$newBlacklist;

$blacklist=array();
if(file_exists("blacklist.txt")){
	$blacklist = explode("\n",trim(file_get_contents("blacklist.txt")));
}

$base = "http://wiki.dokkogroup.com.ar/convert/?page=";
$newBlacklist = array();
@mkdir("data");

function isBlacklisted($page){
	global $blacklist;
	if(strpos($page,"http://")===0) return true;
	if(strpos($page,"https://")===0) return true;
	if(strpos($page,"#")===0) return true;
	return in_array(utf8_encode($page), $blacklist);
}

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
	$links1 = getDataBetweenTokens($data,"[[","]]");
	$links2 = getDataBetweenTokens($data,"](",")");
	$links3 = getDataBetweenTokens($data,"|","]");
	$res = array_merge($links1,$links2,$links3);
	foreach ($res as $name) addPage($name);
}

function removeAccents($cadena){
	$tofind = utf8_decode("ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ");
	$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
	return(strtr($cadena,$tofind,$replac));
}

function normalizeName($name){
    $name = strip_tags(str_replace(array("\r","\t","\n","@",":",";",'"',"'"," ","“","”","`","´","?","¿","%","/"),'-',$name));
    return removeAccents(preg_replace('/[\-]{2,}/','-',$name));
}

function getFileName($page){
	return "data/".$page['normal'].".md";
}

function getPage($page){
	global $base;
	$data = file_get_contents($base.urlencode(utf8_decode(($page['name']))));
	savePage($page,$data);
	return $data;
}

function savePage($page,$data){
	global $newBlacklist;
	echo "salvando ".$page['name']." como: ".$page['normal']."\n";
	if(empty($data)) {
		echo "[WARNING] la pagina esta vacia (talvez la quieras poner en blacklist?)\n";
		$newBlacklist[]=$page['name'];
		return;
	}
	file_put_contents(getFileName($page),$data);
}

function exists($page){
	return file_exists(getFileName($page));
}

function addPage($page){
	global $list;
	if(empty($list)) $list=array();
	$normal = normalizeName($page);
	if(!isset($list[$normal])) $list[$normal]=array("name"=>utf8_encode($page),"normal"=>$normal);
}

@$list = json_decode(file_get_contents("database.txt"),true);
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