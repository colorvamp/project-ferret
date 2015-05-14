	<section class="padding">
		<form method="post">
			<input type="hidden" name="subcommand" value="project.save">
			<input type="hidden" name="_id" value="{%projectOB__id%}">
			<ul class="table">
				<li><div>Nombre</div><div><input type="text" name="projectName" value="{%projectOB_projectName%}" placeholder="Nombre del proyecto"></div></li>
				<li><div>Descripción</div><div><textarea name="projectDescription" placeholder="Descripción del proyecto">{%projectOB_projectDescription%}</textarea></div></li>
			</ul>
			<div class="btn-group">
				<button class="btn">Salvar</button>
			</div>
		</form>
	</section>
