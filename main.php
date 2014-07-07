<?php

$configs = parse_ini_file('config.ini', TRUE);
$projects = $configs['smartling_languages'];
	
require_once 'zendesk.class.php';
require_once 'smartlingAPI.class.php';
define('ZENDESK_AUTH', $configs['zendesk']['email'] . '/token:' . $configs['zendesk']['token']);
define('ZENDESK_SUBDOMAIN', $configs['zendesk']['domain']);
define('ZENDESK_ARTICLES_FILE_URI', 'zendesk_smartling_articles.csv');
define('ZENDESK_SECTIONS_FILE_URI', 'zendesk_smartling_sections.csv');
define('ZENDESK_CATEGORIES_FILE_URI', 'zendesk_smartling_categories.csv');

$action = $argv[1];
$application_path = $argv[2];

if($action == 'view'){
	$zendesk = new Zendesk(ZENDESK_AUTH);
	$info = $zendesk->request('GET', $argv[2]);
	print_r($info);
	exit;
}

if(!in_array($action, array('upload', 'download'))){
	return;
}

$content_types = array();
$content_types['articles'] = ZENDESK_ARTICLES_FILE_URI;
$content_types['sections'] = ZENDESK_SECTIONS_FILE_URI;
$content_types['categories'] = ZENDESK_CATEGORIES_FILE_URI;

foreach($projects as $locale => $smartling_info){
	$dir = $application_path . '/' . $action . 's/' . $locale;
	if(!file_exists($dir)){
		mkdir($dir, 0775);
	}

	foreach($projects as $locale => $smartling_info){
		$zendesk = new Zendesk(ZENDESK_AUTH);
		$smartling = new SmartlingAPI($smartling_info['project_key'], $smartling_info['project_id']);
		foreach($content_types as $content_type => $file_uri){
			$path = $dir . '/' . $file_uri;
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