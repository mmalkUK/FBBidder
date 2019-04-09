<?php

/*
 * THIS IS MASTER CLASS WHICH WILL CONTROLL WHAT TO DISPLAY
 */

/**
 * Description of MView
 *
 * @author Marcin
 */
class MView {
	//Users
	
	static function tableDT($cmd){
		$data = array();
		
		if($cmd == 'category'){
			$data['Id'] = 'ID';
			$data['name'] = 'Name';
			$data['sortOrder'] = 'Sort Order';
		}

		if($cmd == 'banners'){
			$data['Id'] = 'ID';
			$data['alt'] = 'Alt';
			$data['link'] = 'Link';
			$data['path'] = 'Path';
			$data['sortOrder'] = 'Sort Order';
		}
		
		if($cmd == 'active'){
			$data['Id'] = 'ID';	
		}
		
		return $data;
	}
	
	static function tableAction($cmd){
		$actions = array();
		
		if($cmd == 'category'){
			$actions['Edit'] = '?cmd=edit&Id=';
			$actions['Delete'] = '?cmd=delete&Id=';
		}

		if($cmd == 'banners'){
			$actions['Preview'] = 3;
			$actions['Edit'] = '?cmd=edit&Id=';
			$actions['Delete'] = '?cmd=delete&Id=';
		}

		
		return $actions;
	}
	
	static function getTable($cmd){
		if($cmd == 'category'){
			return 'category';
		}
		if($cmd == 'banners'){
			return 'banners';
		}		
	}
	
	static function edit($cmd){
		$data = array();
		
		if($cmd == 'users'){
			$data['user_name'][0] = 'User Name'; //display name
			$data['user_name'][1] = 'text';      //type of input field
			$data['user_name'][2] = true;        //needed
			$data['user_name'][3] = 'none';    //check if field
			
			$data['full_name'][0] = 'Full Name';
			$data['full_name'][1] = 'text';
			$data['full_name'][2] = true;
			$data['full_name'][3] = 'none';
			
			$data['position'][0] = 'Position';
			$data['position'][1] = 'text';
			$data['position'][2] = true;
			$data['position'][3] = 'none';
			
		}
		return $data;    
		
	}
	
	static function create($cmd){
		$data = array();
		
		if($cmd == 'users'){
			$data['user_name'][0] = 'User Name'; //display name
			$data['user_name'][1] = 'text';      //type of input field
			$data['user_name'][2] = true;        //needed
			$data['user_name'][3] = 'exist';     //check if field
			
			$data['full_name'][0] = 'Full Name';
			$data['full_name'][1] = 'text';
			$data['full_name'][2] = true;
			$data['full_name'][3] = 'none';
			
			$data['position'][0] = 'Position';
			$data['position'][1] = 'text';
			$data['position'][2] = true;
			$data['position'][3] = 'none';
			
			$data['email'][0] = 'Email';
			$data['email'][1] = 'email';
			$data['email'][2] = false;
			$data['email'][3] = 'none';
			
			$data['password'][0] = 'Password';
			$data['password'][1] = 'password';
			$data['password'][2] = true;
			$data['password'][3] = 'match';
			
			$data['created_by'][0] = 'Created by';
			$data['created_by'][1] = 'hidden';
			$data['created_by'][2] = true;
			$data['created_by'][3] = 'created_by';
			
		}
		
		
		
		return $data;
	}
}

