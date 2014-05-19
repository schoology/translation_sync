<?php

class Zendesk{
	protected $auth = '';	
	protected $_body_key = 0;
	protected $_body_val = 1;
	protected $_title_key = 2;
	protected $_title_val = 3;
	protected $field_separator = ',';
	protected $key_delimiter = ':';

	public function __construct($auth){
		$this->auth = $auth;
	}

	protected function getSmartlingMeta(){
		$str = "# smartling.source_key_paths=" . ($this->_body_key + 1) . "," . ($this->_title_key + 1) . "\n";
		$str .= "# smartling.paths=" . ($this->_body_val + 1) . "," . ($this->_title_val + 1) . "\n";		
		$str .= "# smartling.string_format_paths=html:" . ($this->_body_val + 1) . ",html:" . ($this->_title_val + 1) . "\n";
		return $str;
	}

	public function request($method, $resource, $params = array()){
		$base_url = "https://schoology.zendesk.com/api/v2/";
		$url = $base_url . $resource;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_USERPWD, ZENDESK_AUTH);
		$headers = array("Content-Type: application/json", "Accept: application/json");
		
		if(in_array($method, array('POST', 'PUT'))){
			switch($method){
				case 'POST':
					$curl_options[ CURLOPT_POST ] = TRUE; 
        			$curl_options[ CURLOPT_CUSTOMREQUEST ] = 'POST';
					break;
				case 'PUT':
					$curl_options[ CURLOPT_CUSTOMREQUEST ] = 'PUT'; 
					break;
			}
			$body = json_encode($params);
			$headers[] = "Content-Length: " . strlen($body);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		$res = curl_exec($ch);
		$info = curl_getinfo($ch);
		return array('body' => json_decode($res), 'http_code' => $info['http_code']);
	}

	public function downloadHelpCenterSource($path, $source_type){
		$fw = fopen($path, 'w');		
		fputs($fw, $this->getSmartlingMeta());
		$iterations = 0;
		$source_type = lcfirst($source_type);
		$page = NULL;

		do{
			if(!isset($page)){
				$page = 1;
			}			
			$info = $this->request('GET', "help_center/" . $source_type . ".json?include=translations&page=" . $page);
			if($info['http_code'] > 300){
				return;
			}
			$data = $info['body'];
			$page_count = $data->page_count;
			foreach($data->{$source_type} as $source){
				$row = array();
				$english = FALSE;
				$content_id = '';
				foreach($source->translations as $trans){
					if($trans->locale == 'en-us'){
						$english = $trans;
						break;
					}				
				}	
				if($english){
					$content_id = $source_type . $this->key_delimiter . $english->source_id;
					$row[$this->_body_key] = $content_id;
					$row[$this->_body_val] = $english->body;
					$title_content_id = 'title' . $this->key_delimiter . $content_id;
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

	public function syncHelpCenterSource($path, $locale, $source_type){
		$fr = fopen($path, 'r');
		while($row = fgetcsv($fr)){
			$body_key = $row[$this->_body_key];
			$body_val = $row[$this->_body_val];
			$title_key = $row[$this->_title_key];
			$title_val = $row[$this->_title_val];

			if(!$body_val || !$title_val){
				continue;
			}

			list($content_type, $content_id) = explode($this->key_delimiter, $body_key);
			$info = $this->request('GET', "help_center/" . $source_type . "/" . $content_id . "/translations/" .$locale . ".json");
			$translation = array(
				'locale' => $locale,
				'title' => $title_val,
				'body' => $body_val
			);
			// No translation exists - create it
			if($info['http_code'] == 404){

				$this->request('POST', "help_center/" . $source_type . "/" . $content_id . "/translations.json", $translation);
			}
			// Something weird happened
			else if($info['http_code'] > 300){
				continue;
			}
			// Update it
			else{
				$this->request('PUT', "help_center/" . $source_type . "/" . $content_id . "/translations/" . $locale . ".json", $translation);
			}		
		}
	}
}