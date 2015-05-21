	<section class="padding">
		<header>
			<h2>{%user_userName%}</h2>
			<p>Perfil de usuario</p>
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
