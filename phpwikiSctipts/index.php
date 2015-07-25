<?php
global $replaces,$repCount;
$replaces = array();
$repCount = 0;
$db = 'phpwiki';
$dbUser = 'root';
$dbPass = '1234';

function guessLang($data){
	$html = substr_count($data,'>');
	$html+= substr_count($data,'<');
	$smarty = substr_count("{/");
	$smarty+= substr_count("{\$");
	$php = substr_count($data,";\n");
	$php+= substr_count($data,"(\$");
	
	if($php>=$html && $php>$smarty) return 'php';
	if($smarty+$html > 5) return 'smarty';
	return '';
}

function saveDataBetweenTokens($data,$open,$close,$lang=null){
	global $replaces,$repCount;
	$arr = explode($open," ".$data);
	unset($arr[0]);
	foreach($arr as $part){
		$aux = explode($close,$part,2);
		if(count($aux)==1) continue;
		$newClose='```';
		$newOpen='```'.($lang==null?guessLang($aux[0]):$lang);
		
		$k="REPLACEMMMMREPLACEMMM".$repCount++."KKKK";
		$data=str_replace($open.$aux[0].$close,$k,$data);
		$aux[0]=str_replace('~//','//',$aux[0]);
		$aux[0]=str_replace('\'~','\'',$aux[0]);
		$replaces[$k]=$newOpen.$aux[0].$newClose;
	}
	return $data;
}

function replaceCodeBlocks($data){
	$data = saveDataBetweenTokens($data,'<?plugin PhpHighlight','?>','php');
	$data = saveDataBetweenTokens($data,"<pre>","</pre>");
	$data = saveDataBetweenTokens($data,"<verbatim>","</verbatim>");
	return $data;
}

function restoreDataBetweenTokens($data){
	global $replaces;
	foreach($replaces as $k=>$v) $data=str_replace($k,$v,$data);
	return $data;
}

$page = $_GET['page'];

$res = exec("echo \"select content from version where id in (select id from page where pagename = '".mysql_escape_string($page)."') order by version desc, minor_edit desc limit 1;\" | mysql -u $dbUser -p$dbPass $db");
$res .="\n";

$file = "tmp_".md5($page).".tmp";
$res = str_replace(array("%%%","\\n","<br>","<br/>"),"\n",$res);
$res = str_replace(array("\r"),"",$res);
$res = replaceCodeBlocks($res);
$res = str_replace(array("<b>","</b>"),"**",$res);
$res = preg_replace("/^\!/m","\n!",$res);

$tablas = explode("<?plugin OldStyleTable",$res);
$res = $tablas[0];
unset($tablas[0]);

foreach($tablas as $t){
	list($tabla,$resto)=explode("?>",$t,2);
	$tabla = trim($tabla);
	$rows=explode("\n",$tabla);
	$res.="\n".$rows[0]."|\n";
	$res.=preg_replace("/([^\|])/","-",$rows[0])."|\n";
	unset($rows[0]);
	foreach($rows as $row){
		$res.=$row."|\n";
	}
//$tabla=preg_replace("/^\|/m","<tr><td>",$tabla);
//	$tabla=preg_replace("/$/m","|",$tabla);
//	$tabla=preg_replace("/\|/","</td><td>",$tabla);
	$res.="\n".$resto;
}

file_put_contents($file,$res);
exec("./wiki2github ".$file);

$res = file_get_contents($file);
unlink($file);

$res = str_replace("\n\n\n","\n\n",$res);
$res = str_replace("\n\n\n","\n\n",$res);
$res = str_replace("\n\n\n","\n\n",$res);
$res = str_replace("\n\n\n","\n\n",$res);
$res = str_replace("\n\n\n","\n\n",$res);
$res = str_replace("\n\n\n","\n\n",$res);
$res = restoreDataBetweenTokens($res);

session_start();
echo $res;
