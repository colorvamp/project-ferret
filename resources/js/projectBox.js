var projectBox = new Class({
	vars: {},
	init: function(){
		this.vars = {'currentProjectID':false};
	},
	onload: function(){
		this.sidebar_listProjects();
	},
	sidebar_listProjects: function(h){
		if(!h){h = $_('projectBox_projectList');}
		h.empty();
		var ths = this;
		$A(VAR_PROJECTS).each(function(el){
			$C('LI',{innerHTML:el.projectName,onclick:function(){ths.project_select(el.id);}},h);
		});
	},
	sidebar_newProject: function(hook,ops){
		function sendData(c,ths){
			var ops = $parseForm(c);
			$C('DIV',{className:'loadingHolder',innerHTML:'Sincronizando, por favor espere...'},c.empty());

			ajaxPetition('r/PHP/API_project.php','command=addOrModify&'+$toUrl(ops),function(ajax){
				var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));info_destroy(c);return;}
				/* Pase lo que pase debemos actualizar la variable VAR_PROJECTS */
				VAR_PROJECTS.push(r.data);

				var l = $_('projectBox_projectList');
				if(l){ths.sidebar_listProjects(l);}

				/*$C("DIV",{innerHTML:"El curso se archivó correctamente."},h.empty());
				var buttonHolder = $C("DIV",{className:"buttonHolder"},h);
				gnomeButton_create("Aceptar",function(){removeThemeInfo(h);},buttonHolder);
				$C("I",{className:"floatSeparator"},h);*/
				eFadeout(c.infoWindow,function(el){info_destroy(el);});
			}.bind(ths));
		}

		if(!ops){ops = {'projectName':'','projectDescription':''};}
		var i = info_create('dialog_newProject',{},hook).infoContainer.empty();
		$C('H1',{innerHTML:'Añadir nuevo proyecto'},i);
		$C('DIV',{innerHTML:'Nombre del proyecto:'},i);
		$C('INPUT',{name:'projectName',value:ops.projectName},$C('DIV',{className:'inputText'},i));
		$C('DIV',{innerHTML:'Descripción del proyecto:'},i);
		$C('TEXTAREA',{name:'projectDescription',value:ops.projectDescription},$C('DIV',{className:'inputText'},i));

		var d = $C('DIV',{className:'buttonHolder'},i);
		gnomeButton_create('Aceptar',function(e,bt){sendData(i,this);}.bind(this),d);
		gnomeButton_create('Cancelar',function(e,bt){info_destroy(i);},d);
	},
	topMenu_addJob: function(hook,ops){
		if(this.vars.currentProjectID === false){return;}

		function sendData(c,ths){
			var ops = $parseForm(c);
			ops.projectID = ths.vars.currentProjectID;
			$C('DIV',{className:'loadingHolder',innerHTML:'Sincronizando, por favor espere...'},c.empty());

			ajaxPetition('r/PHP/API_project.php','command=addJob&'+$toUrl(ops),function(ajax){
				var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));info_destroy(c);return;}
				info_destroy(c);
				this.project_listJobs();
			}.bind(ths));
		}

		if(!ops){ops = {'jobName':'','jobDescription':'','jobAssignedTo':'','jobTimeEstimated':'','jobTags':''};}
		var i = info_create('dialog_newProject',{},hook).infoContainer.empty();
		$C('H1',{innerHTML:'Añadir nuevo trabajo'},i);
		$C('INPUT',{name:'_id_',type:'hidden',value:ops.id},i);
		$C('DIV',{innerHTML:'Nombre del trabajo'},i);
		$C('INPUT',{name:'jobName',value:ops.jobName},$C('DIV',{className:'inputText'},i));
		$C('DIV',{innerHTML:'Descripción del trabajo'},i);
		$C('TEXTAREA',{name:'jobDescription',value:ops.jobDescription},$C('DIV',{className:'inputText'},i));
		$C('DIV',{innerHTML:'Asignado a:'},i);
		$C('INPUT',{name:'jobAssignedTo',value:ops.jobAssignedTo},$C('DIV',{className:'inputText'},i));
		$C('DIV',{innerHTML:'Tiempo estimado'},i);
		$C('INPUT',{name:'jobTimeEstimated',value:ops.jobTimeEstimated},$C('DIV',{className:'inputText'},i));
		$C('DIV',{innerHTML:'Tags'},i);
		$C('INPUT',{name:'jobTags',value:ops.jobTags},$C('DIV',{className:'inputText'},i));

		var d = $C('DIV',{className:'buttonHolder'},i);
		gnomeButton_create('Aceptar',function(e,bt){sendData(i,this);}.bind(this),d);
		gnomeButton_create('Cancelar',function(e,bt){info_destroy(i);},d);
	},
	topMenu_listJobs: function(){this.project_listJobs();},
	projectBody_showJob: function(job,h){
		if(!h){h = $_('projectBox_projectBody');}
		var w = $C('DIV',{className:'jobBlock'},h);
		var ths = this;

		var blockHeader = $C('DIV',{className:'blockHeader',innerHTML:job.jobName},w);
		$C('DIV',{className:'blockOptions',onclick:function(){ths.job_options(this,job);}},blockHeader);

		var blockBody = $C('DIV',{className:'blockBody'},w);
//alert(print_r(job));


		var jobStatusHolder = $C('DIV',{className:'jobStatusHolder'},blockBody);
		var canvas = $C('CANVAS',{width:50,height:50},jobStatusHolder);
		var canvasPies = {};if(job.jobTasks){$A(job.jobTasks).each(function(el,n){canvasPies[n] = $round((el.taskTime/job.jobTimeEstimated)*100);});}
		_innerGraphs.createPieChart(canvas,canvasPies);
		$C('SPAN',{innerHTML:(job.jobPercentage | 0)+'% completado'},jobStatusHolder);

		$C('SPAN',{innerHTML:job.jobDescription},blockBody);

		if(job.jobTasks){
			var tasksHolder = $C('TBODY',{},$C('TABLE',{className:'tasksHolder'},blockBody));
			$A(job.jobTasks).each(function(el,n){
				var tr = $C('TR',{},tasksHolder);
				//var td = $C('TD',{innerHTML:el.taskName},tr);
				var head = $C('TD',{},tr);
				$C('DIV',{'.backgroundColor':_innerGraphs.colors[n+1]},$C('DIV',{className:'colorLegend'},head));
				var edit = $C('DIV',{},head);
				$C('TD',{innerHTML:el.taskName},tr);
				$C('TD',{innerHTML:el.taskTime},tr);
				$C('TD',{innerHTML:$round((el.taskTime/job.jobTimeEstimated)*100)+'%'},tr);
				$C('A',{href:'javascript:',innerHTML:'u',onclick:function(){ths.job_addTask(edit,el);}},$C('TD',{},tr));
				$C('A',{href:'javascript:',innerHTML:'r',onclick:function(){ths.job_removeTask(el.id);}},$C('TD',{},tr));
			});
		}

		var taskOptionsHolder = $C('DIV',{className:'taskOptionsHolder'},blockBody);
		$C('A',{href:'javascript:',innerHTML:'Añadir nueva Tarea',onclick:function(){ths.job_addTask(taskOptionsHolder,job.id);}},taskOptionsHolder);
	},
	job_options: function(hook,job){
		var ths = this;
		var i = info_create('dialog_jobOptions',{},hook).infoContainer.empty();

		function job_options_edit(){ths.topMenu_addJob(hook,job);info_destroy(i);}
		function job_options_remove(){ths.job_remove(hook,job);info_destroy(i);}

		var ul = $C('UL',{},i);
		$C('LI',{innerHTML:'Marcar como oculto'},ul);
		$C('A',{href:'javascript:',innerHTML:'Editar datos del trabajo',onclick:function(){job_options_edit();}},$C('LI',{},ul));
		$C('A',{href:'javascript:',innerHTML:'Eliminar este trabajo',onclick:function(){job_options_remove();}},$C('LI',{},ul));

		var d = $C('DIV',{className:'buttonHolder'},i);
		gnomeButton_create('Cancelar',function(e,bt){info_destroy(i);},d);
	},
	job_remove: function(hook,job){
		function sendData(c,ths){
			var ops = $parseForm(c);
			$C('DIV',{className:'loadingHolder',innerHTML:'Sincronizando, por favor espere...'},c.empty());
			ajaxPetition('r/PHP/API_project.php','command=removeJob&'+$toUrl(ops),function(ajax){var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));info_destroy(c);return;}info_destroy(c);this.project_listJobs();}.bind(ths));
		}

		var i = info_create('dialog_jobRemove',{},hook).infoContainer.empty();
		$C('H1',{innerHTML:'Eliminar este trabajo?'},i);
		$C('INPUT',{name:'jobID',type:'hidden',value:job.id},i);

		var d = $C('DIV',{className:'buttonHolder'},i);
		gnomeButton_create('Aceptar',function(e,bt){sendData(i,this);}.bind(this),d);
		gnomeButton_create('Cancelar',function(e,bt){info_destroy(i);},d);
	},
	job_addTask: function(hook,ops){
		function sendData(c,ths){
			var ops = $parseForm(c);
			$C('DIV',{className:'loadingHolder',innerHTML:'Sincronizando, por favor espere...'},c.empty());

			ajaxPetition('r/PHP/API_project.php','command=addTask&'+$toUrl(ops),function(ajax){
				var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));info_destroy(c);return;}
				alert(print_r(r));
				info_destroy(c);
			}.bind(ths));
		}

		if(!ops || ops.constructor != Object){ops = {'jobID':ops,'taskName':'','taskTime':''};}
		var i = info_create('dialog_newProject',{},hook).infoContainer.empty();
		$C('H1',{innerHTML:'Añadir nueva tarea'},i);
		$C('INPUT',{name:'_id_',type:'hidden',value:ops.id},i);
		$C('INPUT',{name:'jobID',type:'hidden',value:ops.jobID},i);
		$C('DIV',{innerHTML:'Nombre de la tarea'},i);
		$C('INPUT',{name:'taskName',value:ops.taskName},$C('DIV',{className:'inputText'},i));
		$C('DIV',{innerHTML:'Tiempo consumido'},i);
		$C('INPUT',{name:'taskTime',value:ops.taskTime},$C('DIV',{className:'inputText'},i));

		var d = $C('DIV',{className:'buttonHolder'},i);
		gnomeButton_create('Aceptar',function(e,bt){sendData(i,this);}.bind(this),d);
		gnomeButton_create('Cancelar',function(e,bt){info_destroy(i);},d);
	},
	job_removeTask: function(taskID){
		ajaxPetition('r/PHP/API_project.php','command=removeTask&taskID='+taskID,function(ajax){
			var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));info_destroy(c);return;}
			alert(print_r(r));
			//info_destroy(c);
		});
	},
	/*INI-PROJECTS*/
	project_select: function(id){
		this.vars.currentProjectID = id;
		this.project_listJobs();
	},
	project_listJobs: function(){
		var projectID = this.vars.currentProjectID;
		ajaxPetition('r/PHP/API_project.php','command=listJobs&projectID='+projectID,function(ajax){
			var r = jsonDecode(ajax.responseText);if(parseInt(r.errorCode)>0){alert(print_r(r));info_destroy(c);return;}
			var h = $_('projectBox_projectBody').empty();
			$A(r.data).each(function(el){this.projectBody_showJob(el,h);}.bind(this));
			//alert(print_r(r));
		}.bind(this));
	}
	/*END-PROJECTS*/
});

var _projectBox = new projectBox();
