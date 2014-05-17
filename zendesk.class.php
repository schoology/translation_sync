<?php

class Zendesk{
	protected $auth = '';	
	protected $_body_key = 0;
	protected $_body_val = 1;
	protected $_title_key = 2;
	protected $_title_val = 3;
	protected $field_separator = ',';

	public function __construct($auth){
		$this->auth = $auth;
	}

	protected function getSmartlingMeta(){
		$str = "# smartling.source_key_paths=" . ($this->_body_key + 1) . "," . ($this->_title_key + 1) . "\n";
		$str .= "# smartling.paths=" . ($this->_body_val + 1) . "," . ($this->_title_val + 1) . "\n";		
		$str .= "# smartling.string_format_paths=html:" . ($this->_body_key + 1) . ",html:" . ($this->_title_key + 1) . "\n";
		return $str;
	}

	public function downloadHelpCenterArticles($path){
		$fw = fopen($path, 'w');		
		fputs($fw, $this->getSmartlingMeta());
		$iterations = 0;
		do{
			if(!isset($page)){
				$page = 1;
			}
			$out = array();
			$res = exec('curl -u ' . ZENDESK_AUTH . ' "" -H "Content-Type:application/json"', $out);
			$url = "https://schoology.zendesk.com/api/v2/help_center/articles.json?include=translations&page=" . $page;		
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_USERPWD, ZENDESK_AUTH);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
			$res = curl_exec($ch);
			$info = json_decode($res);
			$page_count = $info->page_count;
			foreach($info->articles as $article){
				$row = array();
				$english = FALSE;
				$content_id = '';
				foreach($article->translations as $trans){
					if($trans->locale == 'en-us'){
						$english = $trans;
						break;
					}				
				}	
				if($english){
					$content_id = $english->source_type . ':' . $english->source_id;
					$row[$this->_body_key] = $content_id;
					$row[$this->_body_val] = $english->body;
					$title_content_id = 'title:' . $content_id;
					$row[$this->_title_key] = $title_content_id;
					$row[$this->_title_val] = $english->title;					
					fputcsv($fw, $row, $this->field_separator);
				}	
			}	
			$page += 1;
			$iterations += 1;
		}while($page <= $page_count && $iterations < 100);
		return $path;
	}
}