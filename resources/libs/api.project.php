<?php
	if( !class_exists('_mongo') ){include_once('classes/class._mongo.php');}

	/* INI-mongo tables */
	$GLOBALS['api']['mongo']['tables']['project'] = [
		 '_id'=>'INTEGER AUTOINCREMENT'
		,'projectName'=>'TEXT'
		,'projectNameFixed'=>'TEXT'
		,'projectDescription'=>'TEXT'
		,'projectTotalTime'=>'TEXT'
		,'projectTags'=>'TEXT'
		,'projectUsers'=>'TEXT'
		,'z'=>'TEXT'/* Datos extra, 'z' para que vayan al final */
	];
	$GLOBALS['api']['mongo']['tables']['task'] = [
		 '_id'=>'INTEGER'
		,'taskProjectID'=>'INTEGER'
		,'taskName'=>'TEXT'
		,'taskNameFixed'=>'TEXT'
		,'taskDescription'=>'TEXT'
		,'taskTags'=>'TEXT'
		,'taskStatus'=>'TEXT'
		,'taskTime'=>'INTEGER'
		,'taskUser'=>'TEXT'
		,'taskStamp'=>'TEXT'
		,'taskPriority'=>'TEXT'
	];
	/* END-mongo tables */

	class projectTB extends _mongo{
		public $table = 'project';
		public $search_fields = ['projectName'];
		public function validate(&$data = [],&$oldData = []){
			if( !function_exists('strings_toURL') ){include_once('inc.strings.php');}
			if( isset($data['projectName']) ){
				$data['projectNameFixed'] = strings_toURL($data['projectName']);
			}
			if( !isset($data['projectUsers']) && isset($GLOBALS['user']['_id']) ){
				$data['projectUsers'][$GLOBALS['user']['_id']] = 'admin';
			}
			return $data;
		}
	}

	class taskTB extends _mongo{
		public $table = 'task';
		public $search_fields = ['taskName'];
		public function validate(&$data = [],&$oldData = []){
			if( !function_exists('strings_toURL') ){include_once('inc.strings.php');}
			if( isset($data['taskName']) ){
				$data['taskNameFixed'] = strings_toURL($data['taskName']);
			}
			if( isset($data['taskTags']) && is_string($data['taskTags']) ){$data['taskTags'] = array_unique(array_diff(explode(',',$data['taskTags']),['']));}

			if( !isset($data['taskStamp']) ){$data['taskStamp'] = time();}
			if( !isset($data['taskPriority']) ){$data['taskPriority'] = 1;}
			if( !isset($data['taskStatus']) ){$data['taskStatus'] = 'open';}
			if( !isset($data['taskTags']) ){$data['taskTags'] = [];}
			if( !isset($data['taskUser']['created']) && isset($GLOBALS['user']['_id']) ){
				$data['taskUser']['created'] = $GLOBALS['user']['_id'];
			}
			return $data;
		}
	}
