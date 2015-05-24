<?php
	if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
		case 'task.save':
			if( !isset($_POST['_id']) ){common_r();}
			include_once('api.users.mongo.php');
			include_once('inc.presentation.php');
			users_isLogged();
			include_once('inc.common.php');
			include_once('api.project.php');
			$mailAssign = false;

			$taskTB    = new taskTB();
			if( !($taskOB = $taskTB->getByID($_POST['_id'])) ){common_r();}
			$_POST['_id'] = $taskOB['_id'];
			if( isset($_POST['taskAssign']) ){
				$_POST['taskUser']['assigned'] = $_POST['taskAssign'];
				$mailAssign = true;
			}

			$r = $taskTB->save($_POST);
			if( isset($r['errorDescription']) ){print_r($r);exit;}
			$taskOB = $_POST;

			if( $mailAssign && ($userOB = users_getByID($_POST['taskUser']['assigned'])) ){
				include_once('api.mailing.php');
				/* INI-Envio de correo */
				$taskOB['url.task'] = presentation_task_url($taskOB);
				$config = json_decode(file_get_contents('../db/mail.json'),1);
				$blob   = common_loadSnippet('mail/es.mail.task.assign',[ 'taskOB'=>$taskOB ]);
				$subj   = 'Asignación de tareas';
				$r = mailing_send($config+[
					 'to'=>$userOB['userMail']
				],$subj,$blob);
				if( isset($r['errorDescription']) ){print_r($r);exit;}
				/* END-Envio de correo */
			}

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

		$userIDs = array_keys($projectOB['projectUsers']);
		if( isset($taskOB['taskUser']['created']) ){$userIDs[] = $taskOB['taskUser']['created'];}
		if( isset($taskOB['taskUser']['assigned']) ){$userIDs[] = $taskOB['taskUser']['assigned'];}

		$userIDs = array_unique($userIDs);
		$userOBs = users_getByIDs($userIDs);
		$TEMPLATE['userOBs'] = $userOBs;

		if( isset($taskOB['taskUser']['created'],$userOBs[strval($taskOB['taskUser']['created'])]) ){
			$taskOB['html.user.created'] = '<a href="">'.$userOBs[strval($taskOB['taskUser']['created'])]['userName'].'</a>'.PHP_EOL;
		}
		if( isset($taskOB['taskUser']['assigned'],$userOBs[strval($taskOB['taskUser']['assigned'])]) ){
			$taskOB['html.user.assigned'] = '<a href="">'.$userOBs[strval($taskOB['taskUser']['assigned'])]['userName'].'</a>'.PHP_EOL;
		}

		//FIXME: el paginador
		$shoutOBs = $shoutTB->getWhere(['shoutChannel'=>$taskOB['_id']]);
		$TEMPLATE['shoutOBs'] = $shoutOBs;

		$taskOB['url.task.edit'] = presentation_task_save_url($taskOB);
		$TEMPLATE['taskOB']  = $taskOB;
		$TEMPLATE['PAGE.H1'] = $projectOB['projectName'];
		$TEMPLATE['PAGE.DESCRIPTION'] = $projectOB['projectDescription'];
		$TEMPLATE['PAGE.TITLE'] = $taskOB['taskName'];
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

			$projectOB = $projectTB->getByID($taskOB['taskProjectID']);
			$TEMPLATE['PAGE.H1'] = $projectOB['projectName'];
			$TEMPLATE['PAGE.DESCRIPTION'] = $projectOB['projectDescription'];
			$TEMPLATE['PAGE.TITLE'] = $taskOB['taskName'];
		}

		common_loadScript('{%w.indexURL%}/r/js/coredown.js');
		return common_renderTemplate('t/save');
	}
