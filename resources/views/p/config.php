	<section class="padding">
		<header>
			<h2>Configuración de {%projectOB_projectName%}</h2>
			<p>Configuración</p>
		</header>
		<section>
			<h3>Configuración de usuarios</h3>
			{%#userOBs%}
			<div class="node">
				<div class="image s32">
					<i>-</i>
				</div>
				<div class="wrapper">
					<h4><a href="#">{%userName%}</a></h4>
					<div class="btn-group clean">
						<div class="btn dropdown-toggle"><i class="fa fa-cog"></i> Permisos
							<div class="dropdown-menu padded">
								<h4><i class="fa fa-cog"></i> Permisos</h4>
								<p>Cambiar permisos de <strong>Usuario</strong> sobre este <strong>Perfil</strong>.</p>
								<form method="post">
									<input type="hidden" name="subcommand" value="user.modes">
									<input type="hidden" name="userID" value="{%_id%}">
									<ul class="table radio">
										<li><div><input type="radio" name="userModes" value="r" {%userOB_html.check.r%}></div><div>Lectura</div></li>
										<li><div><input type="radio" name="userModes" value="w" {%userOB_html.check.w%}></div><div>Escritura</div></li>
									</ul>
									<div class="btn-group right">
										<div class="btn btn-close">Cerrar</div>
										<button class="btn"><i class="fa fa-trash"></i> Salvar</button>
									</div>
								</form>
							</div>
						</div>
						<div class="btn dropdown-toggle"><i class="fa fa-trash"></i> Eliminar
							<div class="dropdown-menu padded">
								<h4><i class="fa fa-trash"></i> Eliminar</h4>
								<p>Eliminar acceso a este <strong>Usuario</strong>.</p>
								<form method="post">
									<input type="hidden" name="subcommand" value="user.remove">
									<input type="hidden" name="userID" value="{%_id%}">
									<div class="btn-group right">
										<div class="btn btn-close">Cerrar</div>
										<button class="btn"><i class="fa fa-trash"></i> Eliminar</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
			{%/userOBs%}
		</section>
		<section>
			<h3>Añadir nuevo usuario</h3>
			<form method="post">
				<input type="hidden" name="subcommand" value="user.invite">
				<ul class="table">
					<li>
						<div>Correo electrónico</div>
						<div><input type="text" name="userMail" placeholder="example@example.es"></div>
					</li>
				</ul>
				<div class="btn-group right">
					<button class="btn">Invitar</button>
				</div>
			</form>
		</section>
	</section>
