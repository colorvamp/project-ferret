	<section class="padding">
		<header>
			<h2>Tareas <a href="{%projectOB_url.project.config%}"><i class="fa fa-cog"></i></a></h2>
			<p>{%projectOB_projectName%}</p>
			<div class="btn-group mini">
				<a class="btn" href="{%projectOB_url.project.save%}"><i class="fa fa-edit"></i> Editar</a>
				<div class="btn dropdown-toggle"><i class="fa fa-plus"></i> Nueva tarea
					<div class="dropdown-menu padded">
						<h4><i class="fa fa-plus"></i> Nueva tarea</h4>
						<p>Añadir nuevo tarea.</p>
						<form method="post">
							<input type="hidden" name="subcommand" value="task.save">
							<ul class="table">
								<li>
									<div>Nombre de la tarea</div>
									<div><input type="text" name="taskName"></div>
								</li>
								<li>
									<div>Descripción de la tarea</div>
									<div><input type="text" name="taskDescription"></div>
								</li>
							</ul>
							<div class="btn-group">
								<div class="btn btn-close">Cerrar</div>
								<button class="btn">Salvar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="row-info">
				<span class="first"><a href="{%url.tasks.active%}">{%tasks.active.count%} active tasks</a></span>
				<span>{%tasks.assigned.count%} tasks assigned to you</span>
				<span><a href="{%url.tasks.closed%}">{%tasks.closed.count%} completed tasks</a></span>
			</div>
		</header>
		<div>
			{%#taskOBs%}
				<div class="node task">
					{%#src.task.48%}
					<div class="image s32">
						<img src="{%src.task.48%}">
					</div>
					{%/src.task.48%}
					{%^src.task.48%}
					<div class="image s32">
						<i>{%taskPriority%}</i>
					</div>
					{%/src.task.48%}
					<div class="wrapper">
						<h4><a href="{%url.task%}">{%taskName%}</a></h4>
						<div class="tags">
							{%#taskTags%}
							<span>{%.%}</span>
							{%/taskTags%}
						</div>
						<div class="description">{%taskDescription%}</div>
						<div class="btn-group clean">
							<a class="btn" href="{%url.task%}"><i class="fa fa-eye"></i> Ver</a>
						</div>
					</div>
				</div>
			{%/taskOBs%}
		</div>
	</section>
