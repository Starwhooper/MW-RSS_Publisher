<?php
function cleantext($string,$title){
	$string = trim($string);
	$string = str_replace('{{PAGENAME}}',$title,$string);
	$string = str_replace("\n"," ",$string);
	$string = str_replace("[[","",$string);
	$string = str_replace("]]","",$string);
	$string = strip_tags($string);
	$string = str_replace('  ',' ',$string);
	return($string);
}
?>