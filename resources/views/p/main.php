	<section class="padding">
		<div class="btn-group">
			<a class="btn" href="{%w.indexURL%}/p/save">Nuevo proyecto</a>
		</div>
		<ul class="table">
		{%#projectOBs%}
			<li>
				<div>
					<h3><a href="{%url.project%}">{%projectName%}</a></h3>
				</div>
			</li>
		{%/projectOBs%}
		</ult>
	</section>
