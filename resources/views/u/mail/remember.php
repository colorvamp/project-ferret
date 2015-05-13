	<div style="background:#eee;padding:20px;">
		<h1 style="font-size:2.2em;color:#676C7B;">hotelVamp</h1>

		<div style="background:white;border-bottom:1px solid #999;padding:10px;margin-top:20px;border-radius:4px;">
			<h3 style="margin:0;padding:0;">Hola, {%userOB_userName%}</h3>
			<p>HotelVamp ha recibido un petición para cambiar la contraseña de tu cuenta ({%userOB_userMail%})</p>
			<p>Para confirmar dicha solicitud pulsa en el siguiente enlace:</p>
			<div><a 
				style="font-size:13px !important;padding:4px 10px 3px 10px;margin-bottom:2px;border-radius:4px;background:#eca168;color:white;border-bottom:3px solid #e38873;text-decoration:none;"
				href="{%w.indexURL%}/u/remember/{%userOB_userMail%}/{%userOB_userCode_remember%}">Recuperar contraseña</a></div>
			<p>Si te ha llegado este correo por error o tienes alguna duda ponte en contacto con nosotros respondiendo a este correo.</p>
		</div>
	</div>
