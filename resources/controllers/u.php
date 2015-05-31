<?php
	function u_main($userName = ''){
		if($userName){return u_profile($userName);}
	}

	function u_profile($userName = ''){
		if( !users_isLogged() ){common_r('',404);}
		$TEMPLATE = &$GLOBALS['TEMPLATE'];


		$TEMPLATE['PAGE.TITLE'] = 'Perfil de usuario';
		common_renderTemplate('u/profile');
	}

	function u_me(){
		if( !users_isLogged() ){common_r('',404);}
		$TEMPLATE = &$GLOBALS['TEMPLATE'];

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'mail.change':
				if( !isset($_POST['userMail']) ){common_r();}
				/* Si el usuario por el que queremos cambiar ya está registrado
				 * en el sistema no podemos cambiarlo, mejor que recupere la 
				 * contraseña desde ese otro usuario */
				if( $userOB = users_getByMail($_POST['userMail']) ){common_r();}
				$r = users_save(['userMail'=>$_POST['userMail']]+$GLOBALS['user']);
				//FIXME: reenviar correo de confirmación
				common_r();
			case 'user.avatar':
				if( !$_FILES ){common_r();}
				$file = array_shift($_FILES);
				if( $file['error'] ){common_r();}
				$r = users_avatar_save($GLOBALS['user']['_id'],$file['tmp_name']);
				if( isset($r['errorDescription']) ){
					print_r($r);
					exit;
				}
				common_r();
exit;
		}}


		$TEMPLATE['PAGE.TITLE'] = 'Perfil de usuario';
		common_renderTemplate('u/profile.me');
	}

	function u_avatar($id = false,$size = false){
		if($id && ($userOB = users_getByID($id)) && ($imagePath = users_avatar_get($id,$size)) ){
			$r = stat($imagePath);
			$m = date('D, d M Y H:m:s \G\M\T',$r['mtime']);
			header('Last-Modified: '.$m);
			header('Cache-Control: max-age=31557600');
			header_remove('Pragma');
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])){$d = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);$k = strtotime($m);if($d == $k){header('HTTP/1.1 304 Not Modified');exit;}}

			header('Content-Type: image/jpeg');
			readfile($imagePath);exit;
		}

		include_once('inc.graph.php');
		$gradient = graph_gradient('8cc277','6fa85b',6);

		$svg = '<?xml version="1.0" encoding="UTF-8"?>'.PHP_EOL;
		$svg .= '<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">'.PHP_EOL;
		$svg .= '<svg width="48" height="48" version="1.1" xmlns="http://www.w3.org/2000/svg">'.PHP_EOL;
		for($i = 0; $i < 48; $i+=6){
			for($j = 0; $j < 48; $j+=6){
				$index = array_rand($gradient); /* Get a random index */
				$color = $gradient[$index]; /* Grab a color */
				$svg .= '<rect x="'.$i.'" y="'.$j.'" width="6" height="6" style="fill:#'.$color.';" />'.PHP_EOL;
			}
		}
		$svg .= '</svg>'.PHP_EOL;

		header('Content-type: image/svg+xml');
		echo $svg;
		exit;
	}

	function u_login(){
		if( users_isLogged() ){
			/* Si hay usuario logueado redireccionamos a su perfil */
			common_r($GLOBALS['w.indexURL'].'/profile');
		}

		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		if(count($_POST)){do{
			$r = users_login($_POST['userMail'],$_POST['userPass']);
			if( isset($r['errorDescription']) ){$TEMPLATE['loginWarn'] = $r['errorDescription'];break;}
			if( isset($_GET['continue']) ){common_r(urldecode($_GET['continue']));exit;}
			common_r($GLOBALS['w.indexURL'].'/profile');
		}while(false);}

		$TEMPLATE['BLOG.TITLE'] = 'Login de usuarios';
		$TEMPLATE['HTML.TITLE'] = $TEMPLATE['BLOG.TITLE'];
		$TEMPLATE['HTML.DESCRIPTION'] = 'Login de usuarios';
		common_setBase('base.public');
		return common_renderTemplate('u/login');
	}

	function u_logout(){
		users_logout();
		common_r($GLOBALS['w.indexURL']);
	}

	function u_register($invitation = ''){
		if( isset($_GET['invitation']) ){$invitation = $_GET['invitation'];}
		if( !$invitation ){common_r('',404);}
		$file = '../db/invitations/'.preg_replace('/[^a-z0-9]/','',$invitation);
		if( !file_exists($file) ){common_r('',404);}
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		common_setBase('base.public');

		if( isset($_GET['confirm']) ){
			$TEMPLATE['PAGE.TITLE'] = 'Correo de confirmación enviado';
			$TEMPLATE['HTML.DESCRIPTION'] = 'Correo de confirmación enviado';
			return common_renderTemplate('u/register.confirm');
		}
		if( isset($_GET['duplicated']) ){
//FIXME: hacer esta plantilla
			$TEMPLATE['PAGE.TITLE'] = 'Correo registrado con anterioridad';
			$TEMPLATE['HTML.DESCRIPTION'] = 'Registro de usuarios';
			return common_renderTemplate('u/register.duplicated');
		}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'user.register':
				include_once('api.mailing.php');

				if(!isset($_POST['userPass']) || !isset($_POST['userPassR']) || $_POST['userPass'] != $_POST['userPassR']){echo 'passwords mismatch';exit;}
				$userOB = users_save($_POST);if(isset($userOB['errorDescription'])){switch($userOB['errorDescription']){
					case 'EMAIL_DUPLICATED':common_r('?duplicated=1');
					default:print_r($r);exit;
				}}

				/* INI-Envio de correo */
				$rep  = [
					'confirm.url'=>$GLOBALS['w.indexURL'].'/u/confirm/'.$userOB['userMail'].'/'.key($userOB['userCode'])
				];
				$config = json_decode(file_get_contents('../db/mail.json'),1);
				$blob   = common_loadSnippet('mail/es.mail.confirm',$rep);
				$subj   = 'Confirmación de usuario';
				$r = mailing_send($config+[
					 'to'=>$userOB['userMail']
				],$subj,$blob);
				if( isset($r['errorDescription']) ){print_r($r);exit;}
				/* END-Envio de correo */

				unlink($file);
				common_r('?confirm=1');
		}}

		$TEMPLATE['PAGE.TITLE'] = 'Registro de usuario';
		$TEMPLATE['HTML.DESCRIPTION'] = 'Registro de usuario';
		return common_renderTemplate('u/register');
	}

	function u_confirm($userMail = false,$userCode = false){
		if($userMail && $userCode){do{
			$userMail = preg_replace($GLOBALS['api']['users']['reg.mail.clear'],'',$userMail);
			if( !($userOB = users_getByMail($userMail)) ){break;}
			if( !isset($userOB['userCode'][$userCode]) ){break;}
			$userOB['userStatus'] = 1;
			$userOB['userCode']   = [users_generateCode($userMail)=>time()];
			$r = users_save($userOB);
			if( isset($r['errorDescription']) ){print_r($r);exit;}
			users_impersonate($userOB);
			common_r($GLOBALS['w.indexURL'].'/profile');
		}while(false);}
		//FIXME:
		exit;
	}

	function u_remember($userMail = false,$userCode = false){
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		if( $userMail && strpos($userMail,'%40') ){$userMail = urldecode($userMail);}

		if( $userMail && $userCode ){do{
			$userMail = preg_replace($GLOBALS['api']['users']['reg.mail.clear'],'',$userMail);
			if( !($userOB = users_getByMail($userMail)) ){common_r();}
			if( !isset($userOB['userCode']['remember']) || $userOB['userCode']['remember'] != $userCode ){common_r();}

			if( count($_POST) && isset($_POST['userPass'],$_POST['userPassR']) && strlen($_POST['userPass']) ){
				if( $_POST['userPass'] != $_POST['userPassR'] ){echo 'Las contraseñas no coinciden';exit;}
				$userOB['userSalt'] = null;
				$userOB['userCode'] = null;
				$userOB['userPass'] = $_POST['userPass'];
				$userOB = users_save($userOB);
				common_r($GLOBALS['w.indexURL'].'/u/login');
			}

			$TEMPLATE['PAGE.TITLE'] = 'Establecer nueva contraseña';
			common_setBase('base.public');
			return common_renderTemplate('u/remember.new.password');
		}while(false);}

		if(isset($_POST['subcommand'])){switch($_POST['subcommand']){
			case 'user.remember':
				if( !isset($_POST['userMail']) ){common_r();}
				$userMail = preg_replace($GLOBALS['api']['users']['reg.mail.clear'],'',$_POST['userMail']);
				if( !($userOB = users_getByMail($userMail)) ){common_r();}

				$newCode = users_generateCode($userOB['userMail']);
				$userOB['userCode']['remember'] = $newCode;
				$userOB  = users_save($userOB);

				include_once('api.mail.php');

				/*$r = mail_send('info@ferret.com',[
					 'to'=>$userOB['userMail']
					,'subject'=>'Recordar contraseña'
					,'body'=>common_loadSnippet('u/mail/remember',[ 'userOB'=>$userOB,'w.indexURL'=>$GLOBALS['w.indexURL'] ])
					,'files'=>[
						//El logo
					]
				]);*/

				common_r('?sent=1');
		}}

		$TEMPLATE['PAGE.TITLE'] = 'Recordar contraseña';
		common_setBase('base.public');
		return common_renderTemplate('u/remember');
	}
