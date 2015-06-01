	<section class="padding">
		<header>
			<h2>Proyectos</h2>
			<p>Listado de proyectos</p>
			<div class="btn-group mini">
				<a class="btn" href="{%w.indexURL%}/p/save">Nuevo proyecto</a>
			</div>
		</header>
		{%#projectOBs%}
		<div class="project-box">
			<h3><a href="{%url.project%}">{%projectName%}</a></h3>
			<div class="description">{%projectDescription%}</div>
		</div>
		{%/projectOBs%}
	</section>
