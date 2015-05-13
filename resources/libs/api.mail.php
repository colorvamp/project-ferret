<?php
	if(!isset($GLOBALS['api']['mail'])){$GLOBALS['api']['mail'] = [];}
	$GLOBALS['api']['mail'] = array_merge([
		 'path.creds'=>'../db/mail/'
		,'creds'=>[]
	],$GLOBALS['api']['mail']);

	function mail_compose($params = []){
		$CR = "\r\n";
		$boundary = md5(time());

		if( isset($params['files']) ){
			$cids  = [];
			$files = '';
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			foreach($params['files'] as $file){
				$isArray = is_array($file);
				if( !$isArray && !file_exists($file) ){continue;}

				$name    = $isArray ? $file['name'] : basename($file);
				$mime    = $isArray ? $file['mime'] : finfo_file($finfo,$file);
				$uniq = uniqid();

				$files .= '--'.$boundary.$CR;
				$files .= 'Content-Type: '.$mime.'; name="'.$name.'"'.$CR;
				$files .= 'Content-ID: <'.$uniq.'>'.$CR;
				$files .= 'Content-Transfer-Encoding: base64'.$CR;
				$files .= 'Content-Disposition: inline; filename="'.$name.'"'.$CR.$CR;
				if( !$isArray ){$files .= base64_encode(file_get_contents($file)).$CR.$CR;}
				if( $isArray && isset($file['blob']) ){$files .= base64_encode($file['blob']).$CR.$CR;}
				$cids[] = $uniq;
			}
			finfo_close($finfo);

			if( isset($params['body']) ){foreach($cids as $k=>$cid){$params['body'] = str_replace('{%image.'.$k.'%}','cid:'.$cid,$params['body']);}}
		}

		if( !isset($params['body']) ){$params['body'] = '';}
		$raw =
			 'MIME-Version: 1.0'.$CR
			.( isset($params['subject']) ? 'Subject: '.utf8_decode($params['subject']).$CR : '' )
			.( isset($params['to']) ? 'To: '.$params['to'].$CR : '')
			.( isset($params['bcc']) ? 'Bcc: '.$params['bcc'].$CR : '')
			.( isset($params['from']) ? 'From: '.$params['from'].$CR : '' )
			.'Content-Type: multipart/related; boundary='.$boundary.$CR.$CR
			.'--'.$boundary.$CR
			.'Content-Type: text/html; charset="UTF-8"'.$CR.$CR
			.$params['body'].$CR.$CR
			.( isset($params['files']) && $files ? $files.$CR : '' );
		//echo $raw;exit;
		return $raw;
	}

	function mail_send($mail = '',$params = []){
		if( !function_exists('html_query') ){include_once('inc.html.curl.php');}
		$file = $GLOBALS['api']['mail']['path.creds'].$mail.'.json';
		if( !isset($GLOBALS['api']['mail']['creds'][$mail]) ){
			if( !file_exists($file) ){return ['errorDescription'=>'DATA_ERROR','file'=>__FILE__,'line'=>__LINE__];}
			$GLOBALS['api']['mail']['creds'][$mail] = json_decode(file_get_contents($file),1);
		}
		$token = $GLOBALS['api']['mail']['creds'][$mail]['access_token'];
		if( !isset($params['from']) ){$params['from'] = $mail;}

		$raw = mail_compose($params);
		$url = 'https://www.googleapis.com/upload/gmail/v1/users/me/messages/send';
		$header = [
			 'Content-Type'=>'message/rfc822'
			,'Authorization'=>'OAuth '.$token
		];
		$data = ['headers'=>$header,'post'=>$raw];
		$data = html_query($url,$data);
		if( isset($data['errorDescription']) ){return $data;}
		//if( $data['pageCode'] != 200 ){print_r($data);exit;}
		$resp = json_decode($data['pageContent'],1);
		if( isset($resp['error']['code']) && $resp['error']['code'] == 401 ){
			$r = mail_token_refresh($mail);
			if( isset($r['errorDescription']) ){print_r($r);exit;}
			return call_user_func_array(__FUNCTION__,func_get_args());
		}
		return $resp;
	}
	function mail_token_refresh($mail = '',$params = []){
		$file = $GLOBALS['api']['mail']['path.creds'].$mail.'.json';
		if( !isset($GLOBALS['api']['mail']['creds'][$mail]) ){
			if( !file_exists($file) ){return ['errorDescription'=>'DATA_ERROR','file'=>__FILE__,'line'=>__LINE__];}
			$GLOBALS['api']['mail']['creds'][$mail] = json_decode(file_get_contents($file),1);
		}
		$creds = $GLOBALS['api']['mail']['creds'][$mail];

		include_once('inc.html.curl.php');
		/* Para refrescar el token */
		$url = 'https://accounts.google.com/o/oauth2/token';
		$post = [
//FIXME: esto seguramente no debería estar aqui
			 'client_id'=>'1013968149312-5n1da5e5c6f1jsdjib4bgdhll5910490.apps.googleusercontent.com'
			,'client_secret'=>'pyL4qV1YZSQYsBN72aJCnLww'
			,'refresh_token'=>$creds['refresh_token']
			,'grant_type'=>'refresh_token'
		];
		$data = html_query($url,['post'=>$post]);
		if( isset($data['errorDescription']) ){return $data;}
		if( $data['pageCode'] != 200 ){return ['errorDescription'=>$data['pageCode'],'file'=>__FILE__,'line'=>__LINE__];}

		$json = json_decode($data['pageContent'],1);
		if( !isset($json['access_token']) ){
			echo 'invalid:';
			print_r($data);exit;
		}
		$creds['access_token'] = $json['access_token'];

		file_put_contents($file,json_encode($creds));
		$GLOBALS['api']['mail']['creds'][$mail] = $creds;
		return true;
	}
