<div class="wrapper">
	<h2>Login de usuarios</h2>
	<p>{%loginWarn%}</p>
	<form method="post" class="login">
		<input type="hidden" name="subcommand" value="user.login">
		<ul class="table">
			<li><div>email:</div><div><input type="text" name="userMail" placeholder="correo electrónico"></div></li>
			<li><div>contraseña:</div><div><input type="password" name="userPass" placeholder="contraseña"></div></li>
		</ul>
		<div class="btn-group">
			<a href="{%w.indexURL%}/u/remember">Recordar contraseña</a>
			<button class="btn"><i class="icon-unlock-alt"></i> Entrar en el sistema</button>
		</div>
	</form>
</div>
