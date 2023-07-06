<?php require_once "header.php"; ?>

<script>
	$(document).ready(function () {
		var username = "";
		var password = "";
		$('#loginb').submit(function () {
			username = $("#usernameb").val();
			password = $("#passwordb").val();
			$('#passwordb').val('');
			$('#catchb').text('Logging you in...').attr('style', 'color: #FFA500;');
			$.post("login.php", { username: username, password: password, mako: "kawaii" })
				.done(function (data) {
					if (data == "true") {
						window.location = 'index.php';
						$('#catchb').text('Success!').attr('style', 'color: green;');
					}
					else {
						$('#catchb').text('Incorrect login details').attr('style', 'color: red;');
					}
				});
			return false;
		});
	});
</script>

<?php if (empty($_SESSION['username'])) { ?>

	<form id="loginb" action="login.php" method="post">
		<p>Username: <input id="usernameb" maxlength="50" name="usernameb" type="text" /></p>
		<p>Password: <input id="passwordb" maxlength="50" name="passwordb" type="password" /></p>

		<p>
			<a href="playerform.php">Enter a Session</a>
			|
			<a href="sessionform.php"> Create a Session</a>
		</p>

		<p><a href="forgotpassword.php">Forget your password?</a></p>

		<input name="Submit" type="submit" value="Submit" />
	</form>

	<span style="color: red;" id="catchb"></span>

<?php } else { ?>

	<script>
		$(document).ready(function () {
			window.location = 'index.php';
		});
	</script>

<?php } ?>

<?php require_once "footer.php"; ?>
