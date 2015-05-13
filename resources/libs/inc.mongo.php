<?php
	if(!isset($GLOBALS['api']['mongo'])){$GLOBALS['api']['mongo'] = array();}
	$GLOBALS['api']['mongo'] = array_merge(array(
		'db'=>[],
		'db.name'=>false,
		'collection'=>[]
	),$GLOBALS['api']['mongo']);

	function mongo_client_get($params = []){
		$id = 'db';
		if( isset($params['server']) && $params['server'] ){$id = $params['server'];}
		if( isset($GLOBALS['api']['mongo']['db'][$id]) ){return $GLOBALS['api']['mongo']['db'][$id];}

		$args = null;
		if( isset($params['server']) && $params['server'] ){$args = $params['server'];}

		try{
			$GLOBALS['api']['mongo']['db'][$id] = new MongoClient($args);
		}catch(MongoException $e){
			return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];
		}
		if( !method_exists($GLOBALS['api']['mongo']['db'][$id],'selectCollection') ){return false;}
		return $GLOBALS['api']['mongo']['db'][$id];
	}
	function mongo_database_get($dbName = '',$params = []){
		$client = mongo_client_get($params);
		return $client->selectDB($dbName);
	}
	function mongo_collection_get($dbName = '',$tbname = '',$params = []){
		if(isset($GLOBALS['api']['mongo']['collection'][$dbName][$tbname])){return $GLOBALS['api']['mongo']['collection'][$dbName][$tbname];}
		$client = mongo_client_get($params);
		if( is_array($client) && isset($client['errorDescription']) ){return $client;}
		try{
			$GLOBALS['api']['mongo']['collection'][$dbName][$tbname] = $client->selectCollection($dbName,$tbname);
		}catch(MongoException $e){
			return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];
		}

		if(isset($GLOBALS['api']['mongo']['indexes'][$tbname])){
			foreach($GLOBALS['api']['mongo']['indexes'][$tbname] as $index){
				$params = [$index['fields']];
				if(isset($index['props'])){$params[] = $index['props'];}
				try{
					call_user_func_array([$GLOBALS['api']['mongo']['collection'][$dbName][$tbname],'ensureIndex'],$params);
				}catch(MongoException $e){
					$errorDescription = $e->doc['err'];
					if( preg_match('/Index with name: (?<indexName>[^_]+)_1 already exists with different options/',$errorDescription,$m) ){
						/* Ante este tipo de error, volvemos a generar los índices */
						call_user_func_array([$GLOBALS['api']['mongo']['collection'][$dbName][$tbname],'deleteIndexes'],[]);
						return call_user_func_array(__FUNCTION__,func_get_args());
					}
					return ['errorCode'=>$e->doc['code'],'errorDescription'=>$errorDescription,'file'=>__FILE__,'line'=>__LINE__];
				}
			}
		}
		//FIXME: hacer índices
		return $GLOBALS['api']['mongo']['collection'][$dbName][$tbname];
	}
	function mongo_collection_save($dbName = '',$tbname = '',&$data = [],$validator = false,$params = []){
		/* Remove invalid params */
		foreach($data as $k=>$v){
			if(!isset($GLOBALS['api']['mongo']['tables'][$tbname][$k])){unset($data[$k]);}
		}

		$oldData = [];
		if(isset($data['_id']) && !($oldData = mongo_collection_getByID($dbName,$tbname,$data['_id'],$params)) ){
			//unset($data['_id']);
			$oldData = [];
		}

		//$data = array_replace_recursive($oldData,$data);
		$data = $data+$oldData;
		if(isset($data['_id']) && is_string($data['_id']) && strlen($data['_id']) == 24 && preg_match('/^[a-z0-9]+$/',$data['_id'])){
			try{$data['_id'] = new MongoId($data['_id']);}
			catch(MongoException $e){return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];}
		}
		if(!isset($data['_id'])){$data['_id'] = new MongoId();}

		/* INI-validations */
		if($validator && is_callable($validator)){
			$data = $validator($data,$oldData);
			if(isset($data['errorDescription'])){return $data;}
		}
		/* END-validations */

		$collection = mongo_collection_get($dbName,$tbname,$params);
		if( is_array($collection) && isset($collection['errorDescription']) ){return $collection;}
		$r = false;
		try{
			$r = $collection->save($data);
		}catch(MongoException $e){
			return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];
		}

		return $data;
	}
	function mongo_collection_iterator($dbname = '',$tbname = '',$whereClause = [],$callback = false,$params = []){
		if(!$callback || !is_callable($callback)){
			return ['errorDescription'=>'NO_CALLBACK','file'=>__FILE__,'line'=>__LINE__];
		}
		$skip = 0;if(isset($params['skip'])){$skip = $params['skip'];}
		$chunk = 500;if(isset($params['chunk'])){$chunk = $params['chunk'];}
		$bar = function_exists('cli_pbar') && isset($params['bar']) ? 'cli_pbar' : false;
		$collection = mongo_collection_get($dbname,$tbname);
		$total = $collection->count();
		$c = 0;

		while($objectOBs = mongo_getWhere($dbname,$tbname,$whereClause,['limit'=>$skip.','.$chunk,'order'=>'_id ASC'])){
			$skip += $chunk;
			foreach($objectOBs as $objectOB){
				$c++;
				if($bar){$bar($c,$total,$size=30);}
				$callback($objectOB,$collection);
			}
		}

		return true;
	}
	function mongo_collection_getByID($dbname = '',$tbname = '',$id = false,$params = []){
		if(isset($id) && is_string($id) && strlen($id) == 24 && preg_match('/^[a-z0-9]+$/',$id)){
			try{
				$id = new MongoId($id);
			}catch(MongoException $e){
				return false;
			}
		}
		$collection = mongo_collection_get($dbname,$tbname,$params);
		if( is_array($collection) && isset($collection['errorDescription']) ){
print_r($collection);
return false;}
		try{
			return $collection->findOne(['_id'=>$id]);
		}catch(MongoException $e){
			return false;
		}
	}
	function mongo_collection_getByIDs($dbname = '',$tbname = '',$ids = [],$params = []){
		$ids = array_diff($ids,['']);
		$ids = array_unique($ids);
		$ids = array_map(function($id){
			if(is_string($id)){$id = preg_match('/[a-zA-Z]+/',$id) && strlen($id) == 24 ? new MongoId($id) : intval($id);}
			return $id;
		},$ids);
		$clause = ['_id'=>['$in'=>array_values($ids)]];
		return mongo_getWhere($dbname = '',$tbname = '',$clause,$params);
	}
	function mongo_collection_removeWhere($dbname = '',$tbname = '',$clause = '',$params = []){
		$collection = mongo_collection_get($dbname,$tbname,$params);
		try{
			return $collection->remove($clause);
		}catch(MongoException $e){
			return false;
		}
	}
	function mongo_collection_getFullText($dbname = '',$tbname = '',$criteria = '',$fields = [],$params = []){
		$limitRows = 500;if(isset($params['row.limit'])){$limitRows = $params['row.limit'];}
		$match = false;if(isset($params['match'])){$match = $params['match'];}

		$words = explode(' ',$criteria);
		$countWords = count($words);
		$modeMultipleWords = ($countWords > 1);
		$criteriaLength = strlen($criteria);

		$cnd = [];
		foreach($fields as $field){
			foreach($words as $word){
				$cnd[] = [$field=>['$regex'=>$word,'$options'=>'i']];
			}
		}
		$collection = mongo_collection_get($dbname,$tbname);
		$clause = ['$or'=>$cnd];
		if($match){$clause = ['$and'=>[$match,['$or'=>$cnd]]];}

		$cursor = $collection->find($clause);
		$result = [];
		$i = 0;
		while($row = $cursor->getNext()){
			$i++;
			$score = 0;
			foreach($fields as $k=>$field){
				if(!isset($row[$field])){continue;}
				if($modeMultipleWords && stripos($row[$field],$criteria) !== false){$score += (2*$criteriaLength)+$countWords;continue;}
				$row[$field] = ' '.$row[$field].' ';
				$total = $countWords;
				foreach($words as $word){
					if(stripos($row[$field],' '.$word.' ') !== false){$score += strlen($word)+$total;continue;}
					if(stripos($row[$field],$word) !== false){$score += (0.5*strlen($word))+$total;continue;}
					$total--;
				}
			}
			$result[ceil($score).'.'.$i] = $row;
			krsort($result);
			if(count($result) > $limitRows){array_splice($result,$limitRows);}
		}

		return array_values($result);
	}

	function mongo_processCondition($cond = ''){
		switch(true){
			case preg_match('/^(?<field>[^ ]+) = (?<value>[^\)]+)$/',$cond,$m):return array($m['field']=>$m['value']);
			case preg_match('/^(?<fieldName>[^ ]+) IS NULL$/',$cond,$m):return array('$exists'=>true);
			default:
				echo 'Not supported condition '.$cond.PHP_EOL;exit;
		}
	}
	function mongo_processWhere($whereClause = ''){
		$find = array();
		switch(true){
			case ($whereClause === 1 || !$whereClause || $whereClause === '1'):return $find;
			case preg_match('/^[\(]*(?<field>[^ ]+) = (?<value>[^\)]+)[\)]*$/',$whereClause,$m):
				if($m['value'][0] !== '\'' && $m['value'][0] !== '"' && is_numeric($m['value'])){$m['value'] = floatval($m['value']);}
				return array($m['field']=>$m['value']);
			case preg_match('/^[\(]*(?<fieldName>[^ ]+) IN \((?<values>[^\)]+)\)[\)]*$/',$whereClause,$m):
				$integers = $m['values'][0] != '\'';
				$values = explode(',',str_replace('\'','',$m['values']));
				if($integers){$values = array_map(function($n){return intval($n);},$values);}
				$find[$m['fieldName']] = array('$in'=>$values);
				return $find;
			case preg_match('/^[\(]*(?<cond1>[^\)]+) OR (?<cond2>[^\)]+)[\)]*$/',$whereClause,$m):
				$cond1 = mongo_processCondition($m['cond1']);
				$cond2 = mongo_processCondition($m['cond2']);
				$find = array('$or'=>array($cond1,$cond2));
				return $find;
			default:
				echo 'Not supported '.$whereClause.PHP_EOL;exit;
		}
		return $find;
	}
	function mongo_getSingle($dbname = '',$tbname = '',$whereClause = '',$params = []){
		$query = is_string($whereClause) ? mongo_processWhere($whereClause) : $whereClause;
		$collection = mongo_collection_get($dbname,$tbname,$params);
		if( is_array($collection) && isset($collection['errorDescription']) ){return $collection;}
		$data = ['$query'=>$query];

		/* INI-Soporte para ordenación */
		if( isset($params['order']) ){do{
			if( is_array($params['order']) ){$data['$orderby'] = $params['order'];break;}
			if( ($p = strpos($params['order'],' ')) ){
				/* Support for 'ORDER field (ASC|DESC)' */
				$field = substr($params['order'],0,$p);
				$o = substr($params['order'],$p+1);
				$data['$orderby'] = [$field=>($o == 'ASC') ? 1 : -1];
				break;
			}
			$data['$orderby'] = [$params['order']=>1];
		}while(false);}
		/* END-Soporte para ordenación */

		$row = $collection->findOne($data);
		return $row;
	}
	function mongo_getWhere($database = '',$tbname = '',$whereClause = '',$params = []){
		if( !isset($params['indexBy']) ){$params['indexBy'] = '_id';}
		if( !isset($params['order']) ){$params['order'] = ['_id'=>1];}
		$skip = 0;
		$limit = 20000;
		if(isset($params['limit'])){
			$limit = $params['limit'];
			if(strpos($params['limit'],',')){list($skip,$limit) = explode(',',$params['limit']);}
		}
		if( is_string($params['order']) ){
			$sort = [$params['order']=>1];
			if(($p = strpos($params['order'],' '))){
				/* Support for 'ORDER field (ASC|DESC)' */
				$field = substr($params['order'],0,$p);
				$o = substr($params['order'],$p+1);
				$sort = array($field=>($o == 'ASC') ? 1 : -1);
			}
			$params['order'] = $sort;
		}

		$db = mongo_client_get($params);
		$find = is_array($whereClause) ? $whereClause : mongo_processWhere($whereClause);
		$collection = $db->selectCollection($database,$tbname);

		if(isset($params['selectString']) && preg_match('/count\((?<field>[^\)]+)\) as (?<alias>[^, ])/',$params['selectString'],$m)){
			$params['selectString'] = str_replace($m[0],',',$params['selectString']);
			$selectString = explode(',',$params['selectString']);
			$selectString = array_diff($selectString,array(''));
			$selectString = array_fill_keys($selectString,1);
			if(!isset($selectString[$params['indexBy']])){$params['indexBy'] = false;}

			if($m['field'] == '*'){$m['field'] = 'id';}
			if(isset($params['group'])){$m['field'] = $params['group'];}
			$pipeline = array();
			if($find){$pipeline[] = array('$match'=>$find);}
			$pipeline[] = array('$group'=>array('_id'=>'$'.$m['field'],$m['alias']=>array('$sum'=>1)));
			$pipeline[] = ['$sort'=>$params['order']];
			if($skip){$pipeline[] = array('$skip'=>$skip);}
			if($limit){$pipeline[] = array('$limit'=>$limit);}
			$r = $collection->aggregate($pipeline);
			$countResult = $r['result'];

			$tmp = array();foreach($countResult as $result){$tmp[$result['_id']] = $result[$m['alias']];}$countResult = $tmp;
			$values = array_keys($countResult);
			$find = array($m['field']=>array('$in'=>$values));
			$r = $collection->find($find,$selectString);
			$r = iterator_to_array($r);
			foreach($r as &$row){
				if(!isset($countResult[$row[$m['field']]])){$row[$m['alias']] = 0;continue;}
				$row[$m['alias']] = $countResult[$row[$m['field']]];
			}
		}else{
			if( !isset($params['fields']) ){$params['fields'] = [];}
			$r = $collection->find($find,$params['fields'])->sort($params['order'])->skip($skip)->limit($limit);
		}

		$rows = [];
		if($r && $params['indexBy'] !== false){foreach($r as $row){
			if(!isset($row[$params['indexBy']])){$rows[] = $row;continue;}
			$rows[strval($row[$params['indexBy']])] = $row;}
		}
		if($r && $params['indexBy'] === false){foreach($r as $row){$rows[] = $row;}}
		return $rows;
	}
	function mongo_id_byTimestamp($timestamp = false){
		if(!$timestamp){$timestamp = time();}
		static $inc = 0;

		$ts = pack('N',$timestamp);
		$m = substr(md5(gethostname()),0,3);
		$pid = pack('n',posix_getpid());
		$trail = substr(pack('N',$inc++),1,3);

		$bin = sprintf('%s%s%s%s',$ts,$m,$pid,$trail);

		$id = '';
		for($i = 0; $i < 12; $i++){
			$id .= sprintf('%02X',ord($bin[$i]));
		}
		$id = strtolower($id);
		return new MongoID($id);
	}



	function mongo_get(){
		if($GLOBALS['api']['mongo']['db'] !== false){
			return $GLOBALS['api']['mongo']['db'];
		}

		$GLOBALS['api']['mongo']['db'] = new MongoClient();
		return $GLOBALS['api']['mongo']['db'];
	}
	function mongo_connect($database = ''){
		$m = new MongoClient();
		$m->selectDB($database);
		$m->dbName = $database;
		return $m;
	}
	function mongo_autoincrement($m,$tbname,$field){
		$id = $tbname.'.'.$field;
		//$m->counters->insert(array('_id'=>$id,'seq'=>0));
print_r($m->counters);
		$m->counters->findAndModify( array('_id'=>$id) , array('$inc'=>array('seq'=>1)) , null , array('new'=>true) );
	}
	function mongo_save(&$collection = false,$row = array(),&$params = array()){
		if(!isset($params['db'])){
			//FIXME:
			$params['db'] = mongo_connect('spoiler');
		}
		if(is_string($collection)){
			$collection = $params['db']->selectCollection($params['db']->dbName,$collection);
		}
		$name = $collection->getName();

		$autoincrements = array();
		if(isset($GLOBALS['tables'][$name])){
			//foreach(){}
			//print_r($GLOBALS['tables'][$name]);
		}
	}
