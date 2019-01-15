<?php
require_once 'common.php';
global $base;
$base = "http://your-wiki-host.com/export/?page=";
$uploadsBase = "http://your-wiki-host.com/uploads/";


function getDataBetweenTokens($data,$open,$close){
	$arr = explode($open," ".$data);
	unset($arr[0]);
	$res = array();
	foreach($arr as $part){
		$aux = explode($close,$part,2);
		if(count($aux)==1) continue;
		if(substr_count($aux[0],"\n")>0) continue;
		if(!isAllowed($aux[0])) continue;
		$res[]=$aux[0];
	}
	return $res;
}

function parsePage($data,$page){
	$data=str_replace("#[|","[",$data);
	$data=str_replace("~[","",$data);
	$data=str_replace("~]","",$data);
	$links1 = getDataBetweenTokens($data,"[[","]]");
	$links2 = getDataBetweenTokens($data,"](",")");
	$links3 = getDataBetweenTokens($data,"|","]");
	$res = array_merge($links1,$links2,$links3);
	foreach ($res as $name) {
		if(strpos($name,"#")!==false) $name=substr($name,0, strpos($name,"#"));
		$extension = explode('.', $name);
		if(count($extension)>1){
			$extension = array_pop($extension);
			if(in_array($extension, ['pdf','png','jpg','gif','zip','cdr','doc'])) continue;
		}
		addPage($name,utf8_decode($page['name']));
	}
}

function getPage($page){
	global $base, $uploadsBase;
	if(isBlacklisted($page)) return '';
	$data = file_get_contents($base.urlencode(utf8_decode(($page['name']))));

	$files = explode('<Upload:', $data);
	$newData = $files[0];
	unset($files[0]);
	$canDisplay = ['jpg','gif','png'];
	foreach($files as $file){
		$file = explode('>', $file, 2);
		$extension = explode('.', $file[0]);
		$extension = array_pop($extension);
		if(in_array($extension, $canDisplay)){
			$newData .= '![]('.$file[0].')'.$file[1];
		}else{
			$newData .= '['.$file[0].']('.$file[0].')'.$file[1];
		}
		$file = $file[0];
		$file = str_replace(['[',']'], '', $file);
		$local = 'data/'.$file;
		if(file_exists($local)) continue;
		file_put_contents($local, file_get_contents($uploadsBase.$file));
	}
	$data = $newData;

	$files = explode('[ Upload:', $newData);
	$newData = $files[0];
	unset($files[0]);
	$canDisplay = ['jpg','gif','png'];
	foreach($files as $file){
		$file = explode(' ]', $file, 2);
		$extension = explode('.', $file[0]);
		$extension = array_pop($extension);
		if(in_array($extension, $canDisplay)){
			$newData .= '![]('.$file[0].')'.$file[1];
		}else{
			$newData .= '['.$file[0].']('.$file[0].')'.$file[1];
		}
		$file = $file[0];
		$file = str_replace(['[',']'], '', $file);
		$local = 'data/'.$file;
		if(file_exists($local)) continue;
		file_put_contents($local, file_get_contents($uploadsBase.$file));
	}
	$data = $newData;

	$data = str_replace('<?plugin UpLoad ?>', '', $data);

	savePage($page,$data);
	return $data;
}

function addPage($page,$from=''){
	global $list;
	if(substr($page, 0, 1)=='#') return;
	$normal = normalizeName($page);
	if(!isset($list[$normal])) $list[$normal]=array("name"=>utf8_encode($page),"normal"=>$normal,"who"=>utf8_encode($from));
}

if(!empty($argv[1])) addPage($argv[1],'[[console/manual]]');

foreach($list as $page){
	if(!exists($page)) {
		$data = getPage($page);
	}else{
		$data = file_get_contents(getFileName($page));
	}
	parsePage($data,$page);
}

echo "listo!!!\n";
if(count($newBlacklist)>0){
	echo "\nHay nuevas blacklist:\n";
	print_r($newBlacklist);
	$nb = "";
	foreach($newBlacklist as $new){
		if(empty($new["page"])){
			$nb.=$new["name"]."\n";
			continue;
		}
		$nb.=$new["page"]."\n";
	}
	file_put_contents("newBlacklist.txt", $nb);
}

file_put_contents("database.txt",json_encode($list,true));