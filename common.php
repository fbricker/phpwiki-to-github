<?php
global $base,$list,$blacklist,$newBlacklist;

$blacklist = array();
if(file_exists("blacklist.txt")){
	@$blacklist = explode("\n",trim(file_get_contents("blacklist.txt")));
}

$list = array();
if(file_exists("database.txt")){
	@$list = json_decode(file_get_contents("database.txt"),true);	
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