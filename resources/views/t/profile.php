	<section class="padding">
		<header>
			<h2>{%taskOB_taskName%}</h2>
			<ul class="table radio">
				<li><div><i class="fa fa-folder-open"></i></div><div>id: {%taskOB__id%}</div></li>
				<li><div><i class="fa fa-list-ol"></i></div><div>prioridad: {%taskOB_taskPriority%}</div></li>
				<li><div><i class="fa fa-clock-o"></i></div><div>fecha: {%taskOB_taskStamp%}</div></li>
				<li><div><i class="fa fa-check"></i></div>
					<div>status: 
						<div class="inline-block dropdown-toggle">{%taskOB_taskStatus%} <i class="fa fa-caret-down"></i>
							<div class="dropdown-menu padded">
								<h4><i class="fa fa-check"></i> Cambiar status</h4>
								<p>Cambiar status de la tarea.</p>
								<form method="post">
									<input type="hidden" name="subcommand" value="task.save">
									<input type="hidden" name="_id" value="{%taskOB__id%}">
									<select name="taskStatus">
										<option value="open">Abierta</option>
										<option value="patch">Parche disponible</option>
										<option value="closed">Cerrada</option>
									</select>
									<div class="btn-group right">
										<div class="btn btn-close">Cerrar</div>
										<button class="btn"><i class="fa fa-save"></i> Salvar</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</li>
				<li><div><i class="fa fa-user"></i></div><div>por: {%taskOB_html.user.created%}</div></li>
				<li><div><i class="fa fa-user"></i></div><div>asignado:
						<div class="inline-block dropdown-toggle">{%taskOB_html.user.assigned%} <i class="fa fa-caret-down"></i>
							<div class="dropdown-menu padded">
								<h4><i class="fa fa-check"></i> Asignar tarea a usuario</h4>
								<p>Asignar tarea a usuario.</p>
								<form method="post">
									<input type="hidden" name="subcommand" value="task.save">
									<input type="hidden" name="_id" value="{%taskOB__id%}">
									<select name="taskAssign">
										<option value="">Sin asignar</option>
										{%#userOBs%}
										<option value="{%_id%}">{%userName%}</option>
										{%/userOBs%}
									</select>
									<div class="btn-group right">
										<div class="btn btn-close">Cerrar</div>
										<button class="btn"><i class="fa fa-save"></i> Salvar</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</li>
				<li><div><i class="fa fa-tags"></i></div>
					<div>tags:
						{%#taskOB_taskTags%}
						<a href="{%w.indexURL%}/t/tag/{%.%}">{%.%}</a> 
						{%/taskOB_taskTags%}
					</div>
				</li>
			</ul>
			<p>{%taskOB_taskDescription%}</p>
			<div class="btn-group">
				<a class="btn" href="{%taskOB_url.task.edit%}"><i class="fa fa-edit"></i> Editar</a>
				<div class="btn dropdown-toggle"><i class="fa fa-trash"></i> Eliminar tarea
					<div class="dropdown-menu padded">
						<h4><i class="fa fa-trash"></i> Eliminar tarea</h4>
						<p>Eliminar esta tarea.</p>
						<form method="post">
							<input type="hidden" name="subcommand" value="task.remove">
							<div class="btn-group right">
								<div class="btn btn-close">Cerrar</div>
								<button class="btn"><i class="fa fa-trash"></i> Eliminar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</header>

		<section>
		{%#shoutOBs%}
			<article class="node">
				<div class="image">
					<img src="{%w.indexURL%}/images/avatar">
				</div>
				<div class="wrapper">
					{%shoutText%}
				</div>
			</article>
		{%/shoutOBs%}
		</section>
		<section>
			<form method="post">
				<input type="hidden" name="subcommand" value="shout.save">
				<div class="textarea">
					<textarea placeholder="Leave a comment" name="shoutText"></textarea>
				</div>

				<div class="btn-group right">
					<button class="btn">Comment</button>
				</div>
			</form>
		</section>
	</section>
