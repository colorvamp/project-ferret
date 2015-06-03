<?php
	function presentation_project_url($projectOB = [],$mode = ''){
		return $GLOBALS['w.indexURL'].'/p/'.$projectOB['_id'].($mode ? '/'.$mode : '');
	}
	function presentation_project_save_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/p/save/'.$projectOB['_id'];
	}
	function presentation_project_config_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/p/config/'.$projectOB['_id'];
	}

	/* INI-Images */
	function presentation_image_url($imageOB = []){
		return $GLOBALS['w.indexURL'].'/i/'.$imageOB['_id'];
	}
	function presentation_image_src($imageOB = [],$size = false){
		return $GLOBALS['w.indexURL'].'/i/src/'.$imageOB['_id'].'.jpeg'.($size ? '?s='.$size : '');
	}
	/* END-Images */

	function presentation_task_url($taskOB = []){
		return $GLOBALS['w.indexURL'].'/t/'.$taskOB['_id'];
	}
	function presentation_task_save_url($taskOB = []){
		return $GLOBALS['w.indexURL'].'/t/save/'.$taskOB['_id'];
	}


	function presentation_user_url($u = false){
		return $GLOBALS['w.indexURL'].'/u/profile/'.$u['_id'];
	}
	function presentation_user_src($u = false,$size = false){
		return $GLOBALS['w.indexURL'].'/u/avatar/'.$u['_id'].($size ? '/'.$size : '');
	}
	function presentation_user_login(){
		return $GLOBALS['w.indexURL'].'/u/login';
	}
	function presentation_user_register($invitation = ''){
		return $GLOBALS['w.indexURL'].'/u/register'.($invitation ? '?invitation='.$invitation : '');
	}
