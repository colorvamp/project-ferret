<?php
	function u_main($userName = ''){
		if($userName){return u_profile($userName);}
	}

	function u_profile($userName = ''){
		if( !users_isLogged() ){common_r('',404);}
		//FIXME: cada uno solo debe poder entrar en su perfil realmente
		$TEMPLATE = &$GLOBALS['TEMPLATE'];
		include_once('api.profiles.php');
		$profileTB = new profileTB();

		/* Vamos a detectar si este usuario tiene algún perfil configurado */
		$profilesCount = $profileTB->count(['profileUsers.'.strval($GLOBALS['user']['_id'])=>['$exists'=>true]]);
		$TEMPLATE['html.profile.create'] = '';
		if( !$profilesCount ){
			$TEMPLATE['html.profile.create'] = common_loadSnippet('profile/snippets/profile.create');
		}

		$TEMPLATE['PAGE.TITLE'] = 'Perfil de usuario';
		common_renderTemplate('u/profile');
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
				include_once('api.mail.php');
				//$userOB = users_getByID('548ee8643dfa6cf1258b4567');

				if(!isset($_POST['userPass']) || !isset($_POST['userPassR']) || $_POST['userPass'] != $_POST['userPassR']){echo 'passwords mismatch';exit;}
				$userOB = users_save($_POST);if(isset($userOB['errorDescription'])){switch($userOB['errorDescription']){
					case 'EMAIL_DUPLICATED':common_r('?duplicated=1');
					default:print_r($r);exit;
				}}

				/*$r = mail_send('info@ferret.com',[
					 'to'=>$userOB['userMail']
					,'subject'=>'Confirmación de usuario'
					,'body'=>common_loadSnippet('u/mail/confirm',[ 'userOB'=>$userOB,'w.indexURL'=>$GLOBALS['w.indexURL'] ])
					,'files'=>[
						//El logo
					]
				]);*/

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
			if($userOB['userCode'] != $userCode){break;}
			$userOB['userStatus'] = 1;
			$userOB['userCode']   = users_generateCode($userMail);
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
