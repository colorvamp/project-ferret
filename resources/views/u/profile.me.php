	<section class="padding">
		<header class="node">
			<div class="image s48">
				<img src="{%user_src.user.48%}">
				<div class="inline-block dropdown-toggle">editar
					<div class="dropdown-menu padded">
						<h4><i class="fa fa-edit"></i> Cambiar avatar</h4>
						<p>Cambiar el avatar de tu usuario.</p>
						<form method="post" enctype="multipart/form-data">
							<input type="hidden" name="subcommand" value="user.avatar"/>
							<input type="hidden" name="_id" value="{%user__id%}"/>
							<ul class="table">
								<li>
									<div>Archivo</div>
									<div><input type="file" name="userAvatar"/></div>
								</li>
							</ul>
							<div class="btn-group right">
								<div class="btn close"><i class="icon-remove-sign"></i> Cancelar</div>
								<button class="btn"><i class="icon-ok-sign"></i> Aceptar</button>
							</div>
						</form>
					</div>
				</div>
			</div>
			<div class="wrapper">
				<h2>{%user_userName%}</h2>
				<p>Perfil de usuario</p>
			</div>
		</header>
		<section>
			<h3>Información privada</h3>
			<ul class="table">
				<li><div>
					{%user_userMail%}
					<div class="inline-block dropdown-toggle">editar
						<div class="dropdown-menu padded">
							<h4><i class="fa fa-edit"></i> Editar correo electrónico</h4>
							<p>Al cambiar el correo electrónico será necesario 
								verificar de nuevo la cuenta.</p>
							<form method="post">
								<input type="hidden" name="subcommand" value="mail.change">
								<ul class="table">
									<li>
										<div>Correo electrónico</div>
										<div><input type="text" name="userMail"></div>
									</li>
								</ul>
								<div class="btn-group right">
									<div class="btn btn-close">Cerrar</div>
									<button class="btn">Salvar</button>
								</div>
							</form>
						</div>
					</div>
				</div></li>
			</ul>
		</section>
	</section>
