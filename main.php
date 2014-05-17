<?php
define('ZENDESK_AUTH', 'tim@schoology.com/token:OpKrcNSGp3JYGxMf06SV2nbNT6FL2f5hXdOtnoeT');
define('SMARTLING_PROJECT_ID', 'b8b956733');
define('SMARTLING_PROJECT_KEY', '2f535c60-bf14-47b4-9774-0e5464c09005');
define('ZENDESK_ARTICLES_FILE_URI', 'zendesk_smartling_articles.csv');
require_once 'zendesk.class.php';
$zendesk = new Zendesk(ZENDESK_AUTH);

$path = 'sent_trans_files/' . ZENDESK_ARTICLES_FILE_URI;
$zendesk->downloadHelpCenterArticles($path);

require_once 'smartlingAPI.class.php';
$smartling = new SmartlingAPI(SMARTLING_PROJECT_KEY, SMARTLING_PROJECT_ID);

$upload_params = array(
	'file' => $path,
	'fileUri' => ZENDESK_ARTICLES_FILE_URI,
	'fileType' => 'json',
	'approved' => TRUE
);
$result = $smartling->uploadFile($path, 'csv', ZENDESK_ARTICLES_FILE_URI);
print_r($result);