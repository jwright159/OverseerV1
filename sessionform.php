<?php require_once "header.php"; ?>
<a href="index.php">Home</a>
<p style="font-size: medium;">Create Session</p>

<form action="createsession.php" method="post">
	<div class="spacer">
		<p>WARNING: DO NOT include an apostrophe in your session name, it messes everything up. You have been warned.</p>
		<p>Session name: <input id="session" name="session" type="text"/></p>
		<p>Session password: <input id="sessionpw" name="sessionpw" type="password"/></p>
		<p>Confirm session password: <input id="confirmpw" name="confirmpw" type="password"/></p>
	</div>

	<div class="spacer">
		<p><input type="checkbox" name="canon" value="canon">Use canon SBURB devices and alchemy methods (for that authentic feel at the cost of a bit of convenience)</p>
		<p><input type="hidden" name="admin" value="admin"><input type="checkbox" name="challenge" value="challenge">Enable Challenge Mode (for veteran players who know too many codes and recipes for a normal game to be a challenge)</p>
		<p><input type="checkbox" name="randoms" value="randoms">Allow players to randomly join this session</p>
		<p><input type="checkbox" name="unique" value="unique">Enforce unique classpects (a new player cannot share a class or aspect with another player in the session unless there are over 12 players)</p>
	</div>

	<input type="submit" value="Register" />
</form>

<p>NOTE - Session administration works by making the first user to enter the session the session admin.</p>
<?php require_once "footer.php"; ?>