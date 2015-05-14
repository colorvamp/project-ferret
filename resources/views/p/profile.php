	<section class="padding">
		<header>
			<h2>{%projectOB_projectName%} <a href="{%projectOB_url.project.config%}"><i class="fa fa-cog"></i></a></h2>
			<p>{%projectOB_projectDescription%}</p>
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
		</header>
		<div>
			{%#taskOBs%}
				<div class="node">
					<div class="image s32">
						<i>{%taskPriority%}</i>
					</div>
					<div class="wrapper">
						<h4><a href="{%url.task%}">{%taskName%}</a></h4>
						<p>{%taskDescription%}</p>
						<div class="btn-group clean">
							<a class="btn" href="{%url.task%}"><i class="fa fa-eye"></i> Ver</a>
						</div>
					</div>
				</div>
			{%/taskOBs%}
		</div>
	</section>
