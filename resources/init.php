<?php
	if($GLOBALS['w.localhost']){ini_set('display_errors',1);}
	date_default_timezone_set('Europe/Madrid');

	include_once('inc.common.php');
	include_once('inc.presentation.php');
	include_once('api.project.php');
	include_once('api.users.mongo.php');
	common_setBase('base');

	$userIsLogged = users_isLogged();
	if( !$userIsLogged && ( $command != 'u_login' && $command != 'u_register' ) ){
		common_r($GLOBALS['w.indexURL'].'/u/login');
		return false;
	}

	if( $userIsLogged ){
		$TEMPLATE['user'] = $GLOBALS['user'];
	}
