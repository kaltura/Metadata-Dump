<?php
ini_set('display_errors',1);
error_reporting(E_ALL);
require_once('lib/php5/KalturaClient.php');
require_once 'lib/PHPExcel/Classes/PHPExcel.php';
$config = new KalturaConfiguration($_REQUEST['partnerId']);
$config->serviceUrl = 'http://www.kaltura.com/';
$client = new KalturaClient($config);
$client->setKs($_REQUEST['session']);
$pager = new KalturaFilterPager();
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator($client->partner->getInfo()->adminEmail)
							 ->setLastModifiedBy($client->partner->getInfo()->adminEmail)
							 ->setTitle("Metadata")
							 ->setDescription("Metadata");
$j = 0;
$filter = new KalturaMediaEntryFilter();
$filter->orderBy = "-createdAt";
$results = $client->media->listAction($filter, $pager);
$entry = $results->objects[0];
foreach($entry as $data => $value) {
	$cell = generateCell($j, 1);
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $data);
	++$j;
}
$pageSize = 5;
$pager->pageSize = $pageSize;
$lastCreatedAt = 0;
$lastEntryIds = "";
$metaFound = false;
$cont = true;
$k = 2;
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
	foreach($results->objects as $entry) {
		$j = 0;
		foreach($entry as $value) {
			$cell = generateCell($j, $k);
			if(is_string($value) || is_numeric($value))
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $value);
			++$j;
		}
		$metadataFilter = new KalturaMetadataFilter();
		$metadataFilter->objectIdIn = $entry->id;
		$metaResult = $client->metadata->listAction($metadataFilter, $pager)->objects;
		if(isset($metaResult[0])) {
			print '<pre>'.print_r($metaResult[0], true).'</pre>';
			foreach($metaResult[0] as $key => $value) {
				if($metaFound === false) {
					$cell = generateCell($j, 1);
					$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $key);
					if(strcmp($key, 'status') == 0)
						$metaFound = true;
				}
				$cell = generateCell($j, $k);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $value);
				++$j;
			}
			--$j;
			$profileId = $metaResult[0]->id;
			$metadata = $client->metadata->get($profileId);				
			$xml = simplexml_load_string($metadata->xml);
			foreach($xml as $key => $value) {
				$cell = generateCell($j, $k);
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $value);
				++$j;
			}
		}
		//Keeps a tally of which creation dates were examined
		//and which entry ids have already been seen
		if($lastCreatedAt != $entry->createdAt)
			$lastEntryIds = "";
		if($lastEntryIds != "")
			$lastEntryIds .= ",";
		$lastEntryIds .= $entry->id;
		$lastCreatedAt = $entry->createdAt;
		++$k;
	}
 	$cont = false;
}
$objPHPExcel->setActiveSheetIndex(0);
$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('metadata.xlsx');
	
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