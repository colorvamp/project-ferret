<?php
	if( !isset($GLOBALS['api']['users']) ){$GLOBALS['api']['users'] = [];}
	if( !class_exists('_mongo') ){include_once('classes/class._mongo.php');}

	$GLOBALS['api']['users'] = array_merge([
		'db.name'=>'projectferret',
		'table.users'=>'users',
		'reg.mail.clear'=>'/[^a-z0-9\._\+\-\@]*/',
		'reg.mail.match'=>'/^[a-z0-9\._\+\-]+@[a-z0-9\.\-]+\.[a-z]{2,6}$/',
		'dir.users'=>'../db/api.users/'
	],$GLOBALS['api']['users']);

	/* INI-mongo tables */
	$GLOBALS['api']['mongo']['tables']['users'] = [
		 '_id'=>'INTEGER AUTOINCREMENT'
		,'userMail'=>'TEXT'
		,'userPass'=>'TEXT'
		,'userSalt'=>'TEXT'
		,'userName'=>'TEXT'
		,'userAddr'=>'TEXT'
		,'userCode'=>'TEXT'
		,'userNick'=>'TEXT'
		,'userDate'=>'TEXT'
		,'userStatus'=>'TEXT'
		,'userModes'=>'TEXT'
		,'userLastLogin'=>'TEXT'
	];
	/* END-mongo tables */
	/* INI-mongo indexes */
	$GLOBALS['api']['mongo']['indexes']['users'] = [
		 ['fields'=>['userMail'=>1],'props'=>['unique'=>true]]
		,['fields'=>['userNick'=>1],'props'=>['unique'=>true]]
	];
	/* END-mongo indexes */


	class usersTB extends _mongo{
		public $table = 'users';
		public $search_fields = ['userName','userNick','userMail'];
		public function validate(&$data = [],&$oldData = []){
			
			return $data;
		}
	}
	

	function users_set_database($dbname = ''){
		$GLOBALS['api']['users']['db.name'] = $dbname;
	}
	function users_save($data = [],$params = []){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}

		$userOB = mongo_collection_save(
			$GLOBALS['api']['users']['db.name'],
			$GLOBALS['api']['users']['table.users'],
			$data,
			function($data){
				//FIXME: para evitar el contraint error
				if( !isset($data['userMail']) || !preg_match($GLOBALS['api']['users']['reg.mail.match'],$data['userMail']) ){return ['errorDescription'=>'EMAIL_ERROR','file'=>__FILE__,'line'=>__LINE__];}
				if( !isset($data['userStatus']) ){$data['userStatus'] = 0;}
				if( !isset($data['userNick']) ){$data['userNick'] = strval($data['_id']);}
				if( !isset($data['userCode']) ){$data['userCode'] = [users_generateCode($data['userMail'])=>time()];}
				if( !isset($data['userDate']) ){$data['userDate'] = ['stamp'=>time(),'date'=>date('Y-m-d'),'time'=>date('H:i:s')];}

				if( isset($data['userPass']) && !isset($data['userSalt']) ){
					if( $data['userPass'] == '12345678' ){return ['errorDescription'=>'PASSWORD_NOT_SECURE','file'=>__FILE__,'line'=>__LINE__];}
					$data['userSalt'] = users_generateSalt();
					$data['userPass'] = sha1($data['userSalt'].$data['userPass']);
				}
				if( isset($data['userStatus']) ){$data['userStatus'] = intval($data['userStatus']);}

				return $data;
			},
			$params);

		return $userOB;
	}
	function users_getByID($id = ''){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		return mongo_collection_getByID($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users'],$id);
	}
	function users_getByIDs($ids = [],$params = []){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		$ids = array_diff($ids,['']);
		$ids = array_unique($ids);
		$ids = array_map(function($id){
			if(is_string($id)){$id = (preg_match('/[a-zA-Z0-9]+/',$id) && strlen($id) == 24) ? new MongoId($id) : intval($id);}
			return $id;
		},$ids);
		$ids = array_values($ids);
		if( !$ids ){return [];}
		$whereClause = ['_id'=>['$in'=>$ids]];
		return users_getWhere($whereClause,$params);
	}
	function users_getByMail($userMail = ''){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		$collection = mongo_collection_get($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users']);
		return $collection->findOne(['userMail'=>$userMail]);
	}
	function users_getByNameFixed($userNameFixed = ''){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		$collection = mongo_collection_get($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users']);
		return $collection->findOne(['userNameFixed'=>$userNameFixed]);
	}
	function users_count($clause = [],$params = []){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		$collection = mongo_collection_get($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users']);
		return $collection->count($clause);
	}
	function users_getSingle($whereClause = false,$params = []){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		return mongo_getSingle($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users'],$whereClause,$params);
	}
	function users_getWhere($whereClause = false,$params = []){
		if(!function_exists('mongo_client_get')){include_once('inc.mongo.php');}
		return mongo_getWhere($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users'],$whereClause,$params);
	}
	function users_removeWhere($clause = [],$params = []){
		if( !function_exists('mongo_client_get') ){include_once('inc.mongo.php');}
		$collection = mongo_collection_get($GLOBALS['api']['users']['db.name'],$GLOBALS['api']['users']['table.users']);
		return $collection->remove($clause);
	}

	function users_login($userOB = false,$userPass = ''){
		if(is_string($userOB) && strpos($userOB,'@')){
			if( !($userOB = users_getByMail($userOB)) ){
				return ['errorDescription'=>'USER_ERROR','file'=>__FILE__,'line'=>__LINE__];
			}
		}
		if(!isset($userOB['userSalt'])){return ['errorDescription'=>'USER_ERROR','file'=>__FILE__,'line'=>__LINE__];}
		$userPass = sha1($userOB['userSalt'].$userPass);
		if( $userPass != $userOB['userPass'] ){return ['errorDescription'=>'PASSWORD_ERROR','file'=>__FILE__,'line'=>__LINE__];}
		/* User must be validated */
		if( !isset($userOB['userStatus']) || !$userOB['userStatus'] ){return ['errorDescription'=>'USER_NOT_ACTIVE','file'=>__FILE__,'line'=>__LINE__];}

		$newCode = users_generateCode($userOB['userMail']);
		$userOB  = array_merge($userOB,['userAddr'=>$_SERVER['REMOTE_ADDR'],'userLastLogin'=>time(),'userCode'=>[$newCode=>time()]]);
		$r = users_save($userOB);
		if(isset($r['errorDescription'])){return $r;}

		return users_impersonate($userOB);
	}
	function users_impersonate($userOB = []){
		session_start();
		setcookie('u',key($userOB['userCode']),time()+360000,'/');
		$_SESSION['user'] = $GLOBALS['user'] = $userOB;
		return $userOB;
	}
	function users_logout(){
		session_destroy();
		setcookie('u','',-1,'/');
	}
	function users_isLogged(){
		if( isset($GLOBALS['user']) && is_array($GLOBALS['user']) ){return true;}
		if( isset($_SESSION['user']) && is_array($_SESSION['user']) ){$GLOBALS['user'] = $_SESSION['user'];return true;}
		if( isset($_COOKIE['u']) && strlen($_COOKIE['u']) == 40 ){
			$_COOKIE['u'] = preg_replace('/[^0-9a-zA-Z]*/','',$_COOKIE['u']);
			$userOB = users_getSingle(['userAddr'=>$_SERVER['REMOTE_ADDR'],'userCode.'.$_COOKIE['u']=>['$exists'=>true]]);
			if( !$userOB || !isset($userOB['_id']) ){setcookie('u','',-1,'/');return false;}
			$_SESSION['user'] = $GLOBALS['user'] = $userOB;
			return true;
		}
		return false;
	}
	function users_checkModes($mode = '',$userOB = []){
		if( isset($GLOBALS['user']) && !$userOB ){$userOB = $GLOBALS['user'];}
		if( !isset($userOB['userModes']) ){return false;}
		/* El campo no se podía indexar en mongo, asique hay que buscar en los valores */
		return (array_search($mode,$userOB['userModes']) !== false);
	}

	function users_generateCode($userMail){
		return sha1($userMail.time().date('Y-m-d H:i:s'));
	}
	function users_generateSalt(){
		$pass_a = ['?','$','¿','!','¡','{','}'];
	    	$pass_b = ['a','e','i','o','u','b','c','d','f','g','h','j','k','l','m','n','p','q','r','s','t','v','w','x','y','z'];
		$salt = '';for($a=0; $a<4; $a++){$salt .= $pass_a[array_rand($pass_a)];$salt .= $pass_b[array_rand($pass_b)];}
		return $salt;
	}

	function users_avatar_get($id = false,$size = false){
		$userPath = $GLOBALS['api']['users']['dir.users'].$id.'/avatar/';
		if( !file_exists($userPath) ){return false;}
		$imagePath = $userPath.str_replace('.','',$size).'.jpeg';
		if(!file_exists($imagePath)){
			$imagePath = $userPath.'orig.jpeg';
			if(!file_exists($imagePath)){return false;}
		}
		return $imagePath;
	}
	function users_avatar_save($id = false,$filePath = ''){
		include_once('inc.images.php');
		$res = image_getResource($filePath);if(!$res){return ['errorDescription'=>'NOT_AN_IMAGE','file'=>__FILE__,'line'=>__LINE__];}
		$userPath = $GLOBALS['api']['users']['dir.users'].$id.'/avatar/';
		if(!file_exists($userPath)){$oldmask = umask(0);$r = @mkdir($userPath,0777,1);umask($oldmask);}
		$origPath = $userPath.'orig';
		$oldmask = umask(0);
		$r = @rename($filePath,$origPath);
		chmod($origPath,0777);

		/* Salvamos la imagen original en png y jpeg */
		$r = image_convert($origPath,'jpeg');
		$r = image_convert($origPath,'png');
		/* Realizamos los diferentes tamaños */
		$sizes = ['32','64','128','256','306'];$overWrite = true;
		foreach($sizes as $k=>$size){
			$destPath = $userPath.$size.'.jpeg';
			if($overWrite === false && file_exists($destPath)){continue;}
			if(!is_numeric($size[0])){unset($sizes[$k]);continue;}
			if(strpos($size,'x') !== false){$r = image_thumb($res,$destPath,$size);continue;}
			$r = image_square($res,$destPath,$size);
		}
		umask($oldmask);

		imagedestroy($res);
		return true;
	}
