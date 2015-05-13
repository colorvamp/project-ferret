<main>
	<section>
		<header>
			<h2>Registro de usuario</h2>
			<p>Bienvenido a hotelvamp!</p>
		</header>
		<div class="wrapper timeline">
			<p>
				Introduce tus datos a continuación.
			</p>
			<form method="post">
				<input type="hidden" name="subcommand" value="user.register">
				<ul class="table">
					<li>
						<div>Nombre de usuario</div>
						<div><input type="text" name="userName"></div>
					</li>
					<li>
						<div>Correo electrónico</div>
						<div><input type="text" name="userMail"></div>
					</li>
					<li>
						<div>Contraseña</div>
						<div><input type="password" name="userPass"></div>
					</li>
					<li>
						<div>Repetir Contraseña</div>
						<div><input type="password" name="userPassR"></div>
					</li>
				</ul>
				<div class="btn-group">
					<button class="btn"><i class="fa fa-check"></i> Registrar</button>
				</div>
			</form>
		</div>
	</section>
</main>
<aside>

</aside>
