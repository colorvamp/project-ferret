<?php
	if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
		case 'task.save':
			if( !isset($_POST['_id']) ){common_r();}
			include_once('inc.common.php');
			include_once('api.project.php');
			$taskTB = new taskTB();
			if( !($taskOB = $taskTB->getByID($_POST['_id'])) ){common_r();}
			$_POST['_id'] = $taskOB['_id'];
			$taskOB = $taskTB->save($_POST);
			if( isset($taskOB['errorDescription']) ){print_r($taskOB);exit;}
			common_r();
	}}

	function t_main($id = false){
		if( $id ){return t_profile($id);}
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();

		$projectOBs = $projectTB->getWhere();
		$TEMPLATE['projectOBs'] = $projectOBs;
		return common_renderTemplate('p/main');
	}

	function t_profile($id = false){
		include_once('api.shoutbox.mongo.php');
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();
		$taskTB    = new taskTB();
		$shoutTB   = new shoutTB();
		if( !($taskOB = $taskTB->getByID($id)) ){common_r('',404);}
		$projectOB = $projectTB->getByID($taskOB['taskProjectID']);

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'shout.save':
				$_POST['shoutChannel'] = $taskOB['_id'];
				$shoutOB = $shoutTB->save($_POST);
				if( isset($shoutOB['errorDescription']) ){print_r($shoutOB);exit;}
				//FIXME: enviar correos
				common_r();
		}}

		$userOBs = users_getWhere(['_id'=>['$in'=>[
			 $taskOB['taskUser']['created']
		]]]);

		if( isset($userOBs[strval($taskOB['taskUser']['created'])]) ){
			$taskOB['html.user'] = '<a href="">'.$userOBs[strval($taskOB['taskUser']['created'])]['userName'].'</a>'.PHP_EOL;
		}

		//FIXME: el paginador
		$shoutOBs = $shoutTB->getWhere(['shoutChannel'=>$taskOB['_id']]);
		$TEMPLATE['shoutOBs'] = $shoutOBs;

		$taskOB['url.task.edit'] = presentation_task_save_url($taskOB);
		$TEMPLATE['taskOB']  = $taskOB;
		$TEMPLATE['PAGE.H1'] = $projectOB['projectName'];
		$TEMPLATE['PAGE.DESCRIPTION'] = $projectOB['projectDescription'];
		return common_renderTemplate('t/profile');
	}

	function t_save($id = false){
		$TEMPLATE  = &$GLOBALS['TEMPLATE'];
		$projectTB = new projectTB();
		$taskTB    = new taskTB();
		if( $id && !($taskOB = $taskTB->getByID($id)) ){common_r('',404);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'project.save':
				$projectOB = $projectTB->save($_POST);
				if( isset($projectOB['errorDescription']) ){print_r($projectOB);exit;}
				/* Vamos a la página del hotel */
				//$url = presentation_assis_hotel_url($r);
				//common_r($url);
				common_r();
		}}

		if( isset($taskOB) ){
			if( isset($taskOB['taskTags']) && $taskOB['taskTags'] ){$taskOB['html.tags'] = implode(',',$taskOB['taskTags']);}
			$TEMPLATE['taskOB'] = $taskOB;
		}
		return common_renderTemplate('t/save');
	}
