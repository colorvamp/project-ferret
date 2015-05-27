
addEventListener('load',function(){
	var ts = document.querySelectorAll('.coredown');
	Array.prototype.slice.call(ts).forEach(function(v){_coredown.mentions.init(v);});
});

_coredown.mentions = {
	keys: {},
	keyl: false,
	vars: {
		active: false
	},
	init: function(elem){
		addEventListener('keydown',function(e){_coredown.mentions.keydown(e,elem);});
		elem.addEventListener('keyup',function(e){_coredown.mentions.keyup(e);});
		elem.addEventListener('keypress',function(e){_coredown.mentions.keypress(e,elem);});
	},
	iface: function(elem){
		if( !elem.getAttribute('data-coredown-mentions') ){
			elem.setAttribute('data-coredown-mentions',true);
			var d = document.createElement('DIV');
			elem.appendChild(d);
			elem.mentions = d;
		}
		$ajax(window.location.href,{'subcommand':'user.search','criteria':elem.logk},{
			onEnd: function(text){
alert(text);
return;
				var json = $json.decode(text);
				if(!json.results.length){ths.results.innerHTML = 'No hubo resultados de búsqueda';return true;}
				if( json.html ){ths.results.innerHTML = json.html;return true;}

				json.results.forEach(function(v,k){
					var node = document.createElement('DIV');
					node.innerHTML = v.hotelName;
					ths.results.appendChild(node);

					node.addEventListener('click',function(e){ths.select.call(ths,e,v);});
				});
			}
		});
		elem.mentions.innerHTML = elem.logk;
	},
	keypress: function(e,elem){
		var charCode = e.keyCode || e.which;
		var charStr = String.fromCharCode(charCode);
		if( charStr == '@' ){
			_coredown.mentions.vars.active = true;
		}
	},
	keydown: function(e,elem){
		var charCode = e.keyCode || e.which;
		_coredown.mentions.keys[charCode] = true;
		if( !elem.logk ){elem.logk = '';}
		if( _coredown.mentions.vars.active ){
			elem.logk += String.fromCharCode(charCode);
			_coredown.mentions.iface(elem);
		}
	},
	keyup: function(e){
		var charCode = e.keyCode || e.which;
		delete _coredown.mentions.keys[charCode];
	}
};
