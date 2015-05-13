
	addEventListener('load',function(){
		/* INI-dropdown */
		if( !window.VAR ){window.VAR = {};}
		window.VAR.dropdown = false;
		var dropdownToggles = document.querySelectorAll('.dropdown-toggle');
		Array.prototype.slice.call(dropdownToggles).forEach(function(v){var d = new dropdown(v);});
		var body = document.body;
		body.addEventListener('click',function(event){
			if( window.VAR.dropdown ){
				var event = new CustomEvent('close',{'detail':{},'bubbles':true,'cancelable':true});
				window.VAR.dropdown.dispatchEvent(event);
			}
		});
		/* END-dropdown */
		//_menu.init();
	});

	function dropdown(elem){
		this.elem = elem;
		/* Evitamos que un mismo elemento se pueda instanciar 2 veces como dropdown */
		if( elem.getAttribute('data-dropdown') ){return false;}
		elem.setAttribute('data-dropdown',true);

		var ths = this;
		this.elem.addEventListener('click',function(e){ths.onClick.call(ths,e);});
		this.elem.addEventListener('touchstart',function(e){ths.onClick.call(ths,e);});
		this.elem.addEventListener('close',function(e){ths.close.call(ths,e);});
		/* Evitamos que se cierre al hacer click en el contenido */
		var ddm = elem.querySelector('.dropdown-menu');
		if(ddm){
			ddm.addEventListener('click',function(e){e.stopPropagation();});
			ddm.addEventListener('touchstart',function(e){e.stopPropagation();});
		}

		/* INI-botones de cerrar que incorpore el dropdown */
		var buttons = this.elem.querySelectorAll('.btn.close,.btn.btn-close');
		if(buttons.length){Array.prototype.slice.call(buttons).forEach(function(btn){
			btn.addEventListener('click',function(e){e.stopPropagation();ths.close.call(ths,e);});
			btn.addEventListener('touchstart',function(e){e.stopPropagation();ths.close.call(ths,e);});
		});}
		/* END-botones de cerrar que incorpore el dropdown */

		/* INI-selectBox */
		if(this.isSelectBox()){
			var dataParent = this.elem.getAttribute('data-value');
			this.setValue(dataParent,true);
			Array.prototype.slice.call(ddm.childNodes).forEach(function(it){
				if(!$E.class.exists(it,'item')){return;}
				if(!dataParent){var val = it.getAttribute('data-value');ths.setValue.call(ths,val,true);dataParent = val;}
				it.addEventListener('click',function(e){e.stopPropagation();ths.setValue.call(ths,this.getAttribute('data-value'));ths.close.call(ths);});
			});
		}
		/* END-selectBox */

		/* INI-ajax*/
		if(this.isAjax()){
			var buttons = this.elem.querySelectorAll('.btn.ok,.btn.btn-ok');
			if(buttons.length){Array.prototype.slice.call(buttons).forEach(function(btn){
				btn.addEventListener('click',function(e){
					if(btn.tagName.toUpperCase() == 'BUTTON'){e.preventDefault();}/* Porque puede ser un button y nos envía el formulario */
					e.stopPropagation();
					ths.ajax.call(ths,e);
				});
			});}
		}
		/* END-ajax*/
	}
	dropdown.prototype.isDisabled = function(){return $E.class.exists(this.elem,'disabled');};
	dropdown.prototype.isOpen = function(){return $E.class.exists(this.elem,'active');};
	dropdown.prototype.isSelectBox = function(){return $E.class.exists(this.elem,'select');};
	dropdown.prototype.isAjax = function(){return $E.class.exists(this.elem,'ajax');};
	dropdown.prototype.isRemoveBox = function(){return $E.class.exists(this.elem,'remove');};
	dropdown.prototype.mustSubmit = function(){return $E.class.exists(this.elem,'autosubmit');};
	dropdown.prototype.setValue = function(dataToSet,initial){
		var parent = this.elem.getElementsByClassName('dropdown-menu');if(!parent.length){return;}parent = parent[0];
		Array.prototype.slice.call(parent.childNodes).forEach(function(it){
			if(!$E.class.exists(it,'item')){return;}
			var data = it.getAttribute('data-value');
			if(data != dataToSet){/* Buscamos el element coincidente, saltamos si este no fuera */return;}
			var ddw = $E.parent.find(it,{'className':'dropdown-toggle'});if(!ddw){return false;}
			var ipt = ddw.getElementsByClassName('input');if(ipt){
				Array.prototype.slice.call(ipt).forEach(function(y){y.value = data;});
			}
			var val = ddw.getElementsByClassName('value');if(val){
				Array.prototype.slice.call(val).forEach(function(y){y.innerHTML = it.innerHTML;});
			}
		});

		if(!initial && this.mustSubmit()){
			/* Widget calendar tiene su propio handler */
			if($E.class.exists(this.elem,'widget-calendar')){return false;}
			var form = $E.parent.find(this.elem,{'tagName':'form'});
			return form.submit();
		}
	};
	dropdown.prototype.onClick = function(e){
		var item = false;
		if(this.isDisabled()){return false;}
		e.preventDefault();
		e.stopPropagation();
		if(this.isOpen()){
			//VAR_dropdownToggled = false;
			if(this.isSelectBox() &&  (item = $E.parent.find(e.target,{'className':'item'})) ){
				var dataToSet = item.getAttribute('data-value');
				this.setValue(dataToSet);
			}
			return this.close();
		}
		return this.open();
	}
	dropdown.prototype.ajax = function(e){
		var ths = this;
		var form = $E.parent.find(e.target,{'tagName':'form'});if(!form){return false;}
		var ddm = this.elem.querySelector('.dropdown-menu');if(!ddm){return false;}

		$transition.toState(ddm,'working',function(q){});

		var params = $toUrl($parseForm(form));
		ajaxPetition(window.location.href,params,function(ajax){
			var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));return;}
			if('html' in r){$transition.setHTMLByState(ddm,'info',r.html);}

			$execWhenTrue(function(){return ddm.getAttribute('data-ready') == 'true';},function(){
				$transition.toState(ddm,'info',function(q){});
				if(ths.isRemoveBox()){do{
					/* Si este cuadro de información es para eliminar elementos, lo eliminamos automáticamente */
					var node = $E.parent.find(ddm,{'className':'node'});if(!node){break;}
					$fx.leaveVertical(node,{'callback':function(n){n.parentNode.removeChild(n);}});
				}while(false);}

				var buttons = ddm.querySelectorAll('.btn.close,.btn.btn-close');
				if(buttons.length){Array.prototype.slice.call(buttons).forEach(function(btn){
					btn.addEventListener('click',function(e){e.stopPropagation();ths.close.call(ths,e);});
				});}
			});
		});

		return false;
	};
	dropdown.prototype.open = function(){
		//FIXME: evento before open

		var btn = $E.parent.find(this.elem,{'className':'dropdown-toggle'});
		/* INI-Soporte para recaptcha */
		if( window.grecaptcha && (elems = this.elem.querySelectorAll('.g-recaptcha')) ){
			var key  = false;
			var elem = false;
			Array.prototype.slice.call(elems).forEach(function(elem){
				if( elem.firstChild ){return;}
				key = elem.getAttribute('data-sitekey');
				if(key){grecaptcha.render(elem,{'sitekey':key,'theme':'light'});}
			});
		}
		/* END-Soporte para recaptcha */
		btn.classList.toggle('active');

		if( !window.VAR ){window.VAR = {};}
		window.VAR.dropdown = this.elem;
		var ddm  = btn.querySelector('.dropdown-menu');
		var pos  = $getOffsetPosition(ddm);
		var rpos = (document.body.offsetWidth)-(pos.left+pos.width);
		/* If the infoBox is out the page, fix it to the right border */
		if( rpos < 10 ){ddm.style.left = ddm.offsetLeft+rpos-10+'px';}
		//FIXME: evento open
	}
	dropdown.prototype.close = function(){
		var btn = $E.parent.find(this.elem,{'className':'dropdown-toggle'});
		if(btn){btn.classList.remove('active');}
	};
	/* END-dropdown */

	var _menu = {
		init: function(){
return false;
			var elem = document.body;
			addEventListener('mousedown',_menu.mousedown,false);
			addEventListener('touchstart',_menu.mousedown,false);

			elem.menu = elem.querySelector('ul.menu');
			elem.menu.startW = elem.menu.offsetWidth;
			elem.wrap = elem.querySelector('.wrap');
			elem.wrap.style.transform = 'translate3d(0,0,0)';
			elem.icon = elem.querySelector('nav.menu .icon');

			var ths  = this;
			elem.icon.addEventListener('click',function(){
				if( elem.classList.contains('menu-open') ){
					ths.close(elem);
				}else{
					ths.open(elem);
				}
			});
		},
		open: function(elem){
			if( !elem ){elem = document.body;}
			if( elem.offsetWidth > 500 ){return false;}

			elem.wrap.removeAttribute('style');
			elem.menu.removeAttribute('style');
			elem.scrollX = elem.menu.startW;
			elem.classList.add('menu-open');
		},
		close: function(elem){
			if( !elem ){elem = document.body;}
			if( elem.offsetWidth > 500 ){return false;}

			elem.wrap.removeAttribute('style');
			elem.menu.removeAttribute('style');
			elem.scrollX = 0;
			elem.classList.remove('menu-open');
		},
		mousedown: function(e,elem){
			elem = document.body;
			if( elem.offsetWidth > 500 ){return true;}

			//e.preventDefault();
			//e.stopPropagation();
			elem.rangeX = 8;
			elem.swipeX = false;
			elem.startX = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
			elem.startY = e.changedTouches ? e.changedTouches[0].clientY : e.clientY;
			elem.startScrollX = elem.scrollX ? elem.scrollX : 0;
			elem.menu.startW = (elem.menu.startW) ? elem.menu.startW : elem.menu.offsetWidth;

			if(!('mouseMoveHandler' in elem)){
				elem.mouseMoveHandler = function(ev){return _menu.mousemove(ev,elem);}
				elem.mouseUpHandler   = function(ev){return _menu.mouseup(ev,elem);}
			}

			addEventListener('mousemove',elem.mouseMoveHandler,true);
			addEventListener('mouseup',elem.mouseUpHandler,true);
			addEventListener('touchmove',elem.mouseMoveHandler,true);
			addEventListener('touchend',elem.mouseUpHandler,true);
			addEventListener('touchstop',elem.mouseUpHandler,true);
		},
		mousemove: function(e,elem){
			x = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
			if( !elem.swipeX && ((x > elem.startX+elem.rangeX) || (x < elem.startX-elem.rangeX)) ){
				elem.swipeX = true;
				elem.startX = e.changedTouches ? e.changedTouches[0].clientX : e.clientX;
				elem.startY = e.changedTouches ? e.changedTouches[0].clientY : e.clientY;
			}
			if( !elem.swipeX ){
				return true;
			}
			e.preventDefault();
			e.stopPropagation();

			elem.scrollX = x+elem.startScrollX-elem.startX;
			if( elem.scrollX > elem.menu.startW ){elem.scrollX = elem.menu.startW;}
			if( elem.scrollX < 0 ){elem.scrollX = 0;}
			elem.wrap.style.transform = 'translate3d('+elem.scrollX+'px,0,0)';

			p = -elem.scrollX;
			if( p < (-elem.menu.startW) ){p = -elem.menu.startW;}
			if( p > 0 ){p = 0;}
			elem.menu.style.left = p+'px';
			elem.menu.style.width = elem.scrollX+'px';
		},
		mouseup: function(e,elem){
			//e.preventDefault();
			//e.stopPropagation();

			removeEventListener('mousemove',elem.mouseMoveHandler,true);
			removeEventListener('mouseup',elem.mouseUpHandler,true);
			removeEventListener('touchmove',elem.mouseMoveHandler,true);
			removeEventListener('touchstop',elem.mouseUpHandler,true);

			var left = elem.scrollX || 0;
			var perc = left/elem.menu.startW;
			if( perc < .5 ){
				this.close(elem);
			}else{
				this.open(elem);
			}
		}
	};

