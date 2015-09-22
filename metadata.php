<?php
//Sets the header so that the document is download as a .xls file and ensures
//that it will not timeout before it is finished creating the file
set_time_limit(0);

function xmlspecialchars($text) {
   return str_replace('&#039;', '&apos;', htmlspecialchars($text, ENT_QUOTES));
}

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: inline; filename=\"metadata.xls\"");
header("Set-Cookie: fileDownload=true; path=/");
echo stripslashes("<?xml version=\"1.0\" encoding=\"UTF-8\"?\>\n<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:x=\"urn:schemas-microsoft-com:office:excel\" xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\" xmlns:html=\"http://www.w3.org/TR/REC-html40\">");
echo "\n<Worksheet ss:Name=\"metadata\">\n<Table>\n";
//Includes the client library and starts a Kaltura session to access the API
//More informatation about this process can be found at
//http://knowledge.kaltura.com/introduction-kaltura-client-libraries
require_once('lib/php5/KalturaClient.php');
$config = new KalturaConfiguration($_REQUEST['partnerId']);
$config->serviceUrl = 'https://www.kaltura.com/';
$client = new KalturaClient($config);
$client->setKs($_REQUEST['session']);
echo "<Row>\n";
$pager = new KalturaFilterPager();
$pager->pageSize = 1;
$filter = new KalturaMediaEntryFilter();
$filter->orderBy = "-createdAt";
$results = $client->media->listAction($filter, $pager);
//Creates the top row for the excel file
$entry = $results->objects[0];
foreach($entry as $data => $value) {
	echo "<Cell><Data ss:Type=\"String\">".$data."</Data></Cell>\n";
}
$pager = new KalturaFilterPager();
$pager->pageSize = 400;
$lastCreatedAt = 0;
$lastEntryIds = "";
$metaFound = false;
$metaKeys = array();
$keyCount = 0;
$cont = true;
//This first loop simply parses every media entry to retrieve the full list of
//custom metadata categories. This needs to be done seperately because the organization
//of the rest of the spreadsheet is determined by these categories.
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
	//sleep(1);
	$results = $client->media->listAction($filter, $pager);
	//If no entries are retrieved the loop may end
	if(count($results->objects) == 0)
		$cont = false;
	else {
		$entryIds = "";
		foreach($results->objects as $entry)
			$entryIds .= $entry->id.',';
		$metadataFilter = new KalturaMetadataFilter();
		$metadataFilter->objectIdIn = $entryIds;
		//sleep(1);
		$metaResults = $client->metadata->listAction($metadataFilter, $pager)->objects;
		//Retrives the general metadata categories such as version and metadataProfileId
		if($metaFound == false) {
			if(count($metaResults) > 0) {
				foreach($metaResults[0] as $key => $value) {
					echo "<Cell><Data ss:Type=\"String\">".xmlspecialchars($key)."</Data></Cell>\n";
					if(strcmp($key, 'status') == 0) {
							$metaFound = true;
							break;
					}
				}
			}
		}
		//Retrieves the custom metadata categories that are specific to each metadata profile
		foreach($metaResults as $metaResult) {
			$metadataProfileId = $metaResult->metadataProfileId;
			$xml = simplexml_load_string($metaResult->xml);
			foreach($xml as $key => $value) {
				$index = $metadataProfileId.'_'.$key;
				if(!array_key_exists($index, $metaKeys)) {
					$metaKeys[$index] = $keyCount++;
					echo "<Cell><Data ss:Type=\"String\">".xmlspecialchars($index)."</Data></Cell>\n";
				}
			}
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
//Retrieves all the information for the media entries including their basic and custom metadata
$lastCreatedAt = 0;
$lastEntryIds = "";
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
	//sleep(1);
	$results = $client->media->listAction($filter, $pager);
	//If no entries are retrieved the loop may end
	if(count($results->objects) == 0)
		$cont = false;
	else {
		foreach($results->objects as $entry) {
			$metaFound = false;
			echo "<Row>\n";
			//Retrieves the basic metadata
			foreach($entry as $data => $value) {
				if(is_string($value) || is_numeric($value))
					echo "<Cell><Data ss:Type=\"String\">".xmlspecialchars($value)."</Data></Cell>\n";
				else 
					echo "<Cell><Data ss:Type=\"String\"></Data></Cell>\n";
			}
			$metadataFilter = new KalturaMetadataFilter();
			$metadataFilter->objectIdIn = $entry->id;
			//sleep(1);
			$metaResults = $client->metadata->listAction($metadataFilter, $pager)->objects;
			$customMeta = array();
			//Retrieves the general/custom metadata information for each media entry
			//and organizes it to ensure that the columns are aligned for each category
			foreach($metaResults as $metaResult) {
				if(!$metaFound) {
					foreach($metaResult as $key => $value) {
						if($key != 'xml')
							echo "<Cell><Data ss:Type=\"String\">".xmlspecialchars($value)."</Data></Cell>\n";
					}
					$metaFound = true;
				}
				$metadataProfileId = $metaResult->metadataProfileId;
				$xml = simplexml_load_string($metaResult->xml);
				foreach($xml as $key => $value) {
					$index = $metadataProfileId.'_'.$key;
					if(array_key_exists($index, $metaKeys))
						$customMeta[$metaKeys[$index]] = $value;
				}
			}
			$count = 0;
			foreach($customMeta as $key => $value) {
				while(!array_key_exists($count, $customMeta)) {
					echo "<Cell><Data ss:Type=\"String\"></Data></Cell>\n";
					++$count;
				}
				echo "<Cell><Data ss:Type=\"String\">".xmlspecialchars($customMeta[$count])."</Data></Cell>\n";
				++$count;
			}
			echo "</Row>\n";
		}
		if($lastCreatedAt != $entry->createdAt)
			$lastEntryIds = "";
		if($lastEntryIds != "")
			$lastEntryIds .= ",";
		$lastEntryIds .= $entry->id;
		$lastCreatedAt = $entry->createdAt;
	}
}
echo "</Table>\n</Worksheet>\n";
echo "</Workbook>";
