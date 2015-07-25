<?php
global $list,$blacklist,$newBlacklist;

$blacklist = array();
if(file_exists("blacklist.txt")){
	@$blacklist = explode("\n",trim(file_get_contents("blacklist.txt")));
}

$list = array();
if(file_exists("database.txt")){
	@$list = json_decode(file_get_contents("database.txt"),true);	
}

$newBlacklist = array();
@mkdir("data");

function isAllowed($page){
	if(strpos($page,"http://")===0) return false;
	if(strpos($page,"https://")===0) return false;
	if(strpos($page,"#")===0) return false;
	if(strpos($page,"[")!==false) return false;
	return true;
}

function isBlacklisted($page){
	global $blacklist;
	if(is_array($page)){
		$name=$page['name'];
	}else{
		$name=utf8_encode($page);
	}
	return in_array($name, $blacklist);
}


function savePage($page,$data){
	global $newBlacklist;
	if(isBlacklisted($page)) return;
	echo "salvando ".$page['name']." como: ".$page['normal']."\n";
	if(empty($data)) {
		echo "[WARNING] la pagina esta vacia (talvez la quieras poner en blacklist?)\n";
		$newBlacklist[]=array("name"=>$page['name'],"who"=>$page['who']);
		return;
	}
	file_put_contents(getFileName($page),$data);
}


function exists($page){
	return file_exists(getFileName($page));
}


function removeAccents($cadena){
	$tofind = utf8_decode("ÀÁÂÃÄÅàáâãäåÒÓÔÕÖØòóôõöøÈÉÊËèéêëÇçÌÍÎÏìíîïÙÚÛÜùúûüÿÑñ");
	$replac = "AAAAAAaaaaaaOOOOOOooooooEEEEeeeeCcIIIIiiiiUUUUuuuuyNn";
	return(strtr($cadena,$tofind,$replac));
}

function normalizeName($name){
    $name = strip_tags(str_replace(array("\r","\t","\n","@","[","]",":",";",'"',"'"," ","“","”","`","´","?","¿","%","/"),'-',$name));
    return removeAccents(preg_replace('/[\-]{2,}/','-',$name));
}

function getFileName($page){
	return "data/".$page['normal'].".md";
}