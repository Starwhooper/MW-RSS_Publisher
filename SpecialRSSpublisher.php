<?php
include('chooseimagefromarticle.func.php');
include('cleantext.func.php');

class SpecialRSSpublisher extends SpecialPage {
	function __construct() {
		parent::__construct( 'RSSpublisher' );
	}
 
	function execute( $par ) {
		global $wgArticlePath;
		global $wgEmergencyContact;
		global $wgLanguageCode;
		global $wgFavicon;
		global $wgOut;
		global $wgRSSpublisher;
		global $wgRequest;
		global $wgServer;
		global $wgSitename; 
		
		$this->setHeaders();
		
		$wgOut->disable();
		
		if (isset($wgRSSpublisher['namespaces'])){
			$conditions = '(';
			foreach($wgRSSpublisher['namespaces'] as $namespace) $conditions = 'page_namespace = '.$namespace.' or ';
			$conditions = substr($conditions,0,-4);
			$conditions = ')';
		}
		else $conditions = 'page_namespace = 0';
		
		if (!isset($wgRSSpublisher['showredirects']) or $wgRSSpublisher['showredirects'] == false) $conditions .= ' and page_is_redirect = 0';
		
		$options['ORDER BY'] = 'page_touched DESC';
		if (isset($wgRSSpublisher['limit'])){
			if ($wgRSSpublisher['limit'] == 'unlimited');
			else $options['LIMIT'] = $wgRSSpublisher['limit'];
		}
		else $options['LIMIT'] = 30;
		
		$i = 0;
		$datetimeformat = 'D, d M Y H:i:s O';
		
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select('page', array('page_title','page_touched','page_id','page_latest'), $conditions, $fname = 'Database::select',$options);		
		foreach($res as $row) {
			$items[$i]['title'] = str_replace('_', ' ',$row->page_title);
			$items[$i]['link'] = $wgServer . str_replace('$1', urlencode($row->page_title), $wgArticlePath);
			$items[$i]['pubdate'] = date($datetimeformat, strtotime($row->page_touched));
			if ($row->page_touched > $biggest_page_touched) $biggest_page_touched = $row->page_touched;
			$imageinfo = chooseimagefromarticle($row->page_id);
			if($imageinfo != NULL) {
				$items[$i]['imageurl'] = $imageinfo['htmlurl'];
				$items[$i]['imagesize'] = $imageinfo['size'];
				$items[$i]['imagetype'] = $imageinfo['type'];
				$items[$i]['description'] = wfMsg('rsspublisher-imagelicense');
			}
			$items[$i]['guid'] = $wgServer . str_replace('$1', urlencode($row->page_title), $wgArticlePath) . '&amp;oldid=' . $row->page_latest;
			
			$dbr_revision = wfGetDB( DB_SLAVE );
			$res_revision = $dbr_revision->select('revision', array('rev_text_id'), array('rev_page = "'.$row->page_id.'"'), $fname = 'Database::select', array('LIMIT' => 1, 'ORDER BY' => 'rev_timestamp DESC'));		

			foreach($res_revision as $row_revision) {
				$dbr_old_text = wfGetDB( DB_SLAVE );
				$res_old_text = $dbr_old_text->select('text', array('old_text'), array('old_id = "'.$row_revision->rev_text_id.'"'), $fname = 'Database::select', array('LIMIT' => 1));		

				foreach($res_old_text as $row_old_text) {
					$oldtext = $row_old_text->old_text;
					if (strlen($oldtext) > 100) {
						$items[$i]['description'] .= ' '.cleantext($oldtext,$items[$i]['title']);					
					}
				}
			}
			$i++;
		}
		
		$channel['pubdate'] = date($datetimeformat, strtotime($biggest_page_touched));
		
			
		//OUTPUT
		//header
		header('Content-type: application/rss+xml');
		header('Content-Disposition: attachment; filename="thwiki.xml"');
		
		echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		echo "<rss version=\"2.0\" xmlns:atom=\"http://www.w3.org/2005/Atom\">\n";
		
		//Channel Information
		echo "<channel>\n";
		echo "\t<title>$wgSitename</title>\n";
		echo "\t<link>$wgServer</link>\n";
		if (isset($wgRSSpublisher['pagedescription'])) echo "\t<description>".$wgRSSpublisher['pagedescription']."</description>\n";
		echo "\t<language>$wgLanguageCode</language>\n";
		echo "\t<copyright>$wgSitename</copyright>\n";
		echo "\t<pubDate>".$channel['pubdate']."</pubDate>\n";
		echo "\t<lastBuildDate>".date($datetimeformat)."</lastBuildDate>\n";
		echo "\t<docs>http://thwiki.org/t=Special:RSSpublisher</docs>\n";
		echo "\t<generator>Mediawiki RSSpublicher Extrension ".$wgRSSpublisher['version']." from Thiemo Schuff</generator>\n";
		echo "\t<managingEditor>$wgEmergencyContact($wgSitename)</managingEditor>\n";
		echo "\t<webMaster>$wgEmergencyContact($wgSitename)</webMaster>\n";
		echo "\t<image>\n\t\t<url>$wgServer$wgFavicon</url>\n\t\t<title>$wgSitename</title>\n\t\t<link>$wgServer</link>\n\t</image>\n";
		echo "\t<atom:link href=\"http://thwiki.org/t=Spezial:RSSpublisher\" rel=\"self\" type=\"application/rss+xml\" />\n";


		foreach	($items as $item){
			echo "\t\t<item>\n";
			echo "\t\t\t<title>".trim(wfMsg('rsspublisher-articleedit',$item['title']))."</title>\n";
			echo "\t\t\t<link>".$item['link']."</link>\n";
			if (isset($item['imageurl'])) echo "\t\t\t<enclosure url=\"".$item['imageurl']."\" length=\"".$item['imagesize']."\" type=\"".$item['imagetype']."\" />\n";
			echo "\t\t\t<description><![CDATA[".trim($item['description'])."]]></description>\n";
			echo "\t\t\t<pubDate>".$item['pubdate']."</pubDate>\n";
			echo "\t\t\t<guid>".$item['guid']."</guid>\n";
			
			echo "\t\t</item>\n";
		}

		//footer
		echo "\t</channel>\n</rss>\n";
	}
}