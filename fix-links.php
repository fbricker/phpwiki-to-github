<?php
require_once 'common.php';

function fixLinks($page){
	global $list;
	$original = $data = file_get_contents(getFileName($page));
	
	foreach($list as $link){
		$name = utf8_decode($link['name']);
		$normal = $link['normal'];
		//if($name==$normal) continue;
		//echo "cambiando $name a $normal\n";
		$data=str_replace("[$normal|$normal]","[[$normal]]",$data);
		$data=str_replace("[$normal]($normal)","[[$normal]]",$data);
		$data=str_replace("[[$name]]","[[$normal]]",$data);
		$data=str_replace("]($name)","]($normal)",$data);
		$data=str_replace("|$name]","]($normal)",$data);
	}

	$data=str_replace("~[","[",$data);
	$data=str_replace("~]","]",$data);
	if($original == $data){
		echo "SIN CAMBIOS: ".$page['normal']."\n";
	}else{
		savePage($page,$data);		
	}
}

foreach($list as $page){
	if(!exists($page)) continue;
	//if($page['name']!='Denko') continue;
	fixLinks($page);
}

echo "\n\nListo, ya estan arreglados los links y todo :)\n\n";