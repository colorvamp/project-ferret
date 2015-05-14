<?php
	function presentation_project_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/p/'.$projectOB['_id'];
	}
	function presentation_project_save_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/p/save/'.$projectOB['_id'];
	}
	function presentation_project_config_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/p/config/'.$projectOB['_id'];
	}


	function presentation_task_url($taskOB = []){
		return $GLOBALS['w.indexURL'].'/t/'.$taskOB['_id'];
	}
	function presentation_task_save_url($taskOB = []){
		return $GLOBALS['w.indexURL'].'/t/save/'.$taskOB['_id'];
	}

	function presentation_user_login(){
		return $GLOBALS['w.indexURL'].'/u/login';
	}
	function presentation_user_register($invitation = ''){
		return $GLOBALS['w.indexURL'].'/u/register'.($invitation ? '?invitation='.$invitation : '');
	}
