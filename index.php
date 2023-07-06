<?php require_once "header.php"; ?>

<?php if (empty($_SESSION['username'])) { ?>

	<p class="spacer">
		To create an account, you (or someone you plan to play with) must first Start a Session.
		Once that's done, click Join Session and that would be where you create your own account.
		An email is not required whatsoever.
	</p>

	<p class="spacer">
		To note, for RP purposes, this game is treated as AFTER your Characters have Entered the Medium
	</p>

<?php } else { ?>

	<a href="overview.php"><img src="/Images/title/playnew.png" width="200" /></a>
	<a href="strife.php"><img src="/Images/title/strife.png" width="200" /></a>
	<a href="grist.php"><img src="/Images/title/gristly.png" width="200" /></a>
	<a href="porkhollow.php"><img src="/Images/title/booney.png" width="200" /></a><br/>

	<?php
		foreach (fetchAll("SELECT *  FROM Players WHERE session_name LIKE :sessionName AND enemydata != '';", ['sessionName' => $userrow['session_name']]) as $row)
		{
			if ($row['username'] != $username)
				echo "$row[username] is strifing right now!<br/>";
			else
				echo "You are strifing right now!<br/>";
		}

		$sessionrow = fetchOne("SELECT * FROM `Sessions` WHERE `name` = :sessionName LIMIT 1;", ['sessionName' => $userrow['session_name']]);
		if ($sessionrow['admin'] == $username && $userrow['admin'] == 0)
		{
			$userrow['admin'] = 1;
			query("UPDATE Players SET `admin` = 1 WHERE username = :username LIMIT 1;", ['username' => $username]);
			
			echo "You were set as the session's head admin, but you were not marked as an admin yourself.";
			echo "We have just attempted to fix this, but if you have gotten this message more than once, Blahdev/Babby Overseer would appreciate it if you reported it to him.";
			echo "<br/>";
		}

		//echo '<a href="events.php">Event Log</a><br/>'; -- Will work on event log later.
	?>

<?php } ?>

<?php require_once "footer.php"; ?>
