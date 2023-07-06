<?php require_once "header.php"; ?>

<a href="index.php">Home</a>
<p><span style="font-size: medium;">Enter Session</span></p>
<p>If you are having trouble getting started, check out <a href="https://the-overseer.wikia.com/wiki/Beginner%27s_Guide_to_the_Overseer_Project">this guide!</a></p>

<br/>

<form action="addplayer.php" method="post">
	<div class="spacer">
		<p><u>Register new player:</u></p>

		<div class="spacer indent">
			<p>Username: <input id="username" name="username" type="text"/></p>
			<p>Password: <input id="password" name="password" type="password"/></p>
			<p>Confirm password: <input id="confirmpw" name="confirmpw" type="password"/></p>
		</div>

		<div class="spacer indent">
			<p>Email (optional): <input id="email" name="email" type="text"/></p>
			<p>Confirm email: <input id="cemail" name="cemail" type="text"/></p>
			<p class="indent">
				The Overseer Project uses these emails for the sole purpose of account recovery, should you forget your password.
				We will never give your email to any third parties, or send you anything without your permission.
				You can always change your email through the Player Settings page.
			</p>
		</div>
	</div>

	<div class="spacer">
		<p><u>Enter session:</u></p>

		<div class="spacer indent">
			<p>Session name: <input id="session" name="session" type="text"/></p>
			<p>Session password: <input id="sessionpw" name="sessionpw" type="password"/></p>
			<p>
				<input type="checkbox" name="randomsession" value="randomsession"> Disregard the above, put me in a random session!
				<p class="indent">(Only includes sessions that have opted in)</p>
			</p>
		</div>
	</div>

	<div class="spacer">
		<p><u>Player entry configuration:</u></p>

		<div class="spacer indent">
			<p>Prototyping strength: <input id="prototyping_strength" name="prototyping_strength" type="text"/></p>
			<p class="indent">
				For a first time player, I recommend between 0 and 10. 999 represents the power of a First Guardian.
				Be aware that you can prototype post-entry as well, and the resulting power will not be applied to the enemies in your session.
			</p>
			<p>Sprite name: <input id="sprite_name" name="sprite_name" type="text"/>sprite</p>
			<p>First prototyping item: <input id="protoitem1" name="protoitem1" type="text"/></p>
			<p>Second prototyping item: <input id="protoitem2" name="protoitem2" type="text"/></p>
			<p class="indent"><b>For your prototyping to succeed, you must enter a prototyping strength between -999 and 999, and your first prototyping item field must not be empty!</b></p>
		</div>

		<div class="spacer indent">
			<p>
				Client player: <input id="client" name="client" type="text"/>
				<p class="indent">(This can be left blank, you will have the opportunity to register a client afterwards)</p>
			</p>
		</div>

		<div class="spacer indent">
			<p>Land of <input id="land1" name="land1" type="text"/> and <input id="land2" name="land2" type="text"/></p>

			<p>Grist category: <select name="grist_type">
				<?php
				$gristresult = $mysqli->query("SELECT * FROM Grist_Types");
				while ($gristrow = $gristresult->fetch_array()) {
					echo '<option value="' . $gristrow['name'] . '">' . $gristrow['name'] . ' - ';
					$i = 1;
					while ($i <= 9) { //Nine types of grist. Magic numbers >_>
						$griststr = "grist" . strval($i);
						echo $gristrow[$griststr];
						if ($i != 9)
							echo ", ";
						$i++;
					}
					echo '</option>';
				}
				?>
			</select></p>

			<p>Dreaming status:
				<select name="dreamer">
					<option value="Unawakened">Unawakened</option>
					<option value="Prospit">Prospit</option>
					<option value="Derse">Derse</option>
				</select>
			</p>
		</div>
	</div>

	<div class="spacer">
		<input class="center" type="submit" value="Register"/>
	</div>
</form>

<?php require_once "footer.php"; ?>
