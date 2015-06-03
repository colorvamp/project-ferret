<?php
	if($GLOBALS['w.localhost']){ini_set('display_errors',1);}
	date_default_timezone_set('Europe/Madrid');

	include_once('inc.common.php');
	include_once('inc.presentation.php');
	include_once('api.project.php');
	include_once('api.users.mongo.php');
	common_setBase('base');
	$GLOBALS['images']['sizes'] = ['48','128'];

	$userIsLogged = users_isLogged();
	$loginURL     = presentation_user_login();
	if( !$userIsLogged && $controller != 'u' ){
		common_r($loginURL);
		return false;
	}

	if( $userIsLogged ){
		$GLOBALS['user']['src.user.32'] = presentation_user_src($GLOBALS['user'],32);
		$GLOBALS['user']['src.user.48'] = presentation_user_src($GLOBALS['user'],48);
		$TEMPLATE['user'] = $GLOBALS['user'];
	}
