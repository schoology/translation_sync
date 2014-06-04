<?php

$projects = array();
$projects['es'] = array(
	'project_id' => 'b8b956733',
	'project_key' => '2f535c60-bf14-47b4-9774-0e5464c09005'
);

define('ZENDESK_AUTH', 'tim@schoology.com/token:OpKrcNSGp3JYGxMf06SV2nbNT6FL2f5hXdOtnoeT');
define('ZENDESK_ARTICLES_FILE_URI', 'zendesk_smartling_articles.csv');
define('ZENDESK_SECTIONS_FILE_URI', 'zendesk_smartling_sections.csv');
define('ZENDESK_CATEGORIES_FILE_URI', 'zendesk_smartling_categories.csv');

$action = $argv[1];

if(!in_array($action, array('upload', 'download'))){
	return;
}

$content_types = array();
$content_types['articles'] = ZENDESK_ARTICLES_FILE_URI;
$content_types['sections'] = ZENDESK_SECTIONS_FILE_URI;
$content_types['categories'] = ZENDESK_CATEGORIES_FILE_URI;

foreach($projects as $locale => $smartling_info){
	$dir = $action . 's/' . $locale;
	if(!file_exists($dir)){
		mkdir($dir, 0775);
	}
	
	require_once 'zendesk.class.php';
	require_once 'smartlingAPI.class.php';

	foreach($projects as $locale => $smartling_info){

		foreach($content_types as $content_type => $file_uri){
			$path = $dir . '/' . $file_uri;
			$zendesk = new Zendesk(ZENDESK_AUTH);
			$smartling = new SmartlingAPI($smartling_info['project_key'], $smartling_info['project_id']);
			switch($action){
				case 'upload':	

					$zendesk->downloadHelpCenterSource($path, $content_type);

					$upload_params = array(
						'file' => $path,
						'fileUri' => $file_uri,
						'fileType' => 'json',
						'approved' => TRUE
					);
					$result = $smartling->uploadFile($path, 'csv', $file_uri, TRUE);
					echo "Upload Result: ";
					print_r($result);
					break;
				case 'download':
					$fh = fopen($path, 'w');
					$download = $smartling->downloadFile($file_uri, 'published', $locale, $fh);	
					$zendesk->syncHelpCenterSource($path, $locale, $content_type);
					break;
			}
		
		}		
	}
}