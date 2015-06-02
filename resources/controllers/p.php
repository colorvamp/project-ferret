<?php
	function p_main($id = false){
		if( $id ){return call_user_func_array('p_profile',func_get_args());}
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();
		$userID    = strval($GLOBALS['user']['_id']);

		$projectOBs = $projectTB->getWhere(['projectUsers.'.$userID=>['$exists'=>true]]);
		foreach( $projectOBs as &$projectOB ){
			$projectOB['url.project'] = presentation_project_url($projectOB);
		}

		$TEMPLATE['projectOBs'] = $projectOBs;
		$TEMPLATE['PAGE.TITLE'] = 'Listado de proyectos';
		return common_renderTemplate('p/main');
	}

	function p_profile($id = false,$mode = ''){
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

		$query = ['taskProjectID'=>$projectOB['_id'],'taskStatus'=>'open'];
		if( $mode == 'closed' ){$query = ['taskProjectID'=>$projectOB['_id'],'taskStatus'=>'closed'];}

		$taskOBs = $taskTB->getWhere($query,['order'=>['taskStamp'=>-1]]);
		$userIDs = array_map(function($n){
			return isset($n['taskUser']['assigned']) ? $n['taskUser']['assigned'] : '';
		},$taskOBs);
		$userOBs = users_getByIDs($userIDs);

		foreach( $taskOBs as &$taskOB ){
			if( isset($taskOB['taskUser']['assigned'],$userOBs[strval($taskOB['taskUser']['assigned'])]) ){
				$userOB = $userOBs[strval($taskOB['taskUser']['assigned'])];
				$taskOB['src.task.48'] = presentation_user_src($userOB,48);
			}
			$taskOB['url.task'] = presentation_task_url($taskOB);
		}

		$TEMPLATE['tasks.active.count']   = $taskTB->count(['taskProjectID'=>$projectOB['_id'],'taskStatus'=>'open']);
		$TEMPLATE['tasks.assigned.count'] = $taskTB->count(['taskProjectID'=>$projectOB['_id'],'taskStatus'=>'open','taskUser.assigned'=>$GLOBALS['user']['_id']]);
		$TEMPLATE['tasks.closed.count']   = $taskTB->count(['taskProjectID'=>$projectOB['_id'],'taskStatus'=>'closed']);

		$projectOB['url.project.save']   = presentation_project_save_url($projectOB);
		$projectOB['url.project.config'] = presentation_project_config_url($projectOB);

		$TEMPLATE['url.tasks.active'] = presentation_project_url($projectOB);
		$TEMPLATE['url.tasks.closed'] = presentation_project_url($projectOB,'closed');
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
