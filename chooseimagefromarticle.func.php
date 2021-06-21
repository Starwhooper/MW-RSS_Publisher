<?php
function chooseimagefromarticle($pageid){
	global $wgRSSpublisher;
	global $wgThumbLimits;
	global $orgfilename;

	if (!isset($wgRSSpublisher['defaultpic'])) $wgRSSpublisher['defaultpic'] = $wgLogo;
	
	$conditions = 'il_from = "'.$pageid.'" and (il_to LIKE "%.png" or il_to LIKE "%.jpg" or il_to LIKE "%.jpeg" or il_to LIKE "%.gif" or il_to LIKE "%.svg")';

	$dbr = wfGetDB( DB_SLAVE );
	$res = $dbr->select('categorylinks', array('cl_from'), 'cl_to = "RSSpublisher_blacklist" and cl_type = "file"', $fname = 'Database::select',array('ORDER BY' => 'cl_from'));

////not in
//	$blacklist_cl_from .= 'page_id NOT IN (';
//	foreach($res as $row) $blacklist_cl_from .= $row->cl_from.',';
//	$blacklist_cl_from = substr($blacklist_cl_from,0,-1).')';

	foreach($res as $row) $blacklist_cl_from .= 'page_id = '.$row->cl_from.' or ';	
	$blacklist_cl_from = substr($blacklist_cl_from,0,-3);
	
	$dbr = wfGetDB( DB_SLAVE );
	$res = $dbr->select('page', array('page_title'), $blacklist_cl_from, $fname = 'Database::select',array());
	foreach($res as $row) $conditions .= ' and il_to != "'.$row->page_title.'"';

	
	$dbr = wfGetDB( DB_SLAVE );
	$res = $dbr->select('imagelinks', array('il_to'), $conditions, $fname = 'Database::select',array('ORDER BY' => 'RAND()'));
	
	$i=0;
	foreach($res as $row) $filelist[] = $row->il_to;
	
	$orgfilename = $datei['name'] = $filelist[0];
	
	if (strlen($datei['name']) > 0){
		$foldername = $datei['name'];
		if (substr($datei['name'],-4) == '.svg') $datei['name'] .= '.png';
		
		if (isset($wgThumbLimits)) $prefixes = $wgThumbLimits;
		else $prefixes = $wgRSSpublisher['thumbsizes'];
		
		foreach($prefixes as $prefix){
			$pfade[] = array('path' => 'images/thumb/'.$foldername.'/'.$prefix.'px-'.$datei['name'], 'file' => $datei['name']);
			$pfade[] = array('path' => 'images/thumb/'.substr(md5($foldername),0,1).'/'.substr(md5($foldername),0,2).'/'.$foldername.'/'.$prefix.'px-'.$datei['name'], 'file' => $datei['name']);
			if ($wgUseInstantCommons == true) $pfade[] = array('path' => 'http://upload.wikimedia.org/wikipedia/commons/thumb/'.substr(md5($foldername),0,1).'/'.substr(md5($foldername),0,2).'/'.$foldername.'/'.$prefix.'px-'.$datei['name'], 'file' => $datei['name']);
		}
		$pfade[] = $wgRSSpublisher['defaultpic'];

		$i=0;
		foreach($pfade as $pfad){
			if(file_exists($pfad['path'])) {
				$file = $pfad;
				break;
			}
		}
	}
	
	return($file);
}
?>