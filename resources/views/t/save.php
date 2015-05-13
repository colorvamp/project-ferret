	<section class="padding">
		<form method="post">
			<input type="hidden" name="subcommand" value="task.save">
			<input type="hidden" name="_id" value="{%taskOB__id%}">
			<ul class="table">
				<li><div>Nombre</div><div><input type="text" name="taskName" value="{%taskOB_taskName%}" placeholder="Nombre de la tarea"></div></li>
				<li><div>Descripción</div><div><textarea name="taskDescription" placeholder="Descripción de la tarea">{%taskOB_taskDescription%}</textarea></div></li>
				<li><div>Descripción</div><div><textarea name="taskTags" placeholder="Tags de la tarea">{%taskOB_html.tags%}</textarea></div></li>
			</ul>
			<div class="btn-group">
				<button class="btn">Salvar</button>
			</div>
		</form>
	</section>
