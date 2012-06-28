<?php
set_time_limit(0);
header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: inline; filename=\"metadata.xls\"");
echo stripslashes("<?xml version=\"1.0\" encoding=\"UTF-8\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">");
echo "\n<Worksheet ss:Name=\"metadata\">\n<Table>\n";

function generateCell($column, $row) {
	$cell = "";
	if($column/26 < 1) {
		$cell = sprintf("%c%d", 65 + $column, $row);
	}
	else {
		$cell = sprintf("%c%c%d", 65 + (int)($column / 26 - 1), 65 + ($column % 26), $row);
	}
	return $cell;
}

require_once('lib/php5/KalturaClient.php');
$config = new KalturaConfiguration($_REQUEST['partnerId']);
$config->serviceUrl = 'http://www.kaltura.com/';
$client = new KalturaClient($config);
$client->setKs($_REQUEST['session']);

echo "<Row>\n";
$pager = new KalturaFilterPager();
$pager->pageSize = 1;
$filter = new KalturaMediaEntryFilter();
$filter->orderBy = "-createdAt";
$results = $client->media->listAction($filter, $pager);
$entry = $results->objects[0];
foreach($entry as $data => $value) {
	echo "<Cell><Data ss:Type=\"String\">".$data."</Data></Cell>\n";
}
$pager = new KalturaFilterPager();
$pager->pageSize = 500;
while($cont) {
	//Instead of using a page index, the entries are retrieved by creation date
	//This is the only way to ensure that the server retrieves all of the entries
	$filter = new KalturaMediaEntryFilter();
	$filter->orderBy = "-createdAt";
	//Ignores entries that have already been parsed
	if($lastCreatedAt != 0)
		$filter->createdAtLessThanOrEqual = $lastCreatedAt;
	if($lastEntryIds != "")
		$filter->idNotIn = $lastEntryIds;
	$results = $client->media->listAction($filter, $pager);
	//If no entries are retrieved the loop may end
	if(count($results->objects) == 0) {
		$cont = false;
	}
	else {
		foreach($results->objects as $entry) {
				
		}

		if($lastCreatedAt != $entry->createdAt)
			$lastEntryIds = "";
		if($lastEntryIds != "")
			$lastEntryIds .= ",";
		$lastEntryIds .= $entry->id;
		$lastCreatedAt = $entry->createdAt;
	}
}
echo "</Row>\n";

echo "</Table>\n</Worksheet>\n";
echo "</Workbook>";

$pager = new KalturaFilterPager();
$filter = new KalturaMediaEntryFilter();
$filter->orderBy = "-createdAt";
$results = $client->media->listAction($filter, $pager);
$entry = $results->objects[0];
$excel = array();
$columns = array();
foreach($entry as $data => $value) {
	$columns[] = $data;
}
$excel[] = $columns;
$pageSize = 500;
$pager->pageSize = $pageSize;
$lastCreatedAt = 0;
$lastEntryIds = "";
$metaFound = false;
$cont = true;
while($cont) {
	//Instead of using a page index, the entries are retrieved by creation date
	//This is the only way to ensure that the server retrieves all of the entries
	$filter = new KalturaMediaEntryFilter();
	$filter->orderBy = "-createdAt";
	//Ignores entries that have already been parsed
	if($lastCreatedAt != 0)
		$filter->createdAtLessThanOrEqual = $lastCreatedAt;
	if($lastEntryIds != "")
			$filter->idNotIn = $lastEntryIds;
	$results = $client->media->listAction($filter, $pager);
	//If no entries are retrieved the loop may end
	if(count($results->objects) == 0) {
		$cont = false;
	}
	else {
		foreach($results->objects as $entry) {
			$row = array();
			foreach($entry as $value) {
				if(is_string($value) || is_numeric($value))
					$row[] = $value;
				else
					$row[] = "";
			}
			$excel[] = $row;
//			print '<pre>'.print_r($excel, true).'</pre>';
// 			$metadataFilter = new KalturaMetadataFilter();
// 			$metadataFilter->objectIdIn = $entry->id;
// 			$pager = new KalturaFilterPager();
// 			$pager->$pageSize = 10;
// 			$metaResults = $client->metadata->listAction($metadataFilter, $pager)->objects;
// 			if(array_key_exists(0, $metaResults)) {
// 				$firstMeta = true;
// 				foreach($metaResults as $metaResult) {
// 					if($firstMeta) {
// 						foreach($metaResult as $key => $value) {
// 							if($metaFound === false) {
// 								$cell = generateCell($j, 1);
// 								$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $key);
// 								if(strcmp($key, 'status') == 0)
// 									$metaFound = true;
// 							}
// 							if($key != 'xml') {
// 								$cell = generateCell($j, $k);
// 								$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $value);
// 								++$j;
// 							}
// 						}
// 						$firstMeta = false;
// 					}
// 					$metadataProfileId = $metaResult->metadataProfileId;
// 					$xml = simplexml_load_string($metaResult->xml);
// 					foreach($xml as $key => $value) {
// 						$index = $metadataProfileId.'_'.$key;
// 						if(!array_key_exists($index, $metaKeys)) {
// 							$metaKeys[$index] = $keyCount;
// 							$cell = generateCell($j + $keyCount, 1);
// 							$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $index);
// 							++$keyCount;
// 						}
// 						$cell = generateCell($j + $metaKeys[$index], $k);
// 						$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $value);
// 					}
// 				}
// 			}
			//Keeps a tally of which creation dates were examined
			//and which entry ids have already been seen
			if($lastCreatedAt != $entry->createdAt)
				$lastEntryIds = "";
			if($lastEntryIds != "")
				$lastEntryIds .= ",";
			$lastEntryIds .= $entry->id;
			$lastCreatedAt = $entry->createdAt;
		}
	}
}
require 'php-excel.class.php';
$xls = new Excel_XML('UTF-8', false, 'metadata');
$xls->addArray($excel);
$xls->generateXML('metadata');