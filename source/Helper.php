<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Helper
 *
 * @author Marcin
 */
class Helper {
    //put your code here
    
    static function encryptStringArray($stringArray, $key = "xyz") { 
	$s = strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), serialize($stringArray), MCRYPT_MODE_CBC, md5(md5($key)))), '+/=', '-_,'); 
	return $s; 
    }
    
    static function decryptStringArray($stringArray, $key = "xyz"){ 
        $s = @unserialize(rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(strtr($stringArray, '-_,', '+/=')), MCRYPT_MODE_CBC, md5(md5($key))), "\0")); 
        return $s; 
    }
    
    static function curPageName(){
        $scriptName = filter_input(INPUT_SERVER, 'SCRIPT_NAME');
        return substr($scriptName, strrpos($scriptName, "/") + 1);
    }
    
    static function currentFolder(){
        $current_folder_array = explode('/',dirname(__FILE__)); 
        $current_folder_count = count($current_folder_array);
        return $current_folder_array[$current_folder_count - 2];
    }
    
    static function _log($message){
        $file = "0_log.txt";
        $handle = null;
        if(!file_exists($file)){
            $handle = fopen($file, 'w') or die('Cannot create file: ' . $file);
        }else{
            $handle = fopen($file, 'a') or die('Cannot open file:  ' . $file);
        }
        
        $message .= " ### " . $_SERVER['HTTP_USER_AGENT'] . ", IP: " . $_SERVER['REMOTE_ADDR'] . ", current page: " . $_SERVER['PHP_SELF'] . ", referal: ";// . $_SERVER['HTTP_REFERER'];
        fwrite($handle, date("Y-m-d H:i:s", time()) . ":" . $message . "\n");
        fclose($handle);
    }
    
	static function saveImageFile($client, $subfolder = null){
		$allowedExts = array("jpg", "jpeg", "gif", "png", "JPG", "JPEG", "GIF", "PNG");
		$tmp = explode(".", $_FILES["file_source"]["name"]);
		$extension = end($tmp);
		if ((($_FILES["file_source"]["type"] == "image/gif")
			|| ($_FILES["file_source"]["type"] == "image/jpeg")
			|| ($_FILES["file_source"]["type"] == "image/png")
			|| ($_FILES["file_source"]["type"] == "image/pjpeg")
			|| ($_FILES["file_source"]["type"] == "image/jpg")
			|| ($_FILES["file_source"]["type"] == "image/png"))
				&& ($_FILES["file_source"]["size"] < 10000000)
			&& in_array($extension, $allowedExts)){
				if ($_FILES["file_source"]["error"] > 0){
					return "Return Code: " . $_FILES["file_source"]["error"];
				}
				else{
					if($subfolder == null){
						move_uploaded_file($_FILES["file_source"]["tmp_name"],	"assets/" . $client . "/" . $client . "_" . $_FILES['file_source']['name']);
					}else{
						move_uploaded_file($_FILES["file_source"]["tmp_name"],	"assets/" . $client . "/" . $subfolder . "/" . $client . "_" . $_FILES['file_source']['name']);
					}
				return "Upload completed";
				}
		}
		else{
			return $extension;//"Invalid file";
		}
    }    
   
    
    static function _readFile($file){
            $handle = fopen($file, 'r') or die('Cannot open file:  ' . $file);
            $data = fread($handle,filesize($file));
            fclose($handle);
            return $data;
    }
    
    static function _saveFile($file, $data){
	$handle = fopen($file, 'w') or die('Cannot open file:  ' . $file);
	fwrite($handle, $data);
	fclose($handle);
    }

    static function _copyFile($from, $to){
	$handle = fopen($from, 'r') or die('Cannot open file:  ' . $from);
	$data = fread($handle, filesize($from));
	fclose($handle);

	$handle = fopen($to, 'w') or die('Cannot open file:  ' . $to);
	fwrite($handle, $data);
	fclose($handle);
    }   
	
	static function convertDate($date, $from = 'US', $to = 'mssql'){
		$new_string = null;
		$tmp = explode('/', $date);
		if($from == 'US' && $to == 'mssql'){
			$new_string = $tmp[2] . '-' . $tmp[0] . '-' . $tmp[1];
		}
		
		return $new_string;			
	} 
    
}
