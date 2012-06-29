<?php
set_time_limit(0);
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: inline; filename=\"categories.xls\"");
header("Set-Cookie: fileDownload=true; path=/");
echo stripslashes("<?xml version=\"1.0\" encoding=\"UTF-8\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">");
echo "\n<Worksheet ss:Name=\"categories\">\n<Table>\n";
require_once('lib/php5/KalturaClient.php');
$config = new KalturaConfiguration($_REQUEST['partnerId']);
$config->serviceUrl = 'http://www.kaltura.com/';
$client = new KalturaClient($config);
$client->setKs($_REQUEST['session']);
$filter = null;
$categories = $client->category->listAction($filter)->objects;
$categoryFound = false;
foreach($categories as $category) {
	if(!$categoryFound) {
		echo "<Row>\n";
		foreach($categories[0] as $key => $value) {
			echo "<Cell><Data ss:Type=\"String\">".$key."</Data></Cell>\n";
		}
		echo "</Row>\n";
		$categoryFound = true;
	}
	echo "<Row>\n";
	foreach($category as $value)
		echo "<Cell><Data ss:Type=\"String\">".$value."</Data></Cell>\n";
	echo "</Row>\n";
}
echo "</Table>\n</Worksheet>\n";
echo "</Workbook>";