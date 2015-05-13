<?php
	function presentation_project_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/p/'.$projectOB['_id'];
	}


	function presentation_task_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/t/'.$projectOB['_id'];
	}
	function presentation_task_save_url($projectOB = []){
		return $GLOBALS['w.indexURL'].'/t/save/'.$projectOB['_id'];
	}
