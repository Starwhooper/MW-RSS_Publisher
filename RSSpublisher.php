<?php
//Website http://www.mediawiki.org/wiki/Extension:rsspublisher
//cc-by-sa 4.0 by Thiemo Schuff

if (!defined('MEDIAWIKI')) {
	echo <<<EOT
To install my extension, put the following line in LocalSettings.php:
require_once( "$IP/extensions/RSSpublisher/RSSpublisher.php" );
EOT;
	exit( 1 );
}

$wgExtensionCredits['specialpage'][] = array(
	'path'           => __FILE__,
	'name'           => 'RSSpublisher',
//	'description'    => 'Send e-mail over Mobile-Upload by new articelversions to Facebook, to post a new text and picture on the wall',
	'descriptionmsg' => 'rsspublisher-desc',
	'author'         => '[http://www.mediawiki.org/wiki/User:Starwhooper Thiemo Schuff]',
	'url'            => 'https://www.mediawiki.org/wiki/Extension:RSSpublisher',
	'version'        => '0.3 beta build 20140906',
	'license-name' => 'cc-by-sa-de 4.0'
);

$dir = dirname(__FILE__) . '/';

$wgAutoloadClasses['SpecialRSSpublisher'] = $dir . 'SpecialRSSpublisher.php';
$wgExtensionMessagesFiles['RSSpublisher'] = $dir . 'RSSpublisher.i18n.php';
$wgSpecialPages['RSSpublisher'] = 'SpecialRSSpublisher';
$wgSpecialPageGroups['RSSpublisher'] = 'other';

$wgHooks['SkinBuildSidebar'][] = 'AddRSStoSidebar';

$wgFooterIcons['valitatedby']['w3c'] = array("src" => $wgServer."/extensions/RSSpublisher/valid-rss-rogers.png", "url" => "http://validator.w3.org/feed/check.cgi?url=".$wgServer."/t%253DSpecial%253ARSSpublisher", "alt" => "[Valid RSS]");
 
function AddRSStoSidebar( $skin, &$bar ) {
	global $wgRSSpublisher;
	global $wgServer;
	if (!isset($wgRSSpublisher['showatsidebarnavigation']) or $wgRSSpublisher['showatsidebarnavigation'] == true) $bar['Navigation'][] = array('text' => "RSS", 'href' => "t=Special:RSSpublisher");	
	if (!isset($wgRSSpublisher['showatsidebarrss']) or $wgRSSpublisher['showatsidebarrss'] == true); $bar['RSS'] = '<a href="'.$wgServer.'/t=Special:RSSpublisher"><img src="http://www.w3schools.com/rss/rss.gif" width="36" height="14" alt="RSS"></a>';
    return true;
}