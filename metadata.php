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
	if($j < 26)
		$cell = sprintf("%c1", 65+$j);
	elseif($j < 52)
		$cell = sprintf("A%c1", 65+($j % 26));
	elseif($j < 78)
		$cell = sprintf("B%c1", 65+($j % 26));
	else
		$cell = sprintf("C%c1", 65+($j % 26));
	$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $data);
	++$j;
}
$pageSize = 500;
$pager->pageSize = $pageSize;
$lastCreatedAt = 0;
$lastEntryIds = "";
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
	$entryIds = "";
	foreach($results->objects as $entry) {
		if($entryIds != "")
			$entryIds .= ",";
		$entryIds .= $entry->id;
		$j = 0;
		foreach($entry as $value) {
			if($j < 26)
				$cell = sprintf("%c%d", 65+$j, $k);
			elseif($j < 52)
				$cell = sprintf("A%c%d", 65+($j % 26), $k);
			else
				$cell = sprintf("B%c%d", 65+($j % 26), $k);
			if(is_string($value))
				$objPHPExcel->setActiveSheetIndex(0)->setCellValue($cell, $value);
			++$j;
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
	print '<pre>'.print_r($results, true).'</pre>';
	$metadataFilter = new KalturaMetadataFilter();
	$metadataFilter->objectIdIn = $entryIds;
	$results = $client->metadata->listAction($metadataFilter, $pager);
// 	print $entryIds.'<br>';
// 	print '<pre>'.print_r($results, true).'</pre>';
}
	$objPHPExcel->setActiveSheetIndex(0);
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('metadata.xlsx');