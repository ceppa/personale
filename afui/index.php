<?
	require_once("include/const.php");
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Gestione Personale</title>
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<script type="text/javascript" charset="utf-8" src="js/appframework.min.js"></script>
	<script type="text/javascript" charset="utf-8" src="js/appframework.ui.min.js"></script>	
	<link rel="stylesheet" type="text/css" href="css/style.css" />
	<link rel="stylesheet" type="text/css" href="css/icons.min.css" />
	<link rel="stylesheet" type="text/css" href="css/af.ui.base.min.css" />
	<link rel="stylesheet" type="text/css" href="css/af.ui.min.css" />
	<script src="js/jquery.md5.js"></script>
	<script src="js/login.js"></script>
	<script src="js/home.js"></script>
	<script src="js/main.js"></script>
</head>
<body>
	<div id="afui">
		<div id="header">
			<span class="icon home big" id="home" style="float:left;"></span>
			<span class="icon close big" id="logout" style="float:right;"></span>
		</div>
		<div id="splashscreen" class='ui-loader heavy'>
			gestione personale
			<br>
			<br>
			<span class='ui-icon ui-icon-loading spin'></span>
			<h1>Starting app</h1>
		</div>
		<div id="content">
			<div id="main" title="please login" class="panel">
				<div class="formGroupHead">
		            <form id="loginForm">
						<input type="hidden" id="rnd" value="<?=$_SESSION["rnd"]?>">
						<div class="input-group">
							<label for="username">Username</label>
							<input type="text" name="username" id="username" placeholder="username">
							<label for="password">Password</label>
							<input type="password" name="password" id="password" placeholder="password">
						</div>
						<input type="button" id="login" class="button block" value="login">
					</form>
	            </div>
			</div>
			<div id="thepage" class="panel" data-footer="pageFooter">
			</div>
		</div>
		<footer id="pageFooter">
			<a href="#" id='navbar_home' class='icon home'>Home <span class='af-badge lr'>6</span></a>
			<a href="#" id='navbar_js' class="icon stack">Trans</a>
			<a href="#" id='navbar_ui' class="icon picture">ui</a>
			<a href="#" id='navbar_plugins' class="icon info">api</a>
		</footer>
		<div id="navbar">
		</div>
	</div>
</body>
</html>
