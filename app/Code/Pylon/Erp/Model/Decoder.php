<?php
namespace Pylon\Erp\Model;
class Decoder {
    
    function __construct() {
        
    }
    public function decode(){
        
        
        //$message = array();
        if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
            throw new Exception('Request method must be POST!');
        }
        
        //Make sure that the content type of the POST request has been set to application/json
        $contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
        if(strcasecmp($contentType, 'application/json') != 0){
            throw new Exception('Content type must be: application/json');
        }
        
        $content = trim(file_get_contents("php://input"));
        echo "<pre>";
        print_r($content);
        $decoded = json_decode($content, true);
        //If json_decode failed, the JSON is invalid.
        if(!is_array($decoded)){
            throw new Exception('Received content contained invalid JSON!');
        }
        
        
        return $decoded;
       
    }
}