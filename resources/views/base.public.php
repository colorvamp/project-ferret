<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="utf-8">
	<link rel="icon" href="{%baseURL%}r/images/favicon.png" type="image/png"/>
	<title>{%PAGE.TITLE%}</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="description" content="{%META.DESCRIPTION%}"/>
	{%META.OG.IMAGE%}
	<link href="{%w.indexURL%}/r/css/public.css" rel="stylesheet">
	<link href="{%w.indexURL%}/r/css/font-awesome.min.css" rel="stylesheet">
	<script async type="text/javascript" src="{%w.indexURL%}/r/js/coreJS.404.js"></script>
	{%PAGE.SCRIPT%}
</head>
<body onload="if(window.init){window.init();}">
	<header class="wrapper">
		<a class="logo" href="{%w.indexURL%}">ferret</a>
		<ul class="menu">
			{%#display.menu.logoff%}
				<li><a href="{%w.indexURL%}/u/login"><i class="fa fa-users"></i> Conectar</a></li>
			{%/display.menu.logoff%}

			{%HTML.MENU.TOP%}
		</ul>
	</header>
	<div class="body wrapper">
		{%MAIN%}
	</div>
	<footer class="wrapper">

	</footer>
</body>
</html>
