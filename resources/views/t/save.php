	<section class="padding">
		<form method="post">
			<input type="hidden" name="subcommand" value="task.save">
			<input type="hidden" name="_id" value="{%taskOB__id%}">
			<ul class="table">
				<li><div>Nombre</div><div><input type="text" name="taskName" value="{%taskOB_taskName%}" placeholder="Nombre de la tarea"></div></li>
				<li><div>Tags</div><div><textarea name="taskTags" placeholder="Tags de la tarea">{%taskOB_html.tags%}</textarea></div></li>
			</ul>
			<div>
				<div class="coredown">
					<div class="top">
						<div class="left">Markdown <a class="help" href="{%baseURL%}markdown" target="help"><i class="icon-question"></i></a></div>
						<div class="right">Preview</div>
					</div>
					<div class="content">
						<input class="source" name="taskDescription" type="hidden" value="" autocomplete="off"/>
						<div class="left editor" ContentEditable="true"></div>
						<div class="right preview content">{%taskOB_taskDescription%}</div>
					</div>
				</div>
			</div>
			<div class="btn-group">
				<button class="btn">Salvar</button>
			</div>
		</form>
	</section>
