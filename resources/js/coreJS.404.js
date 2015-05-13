	function extend(destination,source){for(var property in source){destination[property] = source[property];}return destination;}
	function $_(id,obj,holder){var el = document.getElementById(id);if(!el){return null;}if(obj){for(var o in obj){if(o.indexOf('.')==0){el.style[o.replace(/^./,'')] = obj[o];continue;}el[o] = obj[o];}}if(holder){holder.appendChild(el);}return el;}
	function $C(tag,obj,holder){
		var el = document.createElement(tag);
		if( obj ){extend(el,obj);}
		if( holder ){holder.appendChild(el);}
		return el;
	}
	function $A(iterable,callback){
		if( !(callback instanceof Function) ){return false;}
		Array.prototype.slice.call(iterable).forEach(callback);
	}
	/* extended $E-lements functions - to avoid too much selector overload */
	var $E = {
		classHas: function(elem,className){var p = new RegExp('(^| )'+className+'( |$)');return (elem.className && elem.className.match(p));},
		classAdd: function(elem,className){if($E.classHas(elem,className)){return true;}elem.className += ' '+className;},
		classRemove: function(elem,className){var c = elem.className;var p = new RegExp('(^| )'+className+'( |$)');c = c.replace(p,' ').replace(/  /g,' ');elem.className = c;},
		classParentHas: function(elem,className,limit){
			limit = typeof limit !== 'undefined' ? limit : 1;
			if($E.classHas(elem,className)){return elem;}
			if(!elem.parentNode){return false;}
			do{if($E.classHas(elem.parentNode,className)){return elem.parentNode;}elem = elem.parentNode;}while(elem.parentNode && limit--);return false;
		},
		class: {
			exists: function(elem,className){var p = new RegExp('(^| )'+className+'( |$)');return (elem.className && elem.className.match(p));},
			add: function(elem,className){if($E.classHas(elem,className)){return true;}elem.className += ' '+className;},
			remove: function(elem,className){var c = elem.className;var p = new RegExp('(^| )'+className+'( |$)');c = c.replace(p,' ').replace(/  /g,' ');elem.className = c;}
		},
		parent: {
			find: function(elem,p){/* p = {tagName:false,className:false} */if(p.tagName){p.tagName = p.tagName.toUpperCase();}if(p.className){p.className = new RegExp('( |^)'+p.className+'( |$)');}while(elem.parentNode && ((p.tagName && elem.tagName!=p.tagName) || (p.className && !elem.className.match(p.className)))){elem = elem.parentNode;}if(!elem.parentNode){return false;}return elem;}
		}
	}
	/* extended $F-unctions functions */
	var $F = {
		find: function(l,pool){if(!pool){pool = window;}var func = pool;var funcSplit = l.split('.');var e = true;for(i = 0;i < funcSplit.length;i++){if(!func[funcSplit[i]]){e = false;break;}func = func[funcSplit[i]];}return e ? func : false;}
	}

	function $capitalize(str){return str.replace(/\w+/g,function(a){return a.charAt(0).toUpperCase()+a.slice(1).toLowerCase();});}
	function $clone(obj){if(obj == null || typeof(obj) != 'object'){return obj;}var temp = obj.constructor();for(var key in obj){temp[key] = $clone(obj[key]);}return temp;}
	function $getElementStyle(obj,styleProp){if(obj.currentStyle){return obj.currentStyle[styleProp];}if(window.getComputedStyle){return document.defaultView.getComputedStyle(obj,null).getPropertyValue(styleProp);}}
	function $getOffsetLeft(el){var ol = 0;while(el.parentNode){ol += el.offsetLeft+parseInt($getElementStyle(el,'padding-left'));el = el.parentNode;}return ol;}
	function $getOffsetTop(el){var ot = 0;while(el.parentNode){ot += el.offsetTop+parseInt($getElementStyle(el,'padding-top'));el = el.parentNode;}return ot;}
	function $getOffsetPosition(el){return el.getBoundingClientRect();}
	function $htmlEntitiesDecode(html){if(!html){return "";}return html.replace(/&amp;/g,"&").replace(/&lt;/g,"<").replace(/&gt;/g,">");};
	function $htmlEntitiesEncode(html){if(!html){return "";}return html.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/\\/g,"");};
	function $parseForm(f,e){var ops = {};$A(f.$T('INPUT')).append(f.$T('TEXTAREA')).append(f.$T('SELECT')).each(function(el){if(el.type=='checkbox'){ops[el.name] = el.checked;return;}if(el.type=='radio' && !el.checked){return;}ops[el.name] = (!e) ? el.value : encodeURIComponent(el.value);});return ops;}
	function $round(num){num = num.toString();if(num.indexOf('.') == -1){return num;}num = (parseFloat(num)*1000).toString().split('.')[0];if(parseInt(num[num.length-1])>4){if(num[0]!='-'){num = (parseInt(num)+10).toString();}else{num = (parseInt(num)-10).toString();}}num = (parseInt(num)/10).toString();num = num.split('.')[0];num = (parseInt(num)/100).toString();return num;}
	function $toUrl(elem){var str = '';for(var a in elem){str += a+'='+encodeURIComponent(elem[a].toString())+'&';}return str.replace(/&$/,'');}
	function $type(obj){return typeof(obj);}
	var $is = {
		empty: function(o){if(!o || ($is.string(o) && o == '') || ($is.array(o) && !o.length)){return true;}return false;},
		array: function(o){return (Array.isArray(o) || $type(o.length) === 'number');},
		string: function(o){return (typeof o == 'string' || o instanceof String);},
		object: function(o){return (o.constructor.toString().indexOf('function Object()') == 0);},
		element: function(o){return ('nodeType' in o && o.nodeType === 1 && 'cloneNode' in o);},
		function: function(o){if(!o){return false;}return (o.constructor.toString().indexOf('function Function()') == 0);},
		formData: function(o){return (o.constructor.toString().indexOf('function FormData()') == 0);}
	};
	var $json = {
		encode: function(obj){if(JSON.stringify){return JSON.stringify(obj);}},
		decode: function(str){
			if($is.empty(str)){return {errorDescription:"La cadena está vacía, revise la API o el COMANDO"};}
			if(!$is.string(str)){return {errorDescription:'JSON_ERROR'};}
			if(str.match("<title>404 Not Found</title>")){return {errorDescription:"La URL de la API es errónea: 404"};}
			if(!JSON || !JSON.parse){return eval('('+str+')');}
			try{return JSON.parse(str);}catch(err){return {errorDescription:str};}
		}
	};

	function print_r(obj,i){
		var s="";if(!i){i = "    ";}else{i += "    ";}
		if(obj.constructor == Array || obj.constructor == Object){
			for(var p in obj){
				if(!obj[p]){s += i+"["+p+"] => NULL\n";continue;};
				if(obj[p].constructor == Array || obj[p].constructor == Object){
					var t = (obj[p].constructor == Array) ? "Array" : "Object";
					s += i+"["+p+"] => "+t+"\n"+i+"(\n"+print_r(obj[p],i)+i+")\n";
				}else{s += i+"["+p+"] => "+obj[p]+"\n";}
			}
		}
		return s;
	}
	/*==INI-INCLUDE-FILES==*/
	function include_once(file,type){
		/* type = (css || js) */
		if(type){var ext = type;}else{/**/var ext = file.match(/(css|js)$/);if(!ext){return;}else{ext = ext[1];}/**/}
		var fileType = ext.replace(/js/i,'script').replace(/css/i,'link');
		var baseName = file.match(/[^\/]*$/);
		var included = false;
		$A($fix($T('HEAD')[0]).$T(fileType.toUpperCase())).each(function(elem){/**/if((elem.src && elem.src == file) || (elem.href && elem.href == file)){included=true;}/**/});
		if(!included){window["include"+ext.toUpperCase()](file);}
	}
	function includeJS(file){return $C('SCRIPT',{'src':file,'type':'text/javascript'},$T('head')[0]);}
	function includeCSS(file){return $C('LINK',{href:file,rel:'stylesheet',type:'text/css'},$T('head')[0]);}
	/*==END-INCLUDE-FILES==*/

	/*==INI-COOKIE-MANAGEMENT==*/
	function cookieTake(cookieName){var value = document.cookie.match('(?:^|;)\\s*' + cookieName.replace(/([-.*+?^${}()|[\]\/\\])/g, '\\$1') + '=([^;]*)');return cookieName =  value ? value[1] : value;}
	function cookieSet(cookieName,value,expDays){var exdate = new Date();exdate.setDate(exdate.getDate()+expDays);document.cookie = cookieName+"="+escape(value)+((expDays==null) ? "" : ";expires="+exdate.toGMTString());}
	function cookieRemove(cookieName){document.cookie = cookieName+"=;expires=Thu, 01-Jan-1970 00:00:01 GMT";}
	function cookiesToObj(){
		var cookies = document.cookie.replace(/;[ ]?/g,";").split(";");if(isEmpty(cookies)){return {};}
		var obj = {};$A(cookies).each(function(elem){elem = elem.match(/([^=]*)=(.*)/);obj[unescape(elem[1])]=unescape(elem[2]);});return obj;
	}
	function cookiesToArr(){
		var cookies = document.cookie.replace(/;[ ]?/g,";").split(";");if(isEmpty(cookies)){return [];}
		var arr = [];$A(cookies).each(function(elem){elem = elem.match(/([^=]*)=(.*)/);arr.push({cookieName:unescape(elem[1]),cookieValue:unescape(elem[2])});});return arr;
	}
	/*==END-COOKIE-MANAGEMENT==*/

	function $ajax(url,params,callbacks){
		var method = 'GET';if(params){method = 'POST';}
		var rnd = Math.floor(Math.random()*10000);
		var data = false;
		if(params){switch(true){
			case params === {}:break;
			case ($is.object(params)):data = new FormData();for(k in params){data.append(k,params[k]);}break;
			default:data = params;
		}}

		var xhr = new XMLHttpRequest();
		xhr.open(method,url+'?rnd='+rnd,true);
		xhr.onreadystatechange = function(){
			if(callbacks.onEnd && xhr.readyState == XMLHttpRequest.DONE){return callbacks.onEnd(xhr.responseText);}
		}
		if(!$is.formData(data)){xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');}
		xhr.send(data);

		if(callbacks.onUpdate){var offset = 0;var timer = setInterval(function(){
			if(xhr.readyState == XMLHttpRequest.DONE){clearInterval(timer);}
			var text = xhr.responseText.substr(offset);
			if(!$is.empty(text)){var cmds = text.split("\n");$each(cmds,function(k,v){
				if($is.empty(v)){return false;}
				callbacks.onUpdate(v);
			});}
			offset = xhr.responseText.length;
		},1000);}
	}
