<?php
	function path_check($path = ''){
		$oldmask = umask(0);
		$p = pathinfo($path);
		if( !file_exists($p['dirname']) ){
			$r = @mkdir($p['dirname'],0777,1);
		}
		if( substr($path,-1) == '/' && !file_exists($path) ){
			$r = @mkdir($path,0777,1);
		}
		umask($oldmask);
		return $path;
	}
	function path_get(){
		/* args variables -> (':rooms','17591','17-03-2015',true) */
		$args   = func_get_args();
		$exists = false;
		$path   = '';
		if( ($p = current($args)) && is_string($p) && $p[0] == ':' ){$path = array_shift($args);}
		if( is_bool(end($args)) ){$exists = array_pop($args);}

		switch( $path ){
			case ':images': $path = '../db/images/';break;
			case ':cache':  $path = '../db/cache/';break;
			case ':portals':$path = '../db/cache/portals/';break;
		}
		$path .= ($args) ? implode('/',$args).'/' : '';
		if( !$exists && !file_exists($path) ){umask(0);$r = mkdir($path,0777,1);chmod($path,0777);}
		return $path;
	}
