<div class="wrapper">
	<h2>Recordar contraseña</h2>
	<p>{%loginWarn%}</p>
	<form method="post" class="login">
		<input type="hidden" name="subcommand" value="user.remember">
		<ul class="table">
			<li>
				<div>Mail</div>
				<div><input type="text" name="userMail" placeholder="Correo electrónico"></div>
			</li>
		</ul>
		<div class="btn-group">
			<button class="btn"><i class="fa fa-envelope"></i> Recordar contraseña</button>
		</div>
	</form>
</div>
