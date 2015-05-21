<?php
	if( !isset($GLOBALS['api']['mongo']) ){$GLOBALS['api']['mongo'] = [];}
	$GLOBALS['api']['mongo'] = array_merge([
		 'db'=>[]
		,'collection'=>[]
	],$GLOBALS['api']['mongo']);

	class _mongo{
		public $db     = 'hotelvamp';
		public $server = 'db';
		public $table  = '';
		public $otable = '';
		public $client = false;
		public $collection = false;
		public $search_fields = [];
		public function __construct($table = false,$otable = false){
			if( $table ){$this->table = $table;}
			if( $otable ){$this->otable = $otable;}
			if( $this->otable && !isset($GLOBALS['api']['mongo']['tables'][$this->table]) ){
				$GLOBALS['api']['mongo']['tables'][$this->table]  = $GLOBALS['api']['mongo']['tables'][$this->otable];
				$GLOBALS['api']['mongo']['indexes'][$this->table] = $GLOBALS['api']['mongo']['indexes'][$this->otable];
			}
		}
		public function client_get(){
			if( $this->client ){return true;}
			if( isset($GLOBALS['api']['mongo']['db'][$this->server]) ){
				$this->client = &$GLOBALS['api']['mongo']['db'][$this->server];
				return true;
			}

			try{
				$GLOBALS['api']['mongo']['db'][$this->server] = new MongoClient( ($this->server != 'db' ? $this->server : null) );
				$this->client = &$GLOBALS['api']['mongo']['db'][$this->server];
				if( !method_exists($this->client,'selectCollection') ){return ['errorDescription'=>'UNKNOWN_ERROR','file'=>__FILE__,'line'=>__LINE__];}
				return true;
			}catch(MongoException $e){
				return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];
			}
		}
		public function collection_get(){
			if( $this->collection ){return true;}
			if( isset($GLOBALS['api']['mongo']['collection'][$this->server][$this->db][$this->table]) ){
				$this->collection = &$GLOBALS['api']['mongo']['collection'][$this->server][$this->db][$this->table];
				return true;
			}

			$r = $this->client_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			try{
				$GLOBALS['api']['mongo']['collection'][$this->server][$this->db][$this->table] = $this->client->selectCollection($this->db,$this->table);
				$this->collection = &$GLOBALS['api']['mongo']['collection'][$this->server][$this->db][$this->table];
			}catch(MongoException $e){
				return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];
			}

			if( isset($GLOBALS['api']['mongo']['indexes'][$this->table]) ){
				foreach($GLOBALS['api']['mongo']['indexes'][$this->table] as $index){
					$params = [$index['fields']];
					if( isset($index['props']) ){$params[] = $index['props'];}
					try{
						$this->collection->ensureIndex($index['fields'],isset($index['props']) ? $index['props'] : []);
					}catch(MongoException $e){
						$errorDescription = $e->doc['err'];
						if( preg_match('/Index with name: (?<indexName>[^_]+)_1 already exists with different options/',$errorDescription,$m) ){
							/* Ante este tipo de error, volvemos a generar los índices */
							$this->collection->deleteIndexes([]);
							return $this->collection_get();
						}
						return ['errorCode'=>$e->doc['code'],'errorDescription'=>$errorDescription,'file'=>__FILE__,'line'=>__LINE__];
					}
				}
			}
			return true;
		}
		public function count($clause = [],$params = []){return $this->_count($clause,$params);}
		public function getByID($id = false,$params = []){return $this->_getByID($id,$params);}
		public function getSingle($clause = [],$params = []){return $this->_getSingle($clause,$params);}
		public function getWhere($clause = [],$params = []){return $this->_getWhere($clause,$params);}
		public function removeByID($id = false,$params = []){return $this->_removeByID($id,$params);}
		public function removeWhere($clause = [],$params = []){return $this->_removeWhere($clause,$params);}
		public function aggregate($plan = [],$params = []){return $this->_aggregate($plan,$params);}
		public function validate(&$data = [],&$oldData = []){return $data;}
		public function save(&$data = [],$params = []){return $this->_save($data,$params);}
		public function iterator($clause = [],$callback = false,$params = []){return $this->_iterator($clause,$callback,$params);}
		public function search($criteria = '',$params = []){return $this->_search($criteria,$params);}
		public function _count($clause = [],$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			return $this->collection->count($clause);
		}
		public function _getByID($id = false,$params = []){
			if( isset($id) && is_string($id) && strlen($id) == 24 && preg_match('/^[a-z0-9]+$/',$id) ){
				try{$id = new MongoId($id);}
				catch(MongoException $e){return false;}
			}
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return false;}
			try{
				return $this->collection->findOne(['_id'=>$id]);
			}catch(MongoException $e){
				return false;
			}
		}
		public function _getSingle($clause = [],$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}

			$data = ['$query'=>$clause];

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

			$row = $this->collection->findOne($data);
			return $row;
		}
		public function _getWhere($clause = '',$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}

			if( !isset($params['indexBy']) ){$params['indexBy'] = '_id';}
			if( !isset($params['order']) ){$params['order'] = ['_id'=>1];}
			$skip  = 0;
			$limit = 20000;
			if( isset($params['limit']) ){
				$limit = $params['limit'];
				if(strpos($params['limit'],',')){list($skip,$limit) = explode(',',$params['limit']);}
			}
			if( is_string($params['order']) ){
				$sort = [$params['order']=>1];
				if(($p = strpos($params['order'],' '))){
					/* Support for 'ORDER field (ASC|DESC)' */
					$field = substr($params['order'],0,$p);
					$o = substr($params['order'],$p+1);
					$sort = [$field=>($o == 'ASC') ? 1 : -1];
				}
				$params['order'] = $sort;
			}

			if( !isset($params['fields']) ){$params['fields'] = [];}
			$r = $this->collection->find($clause,$params['fields'])->sort($params['order'])->skip($skip)->limit($limit);
			if( isset($params['cursor']) && $params['cursor'] ){return $r;}

			$rows = [];
			if( $r && $params['indexBy'] !== false ){foreach( $r as $row ){
				if( !isset($row[$params['indexBy']]) ){$rows[] = $row;continue;}
				$k = is_array($row[$params['indexBy']]) ? implode('.',$row[$params['indexBy']]) : strval($row[$params['indexBy']]);
				$rows[$k] = $row;
			}}
			else{foreach($r as $row){$rows[] = $row;}}
			return $rows;
		}
		public function _removeByID($id = false,$params = []){
			if( isset($id) && is_string($id) && strlen($id) == 24 && preg_match('/^[a-z0-9]+$/',$id) ){
				try{$id = new MongoId($id);}
				catch(MongoException $e){return false;}
			}
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return false;}
			try{
				return $this->collection->remove(['_id'=>$id]);
			}catch(MongoException $e){
				return false;
			}
		}
		public function _removeWhere($clause = [],$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			try{
				return $this->collection->remove($clause);
			}catch(MongoException $e){
				return false;
			}
		}
		public function _aggregate($plan = [],$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			$rs = $this->collection->aggregate($plan);
			return $rs;
		}
		public function _save(&$data = [],$params = []){
			/* INI-Remove invalid params */
			if( isset($GLOBALS['api']['mongo']['tables'][$this->table]) ){
				foreach($data as $k=>$v){if( !isset($GLOBALS['api']['mongo']['tables'][$this->table][$k]) ){unset($data[$k]);}}
			}
			/* END-Remove invalid params */

			$oldData = [];
			if( isset($data['_id']) && !($oldData = $this->_getByID($data['_id'],$params)) ){
				$oldData = [];
			}

			//$data = array_replace_recursive($oldData,$data);
			$data = $data+$oldData;
			if( isset($data['_id']) && is_string($data['_id']) && strlen($data['_id']) == 24 && preg_match('/^[a-z0-9]+$/',$data['_id']) ){
				try{$data['_id'] = new MongoId($data['_id']);}
				catch(MongoException $e){return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];}
			}
			if( !isset($data['_id']) ){$data['_id'] = new MongoId();}

			/* INI-validations */
			$data = $this->validate($data,$oldData);
			if( isset($data['errorDescription']) ){return $data;}
			/* END-validations */

			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			try{
				$this->collection->save($data);
			}catch(MongoException $e){
				return ['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__];
			}

			return true;
		}
		public function _iterator($clause = [],$callback = false,$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			if(!$callback || !is_callable($callback)){return ['errorDescription'=>'NO_CALLBACK','file'=>__FILE__,'line'=>__LINE__];}

			//$skip = 0;if(isset($params['skip'])){$skip = $params['skip'];}
			//$chunk = 500;if(isset($params['chunk'])){$chunk = $params['chunk'];}
			$bar   = function_exists('cli_pbar') && isset($params['bar']) ? 'cli_pbar' : false;
			$total = $this->collection->count($clause);
			$params['cursor'] = true;
			$c = 0;

			$cursor = $this->_getWhere($clause,$params);
			while( ($row = $cursor->getNext()) ){
				$c++;if( $bar ){$bar($c,$total,$size=30);}
				$r = $callback($row,$this->collection);
				if( $r === 'break' ){break;}
			}

			return true;
		}
		public function _bulk(&$dataArray = [],$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}
			$dataArray  = array_map([$this,'validate'],$dataArray);
			$updtArray  = [];
//FIXME:  try catch
			try{
				$this->collection->batchInsert($dataArray,['continueOnError'=>true]);
			}catch(MongoException $e){
				print_r(['errorCode'=>$e->getCode(),'errorDescription'=>$e->getMessage(),'file'=>__FILE__,'line'=>__LINE__]);
exit;
			}
			return true;
		}
		public function _search($criteria = '',$params = []){
			$r = $this->collection_get();
			if( is_array($r) && isset($r['errorDescription']) ){return $r;}

			if( !$this->search_fields ){return [];}
			$limitRows = 500;if(isset($params['row.limit'])){$limitRows = $params['row.limit'];}
			$match = false;if(isset($params['match'])){$match = $params['match'];}

			$words = explode(' ',$criteria);
			$countWords = count($words);
			$modeMultipleWords = ($countWords > 1);
			$criteriaLength = strlen($criteria);

			$cnd = [];
			foreach($this->search_fields as $field){
				foreach($words as $word){
					$cnd[] = [$field=>['$regex'=>$word,'$options'=>'i']];
				}
			}
			$clause = ['$or'=>$cnd];
			if( $match ){$clause = ['$and'=>[$match,['$or'=>$cnd]]];}

			$cursor = $this->collection->find($clause);
			$result = [];
			$i = 0;
			while($row = $cursor->getNext()){
				$i++;
				$score = 0;
				foreach($this->search_fields as $k=>$field){
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
	}

