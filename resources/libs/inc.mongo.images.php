<?php
	if( !class_exists('_mongo') ){include_once('classes/class._mongo.php');}

	/* INI-mongo tables */
	$GLOBALS['api']['mongo']['tables']['mongo.images'] = [
		 '_id'=>'INTEGER AUTOINCREMENT','objectOB'=>'TEXT'
		,'imageName'=>'TEXT'
		,'imageNameFixed'=>'TEXT'
		,'imageDescription'=>'TEXT'
		,'imageTags'=>'TEXT'
		,'imageObject'=>false
		,'imageThumbs'=>false
	];
	/* END-mongo tables */
	/* INI-mongo indexes */
	$GLOBALS['api']['mongo']['indexes']['mongo.images'] = [
		 ['fields'=>['imageObject._id'=>1]]
	];
	/* END-mongo indexes */

	class mongoimages extends _mongo{
		public $table  = 'images';
		public $otable = 'mongo.images';
		public function validate(&$data = [],&$oldData = []){
			if( !function_exists('strings_toURL') ){include_once('inc.strings.php');}
			if( !isset($data['imageName']) && isset($data['imageHash']) ){$data['imageName'] = ['es'=>$data['imageHash']];}
			if( !isset($data['imageDescription']) ){$data['imageDescription'] = [];}
			if( !isset($data['imageTags']) ){$data['imageTags'] = [];}

			if( !isset($oldData['imageName']) ){$oldData['imageName'] = [];}
			if( !isset($oldData['imageDescription']) ){$oldData['imageDescription'] = [];}
			if( !isset($oldData['imageTags']) ){$oldData['imageTags'] = [];}

			if( isset($data['imageName']) && is_string($data['imageName']) ){$data['imageName'] = ['es'=>$data['imageName']]+$oldData['imageName'];}
			if( isset($data['imageDescription']) && is_string($data['imageDescription']) ){$data['imageDescription'] = ['es'=>$data['imageDescription']]+$oldData['imageDescription'];}
			if( isset($data['imageTags']) && is_string($data['imageTags']) ){$data['imageTags'] = ['es'=>explode(',',$data['imageTags'])]+$oldData['imageTags'];}

			foreach(['es','en'] as $lang){
				if( isset($data['imageName'][$lang]) ){
					$data['imageNameFixed'][$lang] = strings_toURL($data['imageName'][$lang]);
					if( ($func = 'strings_discard_'.$lang) && function_exists($func) ){$data['imageNameFixed'][$lang] = $func($data['imageNameFixed'][$lang]);}
				}
				if( isset($data['imageTags'][$lang]) ){
					if( is_string($data['imageTags'][$lang]) ){$data['imageTags'][$lang] = explode(',',$data['imageTags'][$lang]);}
					$data['imageTags'][$lang] = array_map(function($n){return strtolower(trim($n));},$data['imageTags'][$lang]);
					$data['imageTags'][$lang] = array_diff(array_unique($data['imageTags'][$lang]),['']);
					$data['imageTags'][$lang.'Fixed'] = strings_tags_clean($data['imageTags'][$lang]);
				}
			}
			return $data;
		}
		public function save(&$data = [],$params = []){
			$imagePath = isset($data['imagePath']) ? $data['imagePath'] : false;
			$r = $this->_save($data,$params);
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}

			if( $imagePath ){
				//FIXME: eliminar imagenes antiguas
				$r = $this->blob_store($data,$imagePath);
			}
			return true;
		}
		public function getByObjectID($id = '',$params = []){
			if( isset($id) && is_string($id) && strlen($id) == 24 && preg_match('/^[a-z0-9]+$/',$id) ){
				try{$id = new MongoId($id);}
				catch(MongoException $e){return false;}
			}
			return $this->getWhere(['imageObject._id'=>$id],$params);
		}
		public function getByID($id = '',$params = []){
			if( isset($id) && is_string($id) && strlen($id) == 24 && preg_match('/^[a-z0-9]+$/',$id) ){
				try{$id = new MongoId($id);}
				catch(MongoException $e){return false;}
			}
			return $this->getSingle(['_id'=>$id],$params);
		}
		/* INI-Blob */
		public function blob_get(&$imageOB = [],$size = 'orig',$mime = 'jpeg',$params = []){
			include_once('inc.path.php');
			$_valid_mime      = ['jpeg'=>0,'png'=>0,'gif'=>0];
			$_default_quality = ['jpeg'=>90,'png'=>90,'gif'=>false];
			if( !isset($imageOB['_id']) ){return false;}
			if( !isset($_valid_mime[$mime]) ){return false;}

			$prefix  = substr($imageOB['_id'],0,4);
			$folder  = path_get(':images',$prefix,$imageOB['_id']);
			$quality = isset($params['quality']) ? $params['quality'] : $_default_quality[$mime];

			//if( $size == 'orig' ){return $folder.'orig';}
			$path    = $folder.$size.'.'.intval($quality).'.'.$mime;

			if( !file_exists($path) ){
				include_once('inc.images.php');
				$files = glob($folder.'orig.*');
				//FIXME: decidir prioridades mejor
				$orig  = current($files);
				$parts = explode('.',$orig);
				if( ($parts[4] != 'gif') || !image_gif_is_animated($orig) ){
					$res   = image_mimeDecider('image/'.end($parts),$orig);
					$res   = image_resource_resize($res,$size);
					if( $res === false ){return ['errorDescription'=>'INVALID_SIZE','file'=>__FILE__,'line'=>__LINE__];}

					$q = $quality;
					if( $mime == 'png' ){$q = intval($q/10);}
					$funcSave = 'image'.$mime;
					$funcSave($res,$path,$q);
					if( !file_exists($path) ){return ['errorDescription'=>'UNKNOWN_ERROR','file'=>__FILE__,'line'=>__LINE__];}
					chmod($path,0777);
				}else{
					$tmpPath = '/run/shm/'.uniqid().'/';/* la ram */
					if( !file_exists($tmpPath) ){
						$oldmask = umask(0);
						$r = mkdir($tmpPath,0777,1);
						umask($oldmask);
						if(!$r){echo $r;}
					}
					shell_exec('convert -coalesce '.$orig.' '.$tmpPath.'%03d.gif');
					$delay = shell_exec('identify -format "%T\n" '.$orig);
					$delay = explode(PHP_EOL,$delay);
					$delay = array_diff($delay,['']);
					$files = glob($tmpPath.'*');
					$cmd = 'convert ';
					foreach($files as $file){
						if($file == $orig){continue;}
						$res = image_getResource($file);
						$res2 = image_resource_resize($res,$size);
						imageResource_save($res2,$file);
						$cmd .= ' -delay '.current($delay).' '.$file.' ';
						next($delay);
					}
					shell_exec($cmd.' -loop 0 -layers Optimize '.$path);
					chmod($path,0777);
					/* INI-cleanup */
					foreach($files as $file){unlink($file);}
					rmdir($tmpPath);
					/* END-cleanup */
				}

				/* INI-Registramos en base de datos el nuevo tamaño */
				$imgProp = getimagesize($path);
				$hash    = md5_file($path);
				$imageOB['imageThumbs'][$size][$quality][$mime] = [
					 'width'=>$imgProp[0]
					,'height'=>$imgProp[1]
					,'mime'=>$imgProp['mime']
					,'hash'=>$hash
				];
				$this->_save($imageOB);
				/* INI-Registramos en base de datos el nuevo tamaño */
			}
			return $path;
		}
		public function blob_cleanup(&$imageOB = []){
			include_once('inc.path.php');
			if( !isset($imageOB['_id']) ){return false;}
			$prefix = substr($imageOB['_id'],0,4);
			$folder = path_get(':images',$prefix,$imageOB['_id']);
			$files  = glob($folder.'*',GLOB_NOSORT);
			foreach($files as $file){
				unlink($file);
			}
			unset($imageOB['imageThumbs']);
			return $this->_save($imageOB);
		}
		public function blob_store(&$imageOB = [],$path = '',$data = []){
			include_once('inc.path.php');
			if( !isset($imageOB['_id']) ){$this->save($imageOB);}
			if( !($imgProp = @getimagesize($path)) ){return false;}

			$prefix  = substr($imageOB['_id'],0,4);
			$folder  = path_get(':images',$prefix,$imageOB['_id']);
			$quality = '100';
			$hash    = md5_file($path);
			$mime    = substr($imgProp['mime'],6);
			if( isset($imageOB['imageThumbs']) && $imageOB['imageThumbs'] ){
				if( isset($imageOB['imageThumbs']['orig'][$mime]['hash'])
					&& $imageOB['imageThumbs']['orig'][$mime]['hash'] == $hash ){return true;}
				$this->blob_cleanup($imageOB);
			}
			$target = $folder.'orig.'.$quality.'.'.$mime;
			$r = copy($path,$target);
			chmod($target,0777);

			$imageOB['imageThumbs']['orig'][$quality][$mime] = [
				 'width'=>$imgProp[0]
				,'height'=>$imgProp[1]
				,'mime'=>$imgProp['mime']
				,'hash'=>$hash
			]+$data;
			return $this->_save($imageOB);
		}
		/* END-Blob */
	}
