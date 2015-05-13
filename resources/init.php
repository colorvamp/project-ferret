<?php
	if($GLOBALS['w.localhost']){ini_set('display_errors',1);}
	date_default_timezone_set('Europe/Madrid');

	include_once('inc.common.php');
	include_once('inc.presentation.php');
	include_once('api.project.php');
	include_once('api.users.mongo.php');
	common_setBase('base');

	$userIsLogged = users_isLogged();
	$loginURL     = presentation_user_login();
	$registerURL  = presentation_user_register(isset($_GET['invitation']) ? $_GET['invitation'] : false);
	if( !$userIsLogged && ($GLOBALS['w.currentURL'] != $loginURL && $GLOBALS['w.currentURL'] != $registerURL ) ){
		common_r($loginURL);
		return false;
	}

	if( $userIsLogged ){
		$TEMPLATE['user'] = $GLOBALS['user'];
	}
