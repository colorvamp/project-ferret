	<section class="padding">
		<h2>{%projectOB_projectName%}</h2>
		<div class="btn-group mini">
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
		<div>
			{%#taskOBs%}
				<div class="box">
					<h3 class="dropdown-toggle"><i class="fa fa-plus"></i> {%taskName%}
						<div class="dropdown-menu padded">
							<h4><i class="fa fa-plus"></i> Opciones</h4>
							<p>Opciones de la tarea.</p>
							<div class="btn-group">
								<div class="btn btn-close">Cerrar</div>
							</div>
						</div>
					</h3>
					<div>
						<a href="{%url.task%}">Ver</a>
					</div>
				</div>
			{%/taskOBs%}
		</div>
	</section>
