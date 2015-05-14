<?php
	function p_main($id = false){
		if( $id ){return p_profile($id);}
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();

		$projectOBs = $projectTB->getWhere();
		foreach( $projectOBs as &$projectOB ){
			$projectOB['url.project'] = presentation_project_url($projectOB);
		}

		$TEMPLATE['projectOBs'] = $projectOBs;
		return common_renderTemplate('p/main');
	}

	function p_profile($id = false){
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();
		$taskTB    = new taskTB();
		if( !($projectOB = $projectTB->getByID($id)) ){common_r('',404);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'task.save':
				$_POST['taskProjectID'] = $projectOB['_id'];
				$taskOB = $taskTB->save($_POST);
				if( isset($taskOB['errorDescription']) ){print_r($taskOB);exit;}
				common_r();
		}}

		$taskOBs = $taskTB->getWhere(['taskProjectID'=>$projectOB['_id'],'taskStatus'=>'open']);
		foreach( $taskOBs as &$taskOB ){
			$taskOB['url.task'] = presentation_task_url($taskOB);
		}

		$projectOB['url.project.save']   = presentation_project_save_url($projectOB);
		$projectOB['url.project.config'] = presentation_project_config_url($projectOB);

		$TEMPLATE['taskOBs']    = $taskOBs;
		$TEMPLATE['projectOB']  = $projectOB;
		$TEMPLATE['PAGE.H1']    = $projectOB['projectName'];
		$TEMPLATE['PAGE.DESCRIPTION'] = $projectOB['projectDescription'];
		$TEMPLATE['PAGE.TITLE'] = 'Proyecto «'.$projectOB['projectName'].'»';
		return common_renderTemplate('p/profile');
	}

	function p_save($id = false){
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();
		if( $id && !($projectOB = $projectTB->getByID($id)) ){common_r('',404);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'project.save':
				if( isset($_POST['_id']) && !$_POST['_id'] ){unset($_POST['_id']);}
				$projectOB = $projectTB->save($_POST);
				if( isset($projectOB['errorDescription']) ){print_r($projectOB);exit;}
				/* Vamos a la página del hotel */
				//$url = presentation_assis_hotel_url($r);
				//common_r($url);
				common_r();
		}}

		if( isset($projectOB) ){
			$TEMPLATE['projectOB'] = $projectOB;
		}
		return common_renderTemplate('p/save');
	}

	function p_config($id = false){
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();
		$taskTB    = new taskTB();
		if( !($projectOB = $projectTB->getByID($id)) ){common_r('',404);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'user.invite':
				if( !($userOB = users_getByMail($_POST['userMail'])) ){common_r();}
				$projectOB['projectUsers'][strval($userOB['_id'])] = 'r';
				$projectOB = $projectTB->save($projectOB);
				if( isset($projectOB['errorDescription']) ){print_r($projectOB);exit;}
				common_r();
		}}

		if( isset($projectOB['projectUsers']) && $projectOB['projectUsers'] ){
			$userIDs = array_keys($projectOB['projectUsers']);
			$userOBs = users_getByIDs($userIDs);
			$TEMPLATE['userOBs'] = $userOBs;
		}

		$TEMPLATE['projectOB']  = $projectOB;
		$TEMPLATE['PAGE.TITLE'] = 'Configuración de «'.$projectOB['projectName'].'»';
		return common_renderTemplate('p/config');
	}
