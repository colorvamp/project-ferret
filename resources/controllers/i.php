<?php
	function i_main($id = false){
		if( $id ){return i_profile($id);}
	}

	function i_profile($id = false){
		include_once('inc.mongo.images.php');
		$images = new mongoimages();
		if( !($imageOB = $images->getByID($m['id'])) ){common_r('',404);}
echo 'TODO';
exit;
	}

	function i_src($nameFixed = false){
		if( !preg_match('/^(?<id>[^\.\-]+).(?<mime>jpeg|gif)$/',$nameFixed,$m) ){common_r('',404);}
		$sizes = array_flip($GLOBALS['images']['sizes']);
		$size  = isset($_GET['s']) ? $_GET['s'] : 'orig';
		if( !isset($sizes[$size]) ){$size = 'orig';}
		$mime  = $m['mime'];
		include_once('inc.mongo.images.php');

		$images = new mongoimages();
		$imageOB = $images->getByID($m['id']);
		if( ($uri = presentation_image_src($imageOB,($size != 'orig' ? $size : false))) && $GLOBALS['w.currentURL'] !== $uri ){common_r($uri,301);}
		$path = $images->blob_get($imageOB,$size,$mime);
		if( !$path ){common_r('',404);}

		/* INI-Caché */
		if( !isset($GLOBALS['files']['cache.disable'])
		 || !file_exists($GLOBALS['files']['cache.disable']) ){
			$stat = stat($path);
			$t = $stat['mtime'];
			$m = date('D, d M Y H:m:s \G\M\T',$t);
			header('Last-Modified: '.$m);
			header('Cache-Control: max-age=31557600');
			header_remove('Pragma');
			if(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtolower($_SERVER['HTTP_IF_MODIFIED_SINCE']) == strtolower($m)){header('HTTP/1.1 304 Not Modified');exit;}
		}
		/* END-Caché */

		header('Content-type: image/'.$mime);
		readfile($path);
		exit;
	}

