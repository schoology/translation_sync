<?php

class SmartlingAPI
{
    private $baseUrl = "https://api.smartling.com/v1/file";
 
    private $apiKey;
    private $projectId;
 
    public function __construct($apiKey, $projectId) {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
    }
 
    public function uploadFile($path, $fileType, $fileUri, $approved, $charset = 'UTF-8') {
        return $this->sendRequest('upload', array(
            'file' => '@' . $path . ';type=text/plain charset=' . $charset,
            'fileType' => $fileType,
            'fileUri' => $fileUri,
            'approved' => $approved
        ));
    }
 
    public function downloadFile($fileUri, $retrievalType, $locale, $fh) {
        $params = array(
            'fileUri' => $fileUri,
            'retrievalType' => $retrievalType,
            'locale' => $locale
        );
        return $this->sendRequest('get', $params, $fh);
    }
 
    public function getStatus($fileUri, $locale) {
        return $this->sendRequest('status', array(
            'fileUri' => $fileUri,
            'locale' => $locale
        ));
    }
 
    public function getList($locale, $params = array()) {
        return $this->sendRequest('list', array_merge_recursive(array(
            'locale' => $locale
        ), $params));
    }
 
    private function sendRequest($type, $params, $fh = NULL) {
        $handler = curl_init();
        curl_setopt_array($handler, array(
            CURLOPT_URL            => $this->baseUrl . "/" . $type,
            CURLOPT_PORT           => false,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS => array_merge_recursive(array(
                    'apiKey' => $this->apiKey,
                    'projectId' => $this->projectId
                ),
                $params
            )
        ));

        if(!is_null($fh)){
            curl_setopt($handler, CURLOPT_FILE, $fh);
        }
 
        $response = curl_exec($handler);
 
        if($response) {
            $result = json_decode($response, true);
            return $result ? $result : $response;
        } else {
            echo curl_error($handler);
            return false;
        }
    }
}