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

		$taskOBs = $taskTB->getWhere(['taskProjectID'=>$projectOB['_id']]);
		foreach( $taskOBs as &$taskOB ){
			$taskOB['url.task'] = presentation_task_url($taskOB);
		}

		$TEMPLATE['taskOBs']   = $taskOBs;
		$TEMPLATE['projectOB'] = $projectOB;
		$TEMPLATE['PAGE.H1'] = $projectOB['projectName'];
		$TEMPLATE['PAGE.DESCRIPTION'] = $projectOB['projectDescription'];
		return common_renderTemplate('p/profile');
	}

	function p_save(){
		$projectTB = new projectTB();

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'project.save':
				$projectOB = $projectTB->save($_POST);
				if( isset($projectOB['errorDescription']) ){print_r($projectOB);exit;}
				/* Vamos a la página del hotel */
				//$url = presentation_assis_hotel_url($r);
				//common_r($url);
				common_r();
		}}

		return common_renderTemplate('p/save');
	}
